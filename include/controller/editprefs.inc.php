<?php

function display_editprefs()
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

	if (!empty($userobj['timezone']))
		$tpl->assign('usertimezone', $userobj['timezone']);

	$tpl->assign('defaulttimezone', 'America/New_York');

	$tpl->assign('availabletimezones', DateTimeZone::listIdentifiers(DateTimeZone::AMERICA));

	$tpl->display('editprefs.tpl');

}
