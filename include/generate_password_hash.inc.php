<?php

require_once(TOTE_INCLUDEDIR . 'generate_salt.inc.php');

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
