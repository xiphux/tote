<?php

function display_adduser($username, $firstname, $lastname, $email, $password, $password2)
{
	global $db, $tote_conf, $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$usercol = 'users';
	if (!empty($tote_conf['namespace'])) {
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
	}

	$users = $db->selectCollection($usercol);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

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
		mt_srand(microtime(true)*100000 + memory_get_usage(true));
		$salt = md5(uniqid(mt_rand(), true));
		$hash = md5($username . ':' . $password);
		$saltedHash = md5($salt . $username . $hash);
		$data['salt'] = $salt;
		$data['password'] = $saltedHash;
		$users->insert($data);
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?a=editusers');
	}

}
