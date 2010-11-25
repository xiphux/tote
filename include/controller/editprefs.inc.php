<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

/**
 * editprefs
 *
 * display page for user to edit preferences
 */
function display_editprefs()
{
	global $tpl, $tote_conf;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	// reminder time is stored internally in seconds, convert
	// it to display in hours
	if (!empty($user['remindertime']))
		$user['remindertime'] = (int)($user['remindertime'] / 3600);

	$tpl->assign('user', $user);

	// set default values
	$tpl->assign('defaulttimezone', 'America/New_York');
	$tpl->assign('defaultremindertime', 1);

	// display reminder settings if they're enabled in the config
	if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true))
		$tpl->assign('enablereminders', true);

	// only makes sense to choose from US timezones at the moment
	$tpl->assign('availabletimezones', DateTimeZone::listIdentifiers(2));

	$tpl->assign('csrftoken', $_SESSION['csrftoken']);

	$tpl->display('editprefs.tpl');

}
