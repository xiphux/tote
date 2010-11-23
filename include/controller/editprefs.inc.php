<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_editprefs()
{
	global $tpl, $tote_conf;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (!empty($user['remindertime']))
		$user['remindertime'] = (int)($user['remindertime'] / 3600);

	$tpl->assign('user', $user);

	$tpl->assign('defaulttimezone', 'America/New_York');
	$tpl->assign('defaultremindertime', 1);

	if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true))
		$tpl->assign('enablereminders', true);

	$tpl->assign('availabletimezones', DateTimeZone::listIdentifiers(2));

	$tpl->display('editprefs.tpl');

}
