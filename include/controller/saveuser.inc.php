<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('SAVEUSER_HEADER', 'Edit A User');

/**
 * saveuser controller
 *
 * after editing a user, save the changes in the database
 *
 * @param string $userid user id to save
 * @param string $firstname first name
 * @param string $lastname last name
 * @param string $email email address
 * @param string $role user role
 * @param string $csrftoken CSRF request token
 */
function display_saveuser($userid, $firstname, $lastname, $email, $role, $newpassword, $newpassword2, $csrftoken)
{
	global $tpl, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to change a user
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", SAVEUSER_HEADER);
		return;
	}

	if (empty($userid)) {
		// need to know what user to edit
		display_message("User required", SAVEUSER_HEADER);
		return;
	}

	$userstmt = $mysqldb->prepare('SELECT username, first_name, last_name, email, role FROM ' . TOTE_TABLE_USERS . ' WHERE id=?');
	$userstmt->bind_param('i', $userid);
	$userstmt->execute();
	$userresult = $userstmt->get_result();
	$edituser = $userresult->fetch_assoc();
	$userresult->close();
	$userstmt->close();

	if (!$edituser) {
		// needs to be a valid user
		display_message("User not found", SAVEUSER_HEADER);
		return;
	}

	$errors = array();

	if (empty($email)) {
		// need the email address
		$errors[] = "Email is required";
	} else {
		$emailstmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_USERS . ' WHERE email=? AND id!=?');
		$emailstmt->bind_param('si', $email, $userid);
		$emailstmt->execute();
		if ($emailstmt->fetch()) {
			// no duplicate emails
			$errors[] = "A user with that email address already exists";
		}
		if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $email)) {
			// must be a valid email address
			$errors[] = "Email must be valid";
		}
	}

	if (!(empty($newpassword) && empty($newpassword2))) {
		if ($newpassword != $newpassword2) {
			$errors[] = 'Passwords don\'t match';
		}
	}

	if (count($errors) > 0) {
		// if we have errors, go back to the edit page and display them
		http_headers();
		$tpl->assign("errors", $errors);
		if (!empty($firstname))
			$tpl->assign('firstname', $firstname);
		if (!empty($lastname))
			$tpl->assign('lastname', $lastname);
		$tpl->assign('username', $edituser['username']);
		if (!empty($email))
			$tpl->assign('email', $email);
		if (!empty($role))
			$tpl->assign('role', $role);
		$tpl->assign('userid', $userid);
		$tpl->assign('csrftoken', $_SESSION['csrftoken']);
		$tpl->display('edituser.tpl');
	} else {
		// set data
		if (($firstname != $edituser['first_name']) || ($lastname != $edituser['last_name']) || ($email != $edituser['email']) || ((int)$role != (int)$edituser['role'])) {

			$firstname = !empty($firstname) ? $firstname : null;
			$lastname = !empty($lastname) ? $lastname : null;
			$email = !empty($email) ? $email : null;
			$role = $role > 0 ? (int)$role : 0;
			
			$updatestmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET first_name=?, last_name=?, email=?, role=? WHERE id=?');
			$updatestmt->bind_param('sssii', $firstname, $lastname, $email, $role, $userid);
			$updatestmt->execute();
			$updatestmt->close();
		}
		if (!(empty($newpassword) || empty($newpassword2))) {
			
			$hashdata = generate_password_hash($edituser['username'], $newpassword);
			$passstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET salt=?, password=?, last_password_change=UTC_TIMESTAMP()  WHERE id=?');
			$passstmt->bind_param('ssi', $hashdata['salt'], $hashdata['passwordhash'], $userid);
			$passstmt->execute();
			$passstmt->close();
		}

		// go back to edit users page
		redirect(array('a' => 'editusers'));
	}

}
