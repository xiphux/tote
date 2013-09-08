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
	global $mysqldb;

	if (empty($_SESSION['user']))
		return null;

	$userstmt = $mysqldb->prepare('SELECT * FROM ' . TOTE_TABLE_USERS . ' WHERE username=?');
	$userstmt->bind_param('s', $_SESSION['user']);
	$userstmt->execute();
	$userresult = $userstmt->get_result();
	$user = $userresult->fetch_assoc();

	$userresult->close();
	$userstmt->close();

	return $user;
}
