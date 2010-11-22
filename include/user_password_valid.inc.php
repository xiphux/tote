<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function user_password_valid($username, $password, $salt = '', $passwordHash = '')
{
	if (empty($username) || empty($password))
		return false;

	if (empty($salt) || empty($passwordHash)) {
		$users = get_collection(TOTE_COLLECTION_USERS);
		$user = $users->findOne(array('username' => $username), array('salt', 'password'));
		if (!$user)
			return false;

		$salt = $user['salt'];
		$passwordHash = $user['password'];
	}

	return (md5($salt . $username . md5($username . ':' . $password)) == $passwordHash);
}
