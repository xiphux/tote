<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');

function display_finishresetpass($key, $newpassword, $newpassword2)
{
	global $tpl;

	$errors = array();

	if (empty($newpassword)) {
		$errors[] = 'New password is required';
	}

	if (empty($newpassword2)) {
		$errors[] = 'Confirm password is required';
	}

	if (empty($key)) {
		$errors[] = 'A recovery key is required';
	}

	if (!(empty($key) || empty($newpassword) || empty($newpassword2))) {
		if ($newpassword == $newpassword2) {

			$users = get_collection(TOTE_COLLECTION_USERS);

			$userobj = $users->findOne(array('recoverykey' => $key));
			if ($userobj) {
				$hashdata = generate_password_hash($userobj['username'], $newpassword);
				$users->update(
					array('_id' => $userobj['_id']),
					array('$set' => array(
						'salt' => $hashdata['salt'],
						'password' => $hashdata['passwordhash']),
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
		$tpl->assign('key', $key);
		$tpl->assign('errors', $errors);
		$tpl->display('resetpass.tpl');
	} else {
		$tpl->display('finishresetpass.tpl');
	}

}
