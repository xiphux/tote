<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('DELETEUSER_HEADER', 'Manage Your Users');

/**
 * deleteuser controller
 *
 * deletes a user from the database
 *
 * @param string $userid user id to delete
 * @param string $csrftoken CSRF request token
 */
function display_deleteuser($userid, $csrftoken)
{
	global $tpl, $db;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to delete a user
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", DELETEUSER_HEADER);
		return;
	}

	if (empty($userid)) {
		// need to know which user to delete
		display_message("User to delete is required", DELETEUSER_HEADER);
		return;
	}

	$userstmt = $db->prepare("SELECT (CASE WHEN (first_name IS NOT NULL AND last_name IS NOT NULL) THEN CONCAT(CONCAT(first_name,' '),last_name) WHEN first_name IS NOT NULL THEN first_name ELSE username END) FROM " . TOTE_TABLE_USERS . " WHERE id=:user_id");
	$userstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$userstmt->execute();
	$username = null;
	$userstmt->bindColumn(1, $username);
	$found = $userstmt->fetch(PDO::FETCH_BOUND);
	$userstmt = null;

	if (!$found) {
		// must be a valid user to delete
		display_message("Could not find user to delete", DELETEUSER_HEADER);
		return;
	}

	$db->beginTransaction();

	// delete any user picks
	$pickdelstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' WHERE pool_entry_id IN (SELECT id FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE user_id=:user_id)');
	$pickdelstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$pickdelstmt->execute();
	$pickdelstmt = null;

	// audit entrant removal in any pools user is in
	$entriesstmt = $db->prepare('SELECT id, pool_id FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE user_id=:user_id');
	$entriesstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$entriesstmt->execute();

	$auditstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, username, admin_id, admin_username) VALUES (:pool_id, 2, UTC_TIMESTAMP(), :username, :admin_id, :admin_username)');
	$auditstmt->bindParam(':username', $username);
	$auditstmt->bindParam(':admin_id', $user['id'], PDO::PARAM_INT);
	$auditstmt->bindParam(':admin_username', $user['display_name']);
	while ($entry = $entriesstmt->fetch(PDO::FETCH_ASSOC)) {
	
		$auditstmt->bindParam(':pool_id', $entry['pool_id'], PDO::PARAM_INT);
		$auditstmt->execute();

	}
	$auditstmt = null;
	$entriesstmt = null;

	// delete any user entries
	$delentrystmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE user_id=:user_id');
	$delentrystmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$delentrystmt->execute();
	$delentrystmt = null;

	// nullify any action / administrator ids pointing to this user
	$deluseractionstmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOL_ACTIONS . ' SET user_id=NULL WHERE user_id=:user_id');
	$deluseractionstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$deluseractionstmt->execute();
	$deluseractionstmt = null;

	$deladminactionstmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOL_ACTIONS . ' SET admin_id=NULL WHERE admin_id=:admin_id');
	$deladminactionstmt->bindParam(':admin_id', $userid, PDO::PARAM_INT);
	$deladminactionstmt->execute();
	$deladminactionstmt = null;

	// clear records for this user
	$delrecordstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_RECORDS . ' WHERE user_id=:user_id');
	$delrecordstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$delrecordstmt->execute();
	$delrecordstmt = null;

	// delete user
	$deluserstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_USERS . ' WHERE id=:user_id');
	$deluserstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$deluserstmt->execute();
	$deluserstmt = null;

	$db->commit();

	redirect(array('a' => 'editusers'));
}
