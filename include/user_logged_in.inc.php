<?php

require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Gets the object for the logged in user
 *
 * @return object user object if logged in, or null if not
 */
function user_logged_in()
{
	global $usercache;

	if (empty($_SESSION['user']))
		return null;

	$users = get_collection(TOTE_COLLECTION_USERS);

	$user = $users->findOne(
		array('username' => $_SESSION['user'])  // match on stored username
	);
	
	if (empty($usercache[(string)$user['_id']])) {
		// cache if necessary
		$usercache[(string)$user['_id']] = $user;
	}
	
	return $user;
}
