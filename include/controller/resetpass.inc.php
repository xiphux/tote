<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * resetpass controller
 *
 * attempt to display password reset form
 * given a recovery key
 *
 * @param string $key recovery key
 */
function display_resetpass($key)
{
	global $tpl;

	$errors = array();

	if (empty($key)) {
		// need the key
		$errors[] = 'A recovery key is required';
	} else {
		$users = get_collection(TOTE_COLLECTION_USERS);
		$userobj = $users->findOne(array('recoverykey' => $key));
		if (!$userobj) {
			// the key has to be active and valid
			$errors[] = 'Invalid key.  It may have been used already.';
		}
	}

	http_headers();

	if (count($errors) > 0) {
		// if we have errors, display them
		$tpl->assign('errors', $errors);
		$tpl->display('resetpasserrors.tpl');
	} else {
		// display password reset form
		$tpl->assign('key', $key);
		$tpl->display('resetpass.tpl');
	}

}
