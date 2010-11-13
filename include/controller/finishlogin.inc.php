<?php

function display_finishlogin($user, $pass)
{
	global $tpl, $db, $tote_conf;

	if (isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$errors = array();

	if (empty($user)) {
		$errors[] = 'Username is required';
	}

	if (empty($pass)) {
		$errors[] = 'Password is required';
	}

	if (!(empty($user) || empty($pass))) {
		$usercol = 'users';
		if (!empty($tote_conf['namespace']))
			$usercol = $tote_conf['namespace'] . '.' . $usercol;

		$users = $db->selectCollection($usercol);

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
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
	}
}

