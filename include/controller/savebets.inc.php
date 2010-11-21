<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function sort_bets($a, $b)
{
	return ($a['week'] > $b['week'] ? 1 : -1);
}

function display_savebets($poolID, $entrant, $weekbets)
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		return redirect();
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);
	$games = get_collection(TOTE_COLLECTION_GAMES);
	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin', 'first_name', 'last_name'));
	if (!$user) {
		return redirect();
	}

	if (empty($user['admin'])) {
		return redirect();
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$entrantobj = $users->findOne(array('_id' => new MongoId($entrant)), array('username', 'first_name', 'last_name'));
	if (!$entrantobj) {
		echo "Entrant not found";
		return;
	}

	$userentry = null;
	$userentryindex = -1;
	for ($i = 0; $i < count($pool['entries']); $i++) {
		if ($pool['entries'][$i]['user'] == $entrantobj['_id']) {
			$userentry = $pool['entries'][$i];
			$userentryindex = $i;
			break;
		}
	}

	$adminname = $user['username'];
	if (!empty($user['first_name'])) {
		$adminname = $user['first_name'];
		if (!empty($user['last_name']))
			$adminname .= ' ' . $user['last_name'];
	}

	$entrantname = $entrantobj['username'];
	if (!empty($entrantobj['first_name'])) {
		$entrantname = $entrantobj['first_name'];
		if (!empty($entrantobj['last_name']))
			$entrantname .= ' ' . $user['last_name'];
	}

	if (!$userentry) {
		echo "Entrant not in pool";
		return;
	}

	$lastgame = $games->find(array('season' => (int)$pool['season']), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	$actions = array();

	for ($i = 1; $i <= $weeks; $i++) {
		if (empty($weekbets[$i])) {
			// no bet for the week
			for ($j = 0; $j < count($userentry['bets']); $j++) {
				if (isset($userentry['bets'][$j]) && ($userentry['bets'][$j]['week'] == $i)) {
					// delete existing bet
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
						if ($weekbets[$i] != (string)$userentry['bets'][$j]['team']) {
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
				// new bet, add it
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

	usort($userentry['bets'], 'sort_bets');

	$pools->update(
		array('_id' => $pool['_id']),
		array(
		'$unset' => array('entries.' . (string)$userentryindex . '.bets' => 1),
		)
	);
	$pools->update(
		array('_id' => $pool['_id']),
		array(
		'$set' => array('entries.' . (string)$userentryindex . '.bets' => $userentry['bets'])
		)
	);
	$pools->update(
		array('_id' => $pool['_id']),
		array(
			'$pushAll' => array('actions' => $actions)
		)
	);
	
	redirect();
}
