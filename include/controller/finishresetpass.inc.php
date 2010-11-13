<?php

$errors = array();

if (empty($_POST['newpassword'])) {
	$errors[] = 'New password is required';
}

if (empty($_POST['newpassword2'])) {
	$errors[] = 'Confirm password is required';
}

if (empty($_POST['key'])) {
	$errors[] = 'A recovery key is required';
}

if (!(empty($_POST['key']) || empty($_POST['newpassword']) || empty($_POST['newpassword2']))) {
	if ($_POST['newpassword'] == $_POST['newpassword2']) {
		$usercol = 'users';
		if (!empty($tote_conf['namespace']))
			$usercol = $tote_conf['namespace'] . '.' . $usercol;

		$users = $db->selectCollection($usercol);

		$userobj = $users->findOne(array('recoverykey' => $_POST['key']));
		if ($userobj) {
			mt_srand(microtime(true)*100000 + memory_get_usage(true));
			$salt = md5(uniqid(mt_rand(), true));
			$hash = md5($userobj['username'] . ':' . $_POST['newpassword']);
			$saltedHash = md5($salt . $userobj['username'] . $hash);
			$users->update(
				array('_id' => $userobj['_id']),
				array('$set' => array(
					'salt' => $salt,
					'password' => $saltedHash),
				      '$unset' => array(
				      	'recoverykey' => 1
					)
				)
			);
		} else {
			$errors[] = 'Invalid key.  It may have been used already.';
		}
	} else {
		$errors[] = 'Passwords don\'t match';
	}
}

if (count($errors) > 0) {
	$tpl->assign('key', $_POST['key']);
	$tpl->assign('errors', $errors);
	$tpl->display('resetpass.tpl');
} else {
	$tpl->display('finishresetpass.tpl');
}
