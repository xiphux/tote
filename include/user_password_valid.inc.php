<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Tests if user's password is valid
 *
 * @param string $username username
 * @param string $password password to test
 * @param string $salt user's password salt
 * @param string $passwordHash user's password hash
 */
function user_password_valid($username, $password, $salt = '', $passwordHash = '')
{
	if (empty($username) || empty($password))
		return false;

	if (empty($salt) || empty($passwordHash)) {
		// if we weren't provided with salt and hash,
		// load it from the database
		$users = get_collection(TOTE_COLLECTION_USERS);
		$user = $users->findOne(array('username' => $username), array('salt', 'password'));
		if (!$user)
			return false;

		$salt = $user['salt'];
		$passwordHash = $user['password'];
	}

	return (md5($salt . $username . md5($username . ':' . $password)) == $passwordHash);
}
