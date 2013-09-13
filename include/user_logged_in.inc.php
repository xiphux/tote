<?php

$loggedinuser = null;

/**
 * Gets the object for the logged in user
 *
 * @return object user object if logged in, or null if not
 */
function user_logged_in()
{
	global $mysqldb, $loggedinuser;

	if (empty($_SESSION['user']))
		return null;

	if (!$loggedinuser) {

		$userstmt = $mysqldb->prepare('SELECT * FROM ' . TOTE_TABLE_USERS . ' WHERE username=?');
		$userstmt->bind_param('s', $_SESSION['user']);
		$userstmt->execute();
		$userresult = $userstmt->get_result();
		$loggedinuser = $userresult->fetch_assoc();

		$userresult->close();
		$userstmt->close();

		unset($loggedinuser['salt']);
		unset($loggedinuser['password']);

	}

	return $loggedinuser;
}
