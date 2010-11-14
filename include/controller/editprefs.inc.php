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
	$userobj = $users->findOne(array('username' => $_SESSION['user']), array('timezone', 'reminder', 'remindertime'));

	if (!$userobj) {
		echo "User not found";
		return;
	}

	if (!empty($userobj['remindertime']))
		$userobj['remindertime'] = (int)($userobj['remindertime'] / 60);

	$tpl->assign('user', $userobj);

	$tpl->assign('defaulttimezone', 'America/New_York');
	$tpl->assign('defaultremindertime', 60);

	if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true))
		$tpl->assign('enablereminders', true);

	$tpl->assign('availabletimezones', DateTimeZone::listIdentifiers(DateTimeZone::AMERICA));

	$tpl->display('editprefs.tpl');

}
