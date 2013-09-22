<?php

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
	global $db;

	if (empty($username) || empty($password))
		return false;

	if (empty($salt) || empty($passwordHash)) {
		// if we weren't provided with salt and hash,
		// load it from the database

		$userstmt = $db->prepare('SELECT salt, password FROM ' . TOTE_TABLE_USERS . ' WHERE username=:username');
		if (!$userstmt)
			return false;

		$userstmt->bindParam(':username', $username);
		$userstmt->execute();
		$userstmt->bindColumn(1, $salt);
		$userstmt->bindColumn(2, $passwordHash);
		$found = $userstmt->fetch(PDO::FETCH_BOUND);
		$userstmt = null;
		if (!$found) {
			return false;
		}
	}


	return (md5($salt . $username . md5($username . ':' . $password)) == $passwordHash);
}
