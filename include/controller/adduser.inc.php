<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('ADDUSER_HEADER', 'Add A New User');

/**
 * adduser controller
 *
 * add a user to the database

 * @param string $username username
 * @param string $firstname first name
 * @param string $lastname last name
 * @param string $email email address
 * @param string $password password
 * @param string $password2 confirm password
 * @param string $csrftoken CSRF request token
 */
function display_adduser($username, $firstname, $lastname, $email, $password, $password2, $csrftoken)
{
	global $tpl, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// must be logged in to add a user
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to add a user
		return redirect();
	}
	
	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", ADDUSER_HEADER);
		return;
	}

	$lowerusername = strtolower($username);

	$errors = array();
	if (empty($username)) {
		// must have a username
		$errors[] = "Username is required";
	} else {
		$usernamestmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_USERS . ' WHERE username=?');
		$usernamestmt->bind_param('s', $lowerusername);
		$usernamestmt->execute();
		if ($usernamestmt->fetch()) {
			// no duplicate usernames
			$errors[] = "A user with that username already exists";
		}
		$usernamestmt->close();
	}

	if (empty($email)) {
		// must have an email
		$errors[] = "Email is required";
	} else {
		$emailstmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_USERS . ' WHERE email=?');
		$emailstmt->bind_param('s', $email);
		$emailstmt->execute();
		if ($emailstmt->fetch()) {
			// no duplicate emails
			$errors[] = "A user with that email address already exists";
		}
		$emailstmt->close();
		if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $email)) {
			// must be validly formatted email
			$errors[] = "Email must be valid";
		}
	}

	if (empty($password)) {
		// password is required
		$errors[] = "Password is required";
	}

	if (empty($password2)) {
		// confirm is required
		$errors[] = "Password confirmation is required";
	}

	if (!(empty($password) || empty($password2))) {
		if ($password != $password2) {
			// password and confirm must match
			$errors[] = "Passwords must match";
		}
	}

	if (count($errors) > 0) {
		// if we have any errors, send the user back to the
		// form with the data filled out, and display errors
		http_headers();
		$tpl->assign("errors", $errors);
		if (!empty($firstname))
			$tpl->assign('firstname', $firstname);
		if (!empty($lastname))
			$tpl->assign('lastname', $lastname);
		if (!empty($username))
			$tpl->assign('username', $username);
		if (!empty($email))
			$tpl->assign('email', $email);
		$tpl->assign('csrftoken', $_SESSION['csrftoken']);
		$tpl->display('newuser.tpl');
	} else {
		// insert user into database
		$firstname = !empty($firstname) ? $firstname : null;
		$lastname = !empty($lastname) ? $lastname : null;

		$hashdata = generate_password_hash($lowerusername, $password);

		$newuserstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_USERS . ' (username, email, first_name, last_name, salt, password, created) VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())');
		$newuserstmt->bind_param('ssssss', $lowerusername, $email, $firstname, $lastname, $hashdata['salt'], $hashdata['passwordhash']);
		$newuserstmt->execute();
		$newuserstmt->close();

		// go back to the edit users page
		redirect(array('a' => 'editusers'));
	}

}
