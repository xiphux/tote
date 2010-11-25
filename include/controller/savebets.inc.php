<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

/**
 * Sort bets by week
 *
 * @param array $a first sort item
 * @param array $b second sort item
 */
function sort_bets($a, $b)
{
	return ($a['week'] > $b['week'] ? 1 : -1);
}

/**
 * savebets controller
 *
 * after editing a user's bets, save the changes into the database
 *
 * @param string $poolID pool id
 * @param string $entrant entrant user id
 * @param string $weekbets array of bets for the week
 * @param string $csrftoken CSRF request token
 */
function display_savebets($poolID, $entrant, $weekbets, $csrftoken)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to save bets
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		echo "Invalid request token";
		return;
	}

	if (empty($poolID)) {
		// need the pool
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$games = get_collection(TOTE_COLLECTION_GAMES);

	$pool = $pools->findOne(
		array('_id' => new MongoId($poolID)
		),
		array('season', 'name', 'entries')
	);
	if (!$pool) {
		// must be a valid pool
		echo "Unknown pool";
		return;
	}

	$entrantobj = get_user($entrant);
	if (!$entrantobj) {
		// user must exist
		echo "Entrant not found";
		return;
	}

	// find the user's entry in the pool
	$userentry = null;
	$userentryindex = -1;
	for ($i = 0; $i < count($pool['entries']); $i++) {
		if ($pool['entries'][$i]['user'] == $entrantobj['_id']) {
			$userentry = $pool['entries'][$i];
			$userentryindex = $i;
			break;
		}
	}

	$adminname = user_readable_name($user);

	$entrantname = user_readable_name($entrantobj);

	if (!$userentry) {
		// user needs to be in the pool
		echo "Entrant not in pool";
		return;
	}

	// find the number of weeks in the season
	$lastgame = $games->find(array('season' => (int)$pool['season']), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	$actions = array();

	// go through all the weeks sent down
	for ($i = 1; $i <= $weeks; $i++) {

		if (empty($weekbets[$i])) {

			// no bet for this week, try to see if user had a bet for that week
			for ($j = 0; $j < count($userentry['bets']); $j++) {
				if (isset($userentry['bets'][$j]) && ($userentry['bets'][$j]['week'] == $i)) {
					// user had a bet in the database but now doesn't meaning we're deleting their bet - delete and audit it
					$actions[] = array(
						'action' => 'edit',
						'user' => $entrantobj['_id'],
						'user_name' => $entrantname,
						'admin' => $user['_id'],
						'admin_name' => $adminname,
						'week' => $i,
						'from_team' => $userentry['bets'][$j]['team'],
						'time' => new MongoDate(time())
					);
					unset($userentry['bets'][$j]);
					break;
				}
			}
		} else {

			// setting a bet for a week
			$set = false;

			if (isset($userentry['bets'])) {

				for ($j = 0; $j < count($userentry['bets']); $j++) {

					if (isset($userentry['bets'][$j]) && ($userentry['bets'][$j]['week'] == $i)) {
						// user had a bet for that week

						if ($weekbets[$i] != (string)$userentry['bets'][$j]['team']) {
							// user's old bet for that week doesn't match the new bet for that week,
							// meaning we're changing the user's bet - audit and do it
							$actions[] = array(
								'action' => 'edit',
								'user' => $entrantobj['_id'],
								'user_name' => $entrantname,
								'admin' => $user['_id'],
								'admin_name' => $adminname,
								'week' => $i,
								'from_team' => $userentry['bets'][$j]['team'],
								'to_team' => new MongoId($weekbets[$i]),
								'time' => new MongoDate(time())
							);
							$userentry['bets'][$j]['team'] = new MongoId($weekbets[$i]);
							$userentry['bets'][$j]['edited'] = new MongoDate(time());
						}
						$set = true;
						break;
					}
				}
			}

			if (!$set) {

				// we didn't replace an old bet - meaning we're setting
				// a new bet where there wasn't one, audit and add it
				$actions[] = array(
					'action' => 'edit',
					'user' => $entrantobj['_id'],
					'user_name' => $entrantname,
					'admin' => $user['_id'],
					'admin_name' => $adminname,
					'week' => $i,
					'to_team' => new MongoId($weekbets[$i]),
					'time' => new MongoDate(time())
				);
				$userentry['bets'][] = array(
					'week' => $i,
					'team' => new MongoId($weekbets[$i]),
					'edited' => new MongoDate(time())
				);
			}
		}
	}

	// sort the bets
	usort($userentry['bets'], 'sort_bets');

	// delete the previous bet data
	$pools->update(
		array('_id' => $pool['_id']),
		array(
		'$unset' => array('entries.' . (string)$userentryindex . '.bets' => 1),
		)
	);

	// set the new bet data and add audit log entries
	$pools->update(
		array('_id' => $pool['_id']),
		array(
			'$set' => array('entries.' . (string)$userentryindex . '.bets' => $userentry['bets']),
			'$pushAll' => array('actions' => $actions)
		)
	);

	// go home
	redirect();
}
