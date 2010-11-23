<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_password_valid.inc.php');

/**
 * finishchangepass
 *
 * after user changes pass, update in the database
 *
 * @param string $oldpassword old password
 * @param string $newpassword new password
 * @param string $newpassword2 confirmed new password
 */
function display_finishchangepass($oldpassword, $newpassword, $newpassword2)
{
	global $tpl;

	$user = user_logged_in();	
	if (!$user) {
		// user must be logged in
		return redirect();
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

			if (user_password_valid($user['username'], $oldpassword, $user['salt'], $user['password'])) {
				// hash the new password
				$hashdata = generate_password_hash($user['username'], $newpassword);

				// update the user in the database
				$users = get_collection(TOTE_COLLECTION_USERS);
				$users->update(
					array('_id' => $user['_id']),
					array('$set' => array(
						'salt' => $hashdata['salt'],
						'password' => $hashdata['passwordhash']
					))
				);
			} else {
				// old password has to be correct
				$errors[] = 'Old password incorrect';
			}
		} else {
			// new password and confirm password need to match
			$errors[] = 'Passwords don\'t match';
		}
	}

	if (count($errors) > 0) {
		// if we have errors, send them back to the change pass form
		// with the errors displayed
		$tpl->assign('errors', $errors);
		$tpl->display('changepass.tpl');
	} else {
		// password change successful
		$tpl->display('finishchangepass.tpl');
	}
}

