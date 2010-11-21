<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_saveprefs($timezone, $reminder, $remindertime)
{
	global $tpl, $tote_conf;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);
	$userobj = $users->findOne(array('username' => $_SESSION['user']), array('timezone'));

	if (!$userobj) {
		echo "User not found";
		return;
	}

	$errors = array();

	if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {
		if ($reminder == '1') {
			if (empty($remindertime)) {
				$errors[] = 'A reminder time is required';
			} else if (!is_numeric($remindertime)) {
				$errors[] = 'Reminder time must be a number';
			} else if ((int)$remindertime < 1) {
				$errors[] = 'Reminder time must be 1 hour or greater';
			}
		}
	}

	if (count($errors) > 0) {
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

		if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {
			if ($reminder) {
				$data['$set']['reminder'] = true;
				$data['$set']['remindertime'] = (int)$remindertime * 3600;
			} else {
				$data['$unset']['reminder'] = 1;
				$data['$unset']['lastreminder'] = 1;
			}
		}

		$users->update(array('_id' => $userobj['_id']), $data);
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
	}

}
