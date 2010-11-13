<?php

$errors = array();

if (empty($_POST['username'])) {
	$errors[] = 'Username is required';
}

if (empty($_POST['password'])) {
	$errors[] = 'Password is required';
}

if (!(empty($_POST['username']) || empty($_POST['password']))) {
	$usercol = 'users';
	if (!empty($tote_conf['namespace']))
		$usercol = $tote_conf['namespace'] . '.' . $usercol;

	$users = $db->selectCollection($usercol);

	$userobj = $users->findOne(array('username' => $_POST['username']));

	if ($userobj && (md5($userobj['salt'] . $userobj['username'] . md5($userobj['username'] . ':' . $_POST['password'])) == $userobj['password']))
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
