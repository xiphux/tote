<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_password_valid.inc.php');

function display_finishlogin($user, $pass)
{
	global $tpl;

	if (user_logged_in()) {
		return redirect();
	}

	$errors = array();

	if (empty($user)) {
		$errors[] = 'Username is required';
	}

	if (empty($pass)) {
		$errors[] = 'Password is required';
	}

	if (!(empty($user) || empty($pass))) {
		if (user_password_valid($user, $pass))
			$_SESSION['user'] = $user;
		else
			$errors[] = 'Incorrect username or password';
	}

	if (count($errors) > 0) {
		$tpl->assign('errors', $errors);
		$tpl->display('login.tpl');
	} else {
		redirect();
	}
}

