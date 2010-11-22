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
			$actions = array();
			foreach ($modusers as $adduser) {
				$adduserid = new MongoId($adduser);
				$pools->update(array('_id' => $pool['_id']), array('$push' => array('entries' => array('user' => $adduserid))));
				$adduserobj = get_user($adduserid);
				$addusername = user_readable_name($adduserobj);
				$actions[] = array(
					'action' => 'addentrant',
					'user' => $adduserid,
					'user_name' => $addusername,
					'admin' => $user['_id'],
					'admin_name' => $adminusername,
					'time' => new MongoDate(time())
				);
			}
			$pools->update(array('_id' => $pool['_id']), array('$pushAll' => array('actions' => $actions)));
			break;
		case 'remove':
			$actions = array();
			foreach ($modusers as $removeuser) {
				$removeuserid = new MongoId($removeuser);
				$pools->update(array('_id' => $pool['_id']), array('$pull' => array('entries' => array('user' => $removeuserid))));
				$removeuserobj = get_user($removeuserid);
				$removeusername = user_readable_name($removeuserobj);
				$actions[] = array(
					'action' => 'removeentrant',
					'user' => new MongoId($removeuser),
					'user_name' => $removeusername,
					'admin' => $user['_id'],
					'admin_name' => $adminusername,
					'time' => new MongoDate(time())
				);
			}
			$pools->update(array('_id' => $pool['_id']), array('$pushAll' => array('actions' => $actions)));
			break;
		default:
			echo "Unknown modification";
			return;
	}

}
