<?php

$errors = array();

if (empty($_GET['k'])) {
	$errors[] = 'A recovery key is required';
} else {
	$usercol = 'users';
	if (!empty($tote_conf['namespace']))
		$usercol = $tote_conf['namespace'] . '.' . $usercol;

	$users = $db->selectCollection($usercol);

	$userobj = $users->findOne(array('recoverykey' => $_GET['k']));
	if (!$userobj) {
		$errors[] = 'Invalid key.  It may have been used already.';
	}
}

if (count($errors) > 0) {
	$tpl->assign('errors', $errors);
	$tpl->display('resetpasserrors.tpl');
} else {
	$tpl->assign('key', $_GET['k']);
	$tpl->display('resetpass.tpl');
}
