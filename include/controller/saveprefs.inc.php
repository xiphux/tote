<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('SAVEPREFS_HEADER', 'Edit Your Preferences');

/**
 * saveprefs controller
 *
 * after a user edits their preferences, save the changes into the database
 *
 * @param string $timezone timezone preference
 * @param string $reminder whether we want reminders
 * @param string $remindertime reminder time
 * @param string $resultnotification whether we want game result notifications
 * @param string $csrftoken CSRF request token
 */
function display_saveprefs($timezone, $reminder, $remindertime, $resultnotification, $style, $csrftoken)
{
	global $tpl, $tote_conf, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", SAVEPREFS_HEADER);
		return;
	}

	$errors = array();

	if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {
		// only validate reminder settings if reminders are turned on
		if ($reminder == '1') {
			if (empty($remindertime)) {
				// need the reminder time
				$errors[] = 'A reminder time is required';
			} else if (!is_numeric($remindertime)) {
				// must be numeric
				$errors[] = 'Reminder time must be a number';
			} else if ((int)$remindertime < 1) {
				// set a limit of 1 hour minimum, so we don't have
				// to run the reminder background job absurdly frequently
				$errors[] = 'Reminder time must be 1 hour or greater';
			}
		}
	}

	if (count($errors) > 0) {
		// if we have errors, go back to the preferences edit and
		// display them
		$tpl->assign('errors', $errors);
		require_once(TOTE_CONTROLLERDIR . 'editprefs.inc.php');
		display_editprefs();
	} else {

		$timezoneval = !empty($timezone) ? $timezone : null;
		$styleval = !empty($style) ? $style : null;
		$reminderval = 0;
		$remindertimeval = null;
		$resultnotificationval = ($resultnotification && $resultnotification == '1') ? 1 : 0;

		if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {
			$reminderval = $reminder ? 1 : 0;
			$remindertimeval = !empty($remindertime) ? ((int)$remindertime * 3600) : null;
		}

		$prefsstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET timezone=?, style=?, reminder=?, reminder_time=?, result_notification=? WHERE id=?');
		$prefsstmt->bind_param('ssiiii', $timezoneval, $styleval, $reminderval, $remindertimeval, $resultnotificationval, $user['id']);
		$prefsstmt->execute();
		$prefsstmt->close();

		// go home
		redirect();
	}

}
