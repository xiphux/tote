<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_finishchangepass($oldpassword, $newpassword, $newpassword2)
{
	global $tpl;

	$user = user_logged_in();	
	if (!$user) {
		return redirect();
	}

	$errors = array();

	if (empty($oldpassword)) {
		$errors[] = 'Old password is required';
	}

	if (empty($newpassword)) {
		$errors[] = 'New password is required';
	}

	if (empty($newpassword2)) {
		$errors[] = 'Confirm password is required';
	}

	if (!(empty($oldpassword) || empty($newpassword) || empty($newpassword2))) {

		if ($newpassword == $newpassword2) {

			if (md5($user['salt'] . $user['username'] . md5($user['username'] . ':' . $oldpassword)) == $user['password']) {
				$hashdata = generate_password_hash($user['username'], $newpassword);
				$users = get_collection(TOTE_COLLECTION_USERS);

				$users->update(
					array('_id' => $user['_id']),
					array('$set' => array(
						'salt' => $hashdata['salt'],
						'password' => $hashdata['passwordhash']
					))
				);
			} else {
				$errors[] = 'Old password incorrect';
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
}

