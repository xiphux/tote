<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_finishchangepass($oldpassword, $newpassword, $newpassword2)
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
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

			$users = get_collection(TOTE_COLLECTION_USERS);

			$userobj = $users->findOne(array('username' => $_SESSION['user']));

			if ($userobj) {
				if (md5($userobj['salt'] . $userobj['username'] . md5($userobj['username'] . ':' . $oldpassword)) == $userobj['password']) {
					mt_srand(microtime(true)*100000 + memory_get_usage(true));
					$salt = md5(uniqid(mt_rand(), true));
					$hash = md5($userobj['username'] . ':' . $newpassword);
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
}

