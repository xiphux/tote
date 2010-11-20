<?php

function display_ajaxeditpool($poolID, $modification, $modusers)
{
	global $db, $tote_conf;

	if (!isset($_SESSION['user'])) {
		echo "User not logged in";
		return;
	}

	$poolcol = 'pools';
	$usercol = 'users';
	if (!empty($tote_conf['namespace'])) {
		$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
	}

	$pools = $db->selectCollection($poolcol);
	$users = $db->selectCollection($usercol);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
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

	switch ($modification) {
		case 'add':
			$actions = array();
			foreach ($modusers as $adduser) {
				$pools->update(array('_id' => $pool['_id']), array('$push' => array('entries' => array('user' => new MongoId($adduser)))));
				$actions[] = array(
					'action' => 'addentrant',
					'user' => new MongoId($adduser),
					'admin' => $user['_id'],
					'time' => new MongoDate(time())
				);
			}
			$pools->update(array('_id' => $pool['_id']), array('$pushAll' => array('actions' => $actions)));
			break;
		case 'remove':
			$actions = array();
			foreach ($modusers as $removeuser) {
				$pools->update(array('_id' => $pool['_id']), array('$pull' => array('entries' => array('user' => new MongoId($removeuser)))));
				$actions[] = array(
					'action' => 'removeentrant',
					'user' => new MongoId($removeuser),
					'admin' => $user['_id'],
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
