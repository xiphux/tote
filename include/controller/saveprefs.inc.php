<?php

function display_saveprefs($timezone)
{
	global $tpl, $db, $tote_conf;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$usercol = 'users';
	$users = $db->selectCollection($usercol);
	$userobj = $users->findOne(array('username' => $_SESSION['user']), array('timezone'));

	if (!$userobj) {
		echo "User not found";
		return;
	}

	$errors = array();
	if (!empty($timezone)) {
		$users->update(array('_id' => $userobj['_id']), array('$set' => array('timezone' => $timezone)));
	} else {
		$users->update(array('_id' => $userobj['_id']), array('$unset' => array('timezone' => 1)));
	}

	if (count($errors) > 0) {
		$tpl->assign('errors', $errors);
		require_once(TOTE_CONTROLLERDIR . 'editprefs.inc.php');
		display_editprefs();
	} else {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
	}

}
