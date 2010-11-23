<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

/**
 * ajaxeditpool controller
 *
 * perform AJAX asynchronous pool modifications
 *
 * @param string $poolID pool ID
 * @param string $modification type of modification to do
 * @param array $modusers list of users being modified
 */
function display_ajaxeditpool($poolID, $modification, $modusers)
{
	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		echo "User not logged in";
		return;
	}

	if (!user_is_admin($user)) {
		// need to be an admin to edit the pool
		echo "User is not an admin";
		return;
	}

	if (empty($poolID)) {
		// must have a pool to edit
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (empty($modification)) {
		// need to know what to do
		echo "Modification required";
		return;
	}

	if (empty($modusers) || (count($modusers) < 1)) {
		// need at least 1 user to modify
		echo "No users to modify";
		return;
	}

	$adminusername = user_readable_name($user);

	switch ($modification) {
		case 'add':
		case 'remove':
			$actions = array();
			$moduserdata = array();
			foreach ($modusers as $muser) {
				// for each user, set up the modification
				// and the audit log entry
				$muserid = new MongoId($muser);
				if ($modification == 'add') {
					$moduserdata[] = array('user' => $muserid);
				} else if ($modification == 'remove') {
					$moduserdata[] = $muserid;
				}
				$muserobj = get_user($muserid);
				$musername = user_readable_name($muserobj);
				$action = array(
					'action' => 'addentrant',
					'user' => $muserid,
					'user_name' => $musername,
					'admin' => $user['_id'],
					'admin_name' => $adminusername,
					'time' => new MongoDate(time())
				);
				if ($modification == 'add') {
					$action['action'] = 'addentrant';
					$actions[] = $action;
				} else if ($modification == 'remove') {
					$action['action'] = 'removeentrant';
					$actions[] = $action;
				}
			}

			// do the modification and audit
			if ($modification == 'add') {
				$pools->update(array('_id' => $pool['_id']), array('$pushAll' => array('entries' => $moduserdata)));
			} else if ($modification == 'remove') {
				$pools->update(array('_id' => $pool['_id']), array('$pull' => array('entries' => array('user' => array('$in' => $moduserdata)))));
			}
			$pools->update(array('_id' => $pool['_id']), array('$pushAll' => array('actions' => $actions)));

			break;

		default:
			echo "Unknown modification";
			return;
	}

}
