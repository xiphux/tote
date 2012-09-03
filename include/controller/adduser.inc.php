<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
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
	global $tpl;

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

	$users = get_collection(TOTE_COLLECTION_USERS);

	$errors = array();
	if (empty($username)) {
		// must have a username
		$errors[] = "Username is required";
	} else {
		$existinguser = $users->findOne(
			array('username' => strtolower($username)),
			array('username', 'email')
		);
		if ($existinguser) {
			// no duplicate usernames
			$errors[] = "A user with that username already exists";
		}
	}

	if (empty($email)) {
		// must have an email
		$errors[] = "Email is required";
	} else {
		$existinguser = $users->findOne(
			array('email' => $email),
			array('username', 'email')
		);
		if ($existinguser) {
			// no duplicate emails
			$errors[] = "A user with that email address already exists";
		}
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
		$data = array();
		$data['username'] = strtolower($username);
		$data['email'] = $email;
		if (!empty($firstname))
			$data['first_name'] = $firstname;
		if (!empty($lastname))
			$data['last_name'] = $lastname;
		$hashdata = generate_password_hash($data['username'], $password);
		$data['salt'] = $hashdata['salt'];
		$data['password'] = $hashdata['passwordhash'];
		$data['created'] = new MongoDate();
		$users->insert($data);

		// go back to the edit users page
		redirect(array('a' => 'editusers'));
	}

}
