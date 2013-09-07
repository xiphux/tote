<?php

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
	global $tpl, $mysqldb;

	$errors = array();

	if (empty($key)) {
		// need the key
		$errors[] = 'A recovery key is required';
	} else {
		
		$keystmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_USERS . ' WHERE recovery_key=?');
		$keystmt->bind_param('s', $key);
		$keystmt->execute();
		if (!$keystmt->fetch()) {
			// the key has to be active and valid
			$errors[] = 'Invalid key.  It may have been used already.';
		}
		$keystmt->close();
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
