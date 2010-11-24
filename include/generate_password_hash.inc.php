<?php

require_once(TOTE_INCLUDEDIR . 'generate_salt.inc.php');

/**
 * Generates a one-way hashed password for storage in database
 *
 * @param string username username
 * @param string password password
 * @return array array with data: index 'salt' contains salt, index 'passwordhash' contains password
 */
function generate_password_hash($username, $password)
{
	$ret = array();

	$salt = generate_salt();
	$hash = md5($username . ':' . $password);
	$saltedPass = md5($salt . $username . $hash);

	$ret['salt'] = $salt;
	$ret['passwordhash'] = $saltedPass;

	return $ret;
}
