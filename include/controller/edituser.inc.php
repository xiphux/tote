<?php

function display_edituser($userid)
{
	global $db, $tote_conf, $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$usercol = 'users';
	if (!empty($tote_conf['namespace'])) {
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
	}

	$users = $db->selectCollection($usercol);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($userid)) {
		echo "User required";
		return;
	}

	$edituser = $users->findOne(array('_id' => new MongoId($userid)), array('username', 'admin', 'first_name', 'last_name', 'email'));
	if (!$edituser) {
		echo "User not found";
		return;
	}

	$tpl->assign('username', $edituser['username']);
	if (!empty($edituser['first_name']))
		$tpl->assign('firstname', $edituser['first_name']);
	if (!empty($edituser['last_name']))
		$tpl->assign('lastname', $edituser['last_name']);
	if (!empty($edituser['email']))
		$tpl->assign('email', $edituser['email']);
	if (!empty($edituser['admin']) && ($edituser['admin'] == true))
		$tpl->assign('admin', true);
	$tpl->assign('userid', $userid);
	$tpl->display('edituser.tpl');

}
