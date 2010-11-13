<?php

function display_resetpass($key)
{
	global $db, $tote_conf, $tpl;

	$errors = array();

	if (empty($key)) {
		$errors[] = 'A recovery key is required';
	} else {
		$usercol = 'users';
		if (!empty($tote_conf['namespace']))
			$usercol = $tote_conf['namespace'] . '.' . $usercol;

		$users = $db->selectCollection($usercol);

		$userobj = $users->findOne(array('recoverykey' => $key));
		if (!$userobj) {
			$errors[] = 'Invalid key.  It may have been used already.';
		}
	}

	if (count($errors) > 0) {
		$tpl->assign('errors', $errors);
		$tpl->display('resetpasserrors.tpl');
	} else {
		$tpl->assign('key', $key);
		$tpl->display('resetpass.tpl');
	}

}
