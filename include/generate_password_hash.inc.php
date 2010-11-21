<?php

function generate_password_hash($username, $password)
{
	$ret = array();

	mt_srand(microtime(true)*100000 + memory_get_usage(true));

	$salt = md5(uniqid(mt_rand(), true));
	$hash = md5($username . ':' . $password);
	$saltedPass = md5($salt . $username . $hash);

	$ret['salt'] = $salt;
	$ret['passwordhash'] = $saltedPass;

	return $ret;
}
