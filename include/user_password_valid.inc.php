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
	global $mysqldb;

	if (empty($username) || empty($password))
		return false;

	if (empty($salt) || empty($passwordHash)) {
		// if we weren't provided with salt and hash,
		// load it from the database

		$userstmt = $mysqldb->prepare('SELECT salt, password FROM ' . TOTE_TABLE_USERS . ' WHERE username=?');
		if (!$userstmt)
			return false;

		$userstmt->bind_param('s', $username);
		$userstmt->bind_result($salt, $passwordHash);
		if (!$userstmt->execute()) {
			$userstmt->close();
			return false;
		}
		if (!$userstmt->fetch()) {
			$userstmt->close();
			return false;
		}
	}

	$userstmt->close();

	return (md5($salt . $username . md5($username . ':' . $password)) == $passwordHash);
}
