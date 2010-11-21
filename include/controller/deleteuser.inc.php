<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_deleteuser($userid)
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);
	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin', 'first_name', 'last_name'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($userid)) {
		echo "User to delete is required";
		return;
	}

	$deleteuser = $users->findOne(array('_id' => new MongoId($userid)), array('username', 'first_name', 'last_name'));
	if (!$deleteuser) {
		echo "Could not find user to delete";
		return;
	}

	$adminname = $user['username'];
	if (!empty($user['first_name'])) {
		$adminname = $user['first_name'];
		if (!empty($user['last_name']))
			$adminname .= ' ' . $user['last_name'];
	}

	$username = $deleteuser['username'];
	if (!empty($deleteuser['first_name'])) {
		$username = $deleteuser['first_name'];
		if (!empty($deleteuser['last_name']))
			$username .= ' ' . $deleteuser['last_name'];
	}

	$action = array(
		'action' => 'removeentrant',
		'user' => $deleteuser['_id'],
		'user_name' => $username,
		'admin' => $user['_id'],
		'admin_name' => $username,
	);
		
	$enteredpools = $pools->find(array('entries.user' => $deleteuser['_id']), array('name', 'season'));
	foreach ($enteredpools as $p) {
		$action['time'] = new MongoDate(time());
		$pools->update(array('_id' => $p['_id']), array('$pull' => array('entries' => array('user' => $deleteuser['_id'])), '$push' => array('actions' => $action)));
	}

	$users->remove(array('_id' => $deleteuser['_id']));

	header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?a=editusers');
}
