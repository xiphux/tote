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

		$userstmt = $mysqldb->prepare("SELECT id, username, email, first_name, last_name, role, reminder, reminder_time, result_notification, timezone, style, (CASE WHEN (first_name IS NOT NULL AND last_name IS NOT NULL) THEN CONCAT(CONCAT(first_name,' '),last_name) WHEN first_name IS NOT NULL THEN first_name ELSE username END) AS display_name FROM " . TOTE_TABLE_USERS . " WHERE id=?");
		$userstmt->bind_param('i', $_SESSION['user']);
		$userstmt->execute();
		$userresult = $userstmt->get_result();
		$loggedinuser = $userresult->fetch_assoc();

		$userresult->close();
		$userstmt->close();

	}

	return $loggedinuser;
}
