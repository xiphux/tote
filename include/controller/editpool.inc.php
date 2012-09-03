<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('EDITPOOL_HEADER', 'Manage Your Pool');

/**
 * editpool controller
 *
 * page to edit entrants/settings for a pool
 *
 * @param string $poolID pool id
 */
function display_editpool($poolID)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// must be logged in to add a user
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to add a user
		return redirect();
	}

	if (empty($poolID)) {
		// need to know the pool
		display_message("Pool is required", EDITPOOL_HEADER);
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);

	$pool = $pools->findOne(
		array(
			'_id' => new MongoId($poolID)
		),
		array('season', 'name', 'entries', 'administrators')
	);
	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", EDITPOOL_HEADER);
		return;
	}

	// get all users in the system
	$allusers = $users->find(
		array(),
		array('username', 'first_name', 'last_name', 'email')
	);

	// set all users as "available" (not in pool)
	$availableusers = array();
	foreach ($allusers as $eachuser) {
		$availableusers[(string)$eachuser['_id']] = $eachuser;
	}

	$poolusers = array();
	if (!empty($pool['entries'])) {
		foreach ($pool['entries'] as $entrant) {
			// for each entrant in the pool, move them from the available
			// users list to the pool user list
			$poolusers[(string)$entrant['user']] = $availableusers[(string)$entrant['user']];
			if (!empty($entrant['bets']) && (count($entrant['bets']) > 0)) {
				// flag a user if they have bets, so we can make that clear
				// in the admin page
				$poolusers[(string)$entrant['user']]['hasbets'] = true;
			}

			if (isset($pool['administrators'])) {
				foreach ($pool['administrators'] as $admin) {
					if ($admin['user'] == $entrant['user']) {
						if (isset($admin['secondary']) && ($admin['secondary'] == true)) {
							$poolusers[(string)$entrant['user']]['secondaryadmin'] = true;
						} else {
							$poolusers[(string)$entrant['user']]['primaryadmin'] = true;
						}
						break;
					}
				}
			}

			unset($availableusers[(string)$entrant['user']]);
		}
	}

	// set data and display
	http_headers();
	if (count($poolusers) > 0) {
		uasort($poolusers, 'sort_users');
		$tpl->assign('poolusers', $poolusers);
	}
	if (count($availableusers) > 0) {
		uasort($availableusers, 'sort_users');
		$tpl->assign('availableusers', $availableusers);
	}
	$tpl->assign('pool', $pool);
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->display('editpool.tpl');

}
