<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

function display_adduser($username, $firstname, $lastname, $email, $password, $password2)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (!user_is_admin($user)) {
		return redirect();
	}

	$users = get_collection(TOTE_COLLECTION_USERS);

	$errors = array();
	if (empty($username)) {
		$errors[] = "Username is required";
	} else {
		$existinguser = $users->findOne(array('username' => $username), array('username', 'email'));
		if ($existinguser)
			$errors[] = "A user with that username already exists";
	}

	if (empty($email)) {
		$errors[] = "Email is required";
	} else {
		$existinguser = $users->findOne(array('email' => $email), array('username', 'email'));
		if ($existinguser)
			$errors[] = "A user with that email address already exists";
		if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $email)) {
			$errors[] = "Email must be valid";
		}
	}

	if (empty($password)) {
		$errors[] = "Password is required";
	}

	if (empty($password2)) {
		$errors[] = "Password confirmation is required";
	}

	if (!(empty($password) || empty($password2))) {
		if ($password != $password2)
			$errors[] = "Passwords must match";
	}

	if (count($errors) > 0) {
		$tpl->assign("errors", $errors);
		if (!empty($firstname))
			$tpl->assign('firstname', $firstname);
		if (!empty($lastname))
			$tpl->assign('lastname', $lastname);
		if (!empty($username))
			$tpl->assign('username', $username);
		if (!empty($email))
			$tpl->assign('email', $email);
		$tpl->display('newuser.tpl');
	} else {
		$data = array();
		$data['username'] = $username;
		$data['email'] = $email;
		if (!empty($firstname))
			$data['first_name'] = $firstname;
		if (!empty($lastname))
			$data['last_name'] = $lastname;
		$hashdata = generate_password_hash($username, $password);
		$data['salt'] = $hashdata['salt'];
		$data['password'] = $hashdata['passwordhash'];
		$users->insert($data);
		redirect(array('a' => 'editusers'));
	}

}
