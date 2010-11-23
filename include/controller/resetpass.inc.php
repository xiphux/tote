<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_resetpass($key)
{
	global $tpl;

	$errors = array();

	if (empty($key)) {
		$errors[] = 'A recovery key is required';
	} else {
		$users = get_collection(TOTE_COLLECTION_USERS);

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
