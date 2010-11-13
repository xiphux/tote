<?php

$key = '';
$username = '';
$errors = array();

if (empty($_POST['email'])) {
	$errors[] = 'Email is required';
} else {
	$usercol = 'users';
	if (!empty($tote_conf['namespace']))
		$usercol = $tote_conf['namespace'] . '.' . $usercol;

	$users = $db->selectCollection($usercol);

	$userobj = $users->findOne(array('email' => $_POST['email']));
	if ($userobj) {
		mt_srand(microtime(true)*100000 + memory_get_usage(true));
		$key = md5(uniqid(mt_rand(), true));
		$users->update(
			array('_id' => $userobj['_id']),
			array('$set' => array('recoverykey' => $key))
		);
		$username = $userobj['username'];
	} else {
		$errors[] = 'That email was not found in the system';
	}
}

if (count($errors) > 0) {
	$tpl->assign('errors', $errors);
	$tpl->display('recoverpass.tpl');
} else {
	$tpl->assign('username', $username);
	$tpl->assign('sitename', $tote_conf['sitename']);
	$tpl->assign('url', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?a=resetpass&k=' . $key);
	$email = $tpl->fetch('recoverpassemail.tpl');
	$subject = 'Password recovery for ' . $tote_conf['sitename'];
	$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
		'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	mail($_POST['email'], $subject, $email, $headers);

	$tpl->assign('email', $_POST['email']);
	$tpl->display('finishrecoverpass.tpl');
}
