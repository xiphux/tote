<?php

$errors = array();

if (empty($_POST['oldpassword'])) {
	$errors[] = 'Old password is required';
}

if (empty($_POST['newpassword'])) {
	$errors[] = 'New password is required';
}

if (empty($_POST['newpassword2'])) {
	$errors[] = 'Confirm password is required';
}

if (!(empty($_POST['oldpassword']) || empty($_POST['newpassword']) || empty($_POST['newpassword2']))) {

	if ($_POST['newpassword'] == $_POST['newpassword2']) {
		$usercol = 'users';

		$users = $db->selectCollection($usercol);

		$userobj = $users->findOne(array('username' => $_SESSION['user']));

		if ($userobj) {
			if (md5($userobj['salt'] . $userobj['username'] . md5($userobj['username'] . ':' . $_POST['oldpassword'])) == $userobj['password']) {
				mt_srand(microtime(true)*100000 + memory_get_usage(true));
				$salt = md5(uniqid(mt_rand(), true));
				$hash = md5($userobj['username'] . ':' . $_POST['newpassword']);
				$saltedHash = md5($salt . $userobj['username'] . $hash);
				$users->update(
					array('_id' => $userobj['_id']),
					array('$set' => array(
						'salt' => $salt,
						'password' => $saltedHash
					))
				);
			} else {
				$errors[] = 'Old password incorrect';
			}
		} else {
			$errors[] = 'User not found';
		}
	} else {
		$errors[] = 'Passwords don\'t match';
	}
}

if (count($errors) > 0) {
	$tpl->assign('errors', $errors);
	$tpl->display('changepass.tpl');
} else {
	$tpl->display('finishchangepass.tpl');
}
