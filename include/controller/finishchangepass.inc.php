<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_password_valid.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('FINISHCHANGEPASS_HEADER', 'Change Your Password');

/**
 * finishchangepass
 *
 * after user changes pass, update in the database
 *
 * @param string $oldpassword old password
 * @param string $newpassword new password
 * @param string $newpassword2 confirmed new password
 * @param string $csrftoken CSRF request token
 */
function display_finishchangepass($oldpassword, $newpassword, $newpassword2, $csrftoken)
{
	global $tpl, $mysqldb;

	$user = user_logged_in();	
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", FINISHCHANGEPASS_HEADER);
		return;
	}

	$errors = array();

	if (empty($oldpassword)) {
		// need to enter the old password
		$errors[] = 'Old password is required';
	}

	if (empty($newpassword)) {
		// need to enter the new password
		$errors[] = 'New password is required';
	}

	if (empty($newpassword2)) {
		// need to confirm the new password
		$errors[] = 'Confirm password is required';
	}

	if (!(empty($oldpassword) || empty($newpassword) || empty($newpassword2))) {

		if ($newpassword == $newpassword2) {

			if (user_password_valid($user['username'], $oldpassword)) {
				// hash the new password
				$hashdata = generate_password_hash($user['username'], $newpassword);

				// update the user in the database
				$passstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET salt=?, password=?, last_password_change=UTC_TIMESTAMP() WHERE id=?');
				$passstmt->bind_param('ssi', $hashdata['salt'], $hashdata['passwordhash'], $user['id']);
				$passstmt->execute();
				$passstmt->close();
			} else {
				// old password has to be correct
				$errors[] = 'Old password incorrect';
			}
		} else {
			// new password and confirm password need to match
			$errors[] = 'Passwords don\'t match';
		}
	}

	http_headers();
	if (count($errors) > 0) {
		// if we have errors, send them back to the change pass form
		// with the errors displayed
		$tpl->assign('errors', $errors);
		$tpl->assign('csrftoken', $_SESSION['csrftoken']);
		$tpl->display('changepass.tpl');
	} else {
		// password change successful
		$tpl->display('finishchangepass.tpl');
	}
}

