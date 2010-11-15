<?php

$usercache = array();

function get_user($id)
{
	global $usercache, $tote_conf, $db;

	$usercol = 'users';
	if (!empty($tote_conf['namespace']))
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
	$users = $db->selectCollection($usercol);

	if (empty($usercache[(string)$id])) {
		$usercache[(string)$id] = $users->findOne(array('_id' => $id), array('username', 'first_name', 'last_name', 'email'));
	}

	return $usercache[(string)$id];
}
