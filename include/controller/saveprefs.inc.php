<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
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
	global $tpl, $tote_conf;

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

		$data = array();

		if (!empty($timezone)) {
			$data['$set']['timezone'] = $timezone;
		} else {
			$data['$unset']['timezone'] = 1;
		}

		if (!empty($style)) {
			$data['$set']['style'] = $style;
		} else {
			$data['$unset']['style'] = 1;
		}

		if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {
			// only save reminder settings if reminders are turned on

			if ($reminder) {
				$data['$set']['reminder'] = true;
				// store reminder time in seconds internally
				$data['$set']['remindertime'] = (int)$remindertime * 3600;
			} else {
				$data['$unset']['reminder'] = 1;
				$data['$unset']['lastreminder'] = 1;
			}
		}

		if ($resultnotification && $resultnotification == '1') {
			$data['$set']['resultnotification'] = true;
		} else {
			$data['$unset']['resultnotification'] = 1;
		}

		$users = get_collection(TOTE_COLLECTION_USERS);

		// do the update
		$users->update(array('_id' => $user['_id']), $data);

		// go home
		redirect();
	}

}
