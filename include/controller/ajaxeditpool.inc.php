<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');

function display_ajaxeditpool($poolID, $modification, $modusers)
{
	if (!isset($_SESSION['user'])) {
		echo "User not logged in";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin', 'first_name', 'last_name'));
	if (!$user) {
		echo "Logged in user not found";
		return;
	}

	if (empty($user['admin'])) {
		echo "User is not an admin";
		return;
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

	if (empty($modification)) {
		echo "Modification required";
		return;
	}

	if (empty($modusers) || (count($modusers) < 1)) {
		echo "No users to modify";
		return;
	}

	$adminusername = $user['username'];
	if (!empty($user['first_name'])) {
		$adminusername = $user['first_name'];
		if (!empty($adminusername))
			$adminusername .= ' ' . $user['last_name'];
	}

	switch ($modification) {
		case 'add':
			$actions = array();
			foreach ($modusers as $adduser) {
				$adduserid = new MongoId($adduser);
				$pools->update(array('_id' => $pool['_id']), array('$push' => array('entries' => array('user' => $adduserid))));
				$adduserobj = get_user($adduserid);
				$addusername = $adduserobj['username'];
				if (!empty($adduserobj['first_name'])) {
					$addusername = $adduserobj['first_name'];
					if (!empty($adduserobj['last_name']))
						$addusername .= ' ' . $adduserobj['last_name'];
				}
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
				$removeusername = $removeuserobj['username'];
				if (!empty($removeuserobj['first_name'])) {
					$removeusername = $removeuserobj['first_name'];
					if (!empty($removeuserobj['last_name']))
						$removeusername .= ' ' . $removeuserobj['last_name'];
				}
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
