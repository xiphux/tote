<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

function display_ajaxeditpool($poolID, $modification, $modusers)
{
	$user = user_logged_in();
	if (!$user) {
		echo "User not logged in";
		return;
	}

	if (!user_is_admin($user)) {
		echo "User is not an admin";
		return;
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	if (empty($modification)) {
		echo "Modification required";
		return;
	}

	if (empty($modusers) || (count($modusers) < 1)) {
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
