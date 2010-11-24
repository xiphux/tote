<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_password_valid.inc.php');

/**
 * finishlogin controller
 *
 * after user enters login credentials, validate them and do login
 *
 * @param string $user username
 * @param string $pass password
 */
function display_finishlogin($user, $pass)
{
	global $tpl;

	if (user_logged_in()) {
		// user must be logged in
		return redirect();
	}

	$errors = array();

	if (empty($user)) {
		// need username
		$errors[] = 'Username is required';
	}

	if (empty($pass)) {
		// need password
		$errors[] = 'Password is required';
	}

	if (!(empty($user) || empty($pass))) {
		if (user_password_valid($user, $pass)) {
			// if username and password are valid,
			// store user logged in in session
			$_SESSION['user'] = $user;
		} else {
			$errors[] = 'Incorrect username or password';
		}
	}

	if (count($errors) > 0) {
		// if login failed, send back to login page
		// and display errors
		$tpl->assign('errors', $errors);
		$tpl->display('login.tpl');
	} else {
		// login successful, go back home
		redirect();
	}
}

