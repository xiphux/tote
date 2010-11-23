<?php

require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function user_logged_in()
{
	global $usercache;

	if (empty($_SESSION['user']))
		return null;

	$users = get_collection(TOTE_COLLECTION_USERS);

	$user = $users->findOne(array('username' => $_SESSION['user']));
	
	if (empty($usercache[(string)$user['_id']]))
		$usercache[(string)$user['_id']] = $user;
	
	return $user;
}
