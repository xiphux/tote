<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');
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
	global $tpl, $mysqldb;

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

	$userstmt = $mysqldb->prepare("SELECT (CASE WHEN (first_name IS NOT NULL AND last_name IS NOT NULL) THEN CONCAT(CONCAT(first_name,' '),last_name) WHEN first_name IS NOT NULL THEN first_name ELSE username END) FROM " . TOTE_TABLE_USERS . " WHERE id=?");
	$userstmt->bind_param('i', $userid);
	$username = null;
	$userstmt->bind_result($username);
	$userstmt->execute();
	$found = $userstmt->fetch();
	$userstmt->close();

	if (!$found) {
		// must be a valid user to delete
		display_message("Could not find user to delete", DELETEUSER_HEADER);
		return;
	}

	// delete any user picks
	$pickdelstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' WHERE pool_entry_id IN (SELECT id FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE user_id=?)');
	$pickdelstmt->bind_param('i', $userid);
	$pickdelstmt->execute();
	$pickdelstmt->close();

	// audit entrant removal in any pools user is in
	$entriesstmt = $mysqldb->prepare('SELECT id, pool_id FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE user_id=?');
	$entriesstmt->bind_param('i', $userid);
	$entriesstmt->execute();
	$entriesresult = $entriesstmt->get_result();
	$entries = $entriesresult->fetch_all(MYSQLI_ASSOC);
	$entriesresult->close();
	$entriesstmt->close();

	if (count($entries) > 0) {
		
		$auditstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, username, admin_id, admin_username) VALUES (?, 2, UTC_TIMESTAMP(), ?, ?, ?)');
		foreach ($entries as $entry) {
			$auditstmt->bind_param('isis', $entry['pool_id'], $username, $user['id'], $user['display_name']);
			$auditstmt->execute();
		}
		$auditstmt->close();

		// delete any user entries
		$delentrystmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE user_id=?');
		$delentrystmt->bind_param('i', $userid);
		$delentrystmt->execute();
		$delentrystmt->close();
	}

	// nullify any action / administrator ids pointing to this user
	$deluseractionstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOL_ACTIONS . ' SET user_id=NULL WHERE user_id=?');
	$deluseractionstmt->bind_param('i', $userid);
	$deluseractionstmt->execute();
	$deluseractionstmt->close();

	$deladminactionstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOL_ACTIONS . ' SET admin_id=NULL WHERE admin_id=?');
	$deladminactionstmt->bind_param('i', $userid);
	$deladminactionstmt->execute();
	$deladminactionstmt->close();

	// delete user
	$deluserstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_USERS . ' WHERE id=?');
	$deluserstmt->bind_param('i', $userid);
	$deluserstmt->execute();
	$deluserstmt->close();

	// clear caches
	clear_cache('pool');

	redirect(array('a' => 'editusers'));
}
