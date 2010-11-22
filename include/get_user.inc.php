<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

$usercache = array();

function get_user($id)
{
	global $usercache;

	$users = get_collection(TOTE_COLLECTION_USERS);

	if (empty($usercache[(string)$id])) {
		$usercache[(string)$id] = $users->findOne(array('_id' => $id), array('username', 'admin', 'first_name', 'last_name', 'email'));
	}

	return $usercache[(string)$id];
}
