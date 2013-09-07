<?php

require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * finishresetpass controller
 *
 * after user resets pass, store new password for user
 *
 * @param string $key recovery key
 * @param string $newpassword new password
 * @param string $newpassword2 confirm password
 */
function display_finishresetpass($key, $newpassword, $newpassword2)
{
	global $tpl, $mysqldb;

	$errors = array();

	if (empty($newpassword)) {
		// need the new password
		$errors[] = 'New password is required';
	}

	if (empty($newpassword2)) {
		// need the password confirmation
		$errors[] = 'Confirm password is required';
	}

	if (empty($key)) {
		// need the recovery key
		$errors[] = 'A recovery key is required';
	}

	if (!(empty($key) || empty($newpassword) || empty($newpassword2))) {
		if ($newpassword == $newpassword2) {

			$userid = null;
			$username = null;
			$keystmt = $mysqldb->prepare('SELECT id, username FROM ' . TOTE_TABLE_USERS . ' WHERE recovery_key=?');
			$keystmt->bind_param('s', $key);
			$keystmt->bind_result($userid, $username);
			$keystmt->execute();
			$found = $keystmt->fetch();
			$keystmt->close();

			if ($found) {
				
				// hash the new password
				$hashdata = generate_password_hash($username, $newpassword);
				
				// set the new password for the user and delete the recovery key
				// (since it was used once we don't want it to be used again)
				$resetstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET salt=?, password=?, last_password_change=UTC_TIMESTAMP(), recovery_key=NULL WHERE id=?');
				$resetstmt->bind_param('ssi', $hashdata['salt'], $hashdata['passwordhash'], $userid);
				$resetstmt->execute();
				$resetstmt->close();

			} else {
				// recovery key has to exist in the database to be valid
				$errors[] = 'Invalid key.  It may have been used already.';
			}
		} else {
			// new password and confirm password need to match
			$errors[] = 'Passwords don\'t match';
		}
	}

	http_headers();
	if (count($errors) > 0) {
		// if we have errors, send user back to password reset form
		// and display them
		$tpl->assign('key', $key);
		$tpl->assign('errors', $errors);
		$tpl->display('resetpass.tpl');
	} else {
		// password reset successful
		$tpl->display('finishresetpass.tpl');
	}

}
