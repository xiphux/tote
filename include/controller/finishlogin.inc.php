<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

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

		$users = get_collection(TOTE_COLLECTION_USERS);

		$userobj = $users->findOne(array('username' => $user));

		if ($userobj && (md5($userobj['salt'] . $userobj['username'] . md5($userobj['username'] . ':' . $pass)) == $userobj['password']))
			$_SESSION['user'] = $userobj['username'];
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

