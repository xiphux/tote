<?php

/**
 * Gets the object for the logged in user
 *
 * @return object user object if logged in, or null if not
 */
function user_logged_in()
{
	global $db;

	if (empty($_SESSION['user']))
		return null;

	$userstmt = $db->prepare("SELECT id, username, email, first_name, last_name, role, reminder, reminder_time, result_notification, timezone, style, (CASE WHEN (first_name IS NOT NULL AND last_name IS NOT NULL) THEN CONCAT(CONCAT(first_name,' '),last_name) WHEN first_name IS NOT NULL THEN first_name ELSE username END) AS display_name FROM " . TOTE_TABLE_USERS . " WHERE id=:user_id");
	$userstmt->bindParam(':user_id', $_SESSION['user'], PDO::PARAM_INT);
	$userstmt->execute();

	$loggedinuser = $userstmt->fetch(PDO::FETCH_ASSOC);

	$userstmt = null;

	if (!$loggedinuser) {
		unset($_SESSION['user']);
	}

	return $loggedinuser;
}
