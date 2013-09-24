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
	global $tpl, $db;

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

	$userstmt = $db->prepare('SELECT username, first_name, last_name, email, role FROM ' . TOTE_TABLE_USERS . ' WHERE id=:user_id');
	$userstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$userstmt->execute();
	$edituser = $userstmt->fetch(PDO::FETCH_ASSOC);
	$userstmt = null;

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
		$emailstmt = $db->prepare('SELECT id FROM ' . TOTE_TABLE_USERS . ' WHERE email=:email AND id!=:user_id');
		$emailstmt->bindParam(':email', $email);
		$emailstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
		$existingid = null;
		$emailstmt->bindColumn(1, $existingid);
		$emailstmt->execute();
		if ($emailstmt->fetch(PDO::FETCH_BOUND)) {
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

		$db->beginTransaction();

		if (($firstname != $edituser['first_name']) || ($lastname != $edituser['last_name']) || ($email != $edituser['email']) || ((int)$role != (int)$edituser['role'])) {

			$firstname = !empty($firstname) ? $firstname : null;
			$lastname = !empty($lastname) ? $lastname : null;
			$email = !empty($email) ? $email : null;
			$role = $role > 0 ? (int)$role : 0;
			
			$updatestmt = $db->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET first_name=:first_name, last_name=:last_name, email=:email, role=:role WHERE id=:user_id');
			$updatestmt->bindParam(':first_name', $firstname);
			$updatestmt->bindParam(':last_name', $lastname);
			$updatestmt->bindParam(':email', $email);
			$updatestmt->bindParam(':role', $role, PDO::PARAM_INT);
			$updatestmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
			$updatestmt->execute();
			$updatestmt = null;
		}
		if (!(empty($newpassword) || empty($newpassword2))) {
			
			$hashdata = generate_password_hash($edituser['username'], $newpassword);
			$passstmt = $db->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET salt=:salt, password=:password, last_password_change=UTC_TIMESTAMP()  WHERE id=:user_id');
			$passstmt->bindParam(':salt', $hashdata['salt']);
			$passstmt->bindParam(':password', $hashdata['passwordhash']);
			$passstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
			$passstmt->execute();
			$passstmt = null;
		}

		$db->commit();

		// go back to edit users page
		redirect(array('a' => 'editusers'));
	}

}
