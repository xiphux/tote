<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_editprefs()
{
	global $tpl, $tote_conf;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);
	$userobj = $users->findOne(array('username' => $_SESSION['user']), array('timezone', 'reminder', 'remindertime'));

	if (!$userobj) {
		echo "User not found";
		return;
	}

	if (!empty($userobj['remindertime']))
		$userobj['remindertime'] = (int)($userobj['remindertime'] / 3600);

	$tpl->assign('user', $userobj);

	$tpl->assign('defaulttimezone', 'America/New_York');
	$tpl->assign('defaultremindertime', 1);

	if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true))
		$tpl->assign('enablereminders', true);

	$tpl->assign('availabletimezones', DateTimeZone::listIdentifiers(2));

	$tpl->display('editprefs.tpl');

}
