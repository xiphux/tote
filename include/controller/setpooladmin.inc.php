<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

/**
 * setpooladmin controller
 *
 * toggles users' admin state
 *
 * @param string $poolid pool ID
 * @param string $userid user ID
 * @param string $type admin type
 * @param string $csrftoken CSRF request token
 */
function display_setpooladmin($poolid, $userid, $type, $csrftoken)
{
	global $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		echo "User not logged in";
		return;
	}

	if (!user_is_admin($user)) {
		// need to be an admin to edit the pool
		echo "User is not an admin";
		return;
	}

	if (!validate_csrftoken($csrftoken)) {
		echo "Invalid request token";
		return;
	}

	if (empty($poolid)) {
		// must have a pool to edit
		echo "Pool is required";
		return;
	}

	if (empty($userid)) {
		// must have a user
		echo "User is required";
		return;
	}

	if (($type > 2) || ($type < 0)) {
		echo "Unknown admin type";
		return;
	}

	$poolstmt = $mysqldb->prepare("SELECT pools.id AS pool_id, (CASE WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name) WHEN users.first_name IS NOT NULL THEN users.first_name ELSE users.username END) AS user_display_name, pool_administrators.id AS admin_id, pool_administrators.admin_type AS admin_type FROM " . TOTE_TABLE_POOLS . " AS pools LEFT JOIN " . TOTE_TABLE_USERS . " AS users ON users.id=? LEFT JOIN " . TOTE_TABLE_POOL_ADMINISTRATORS . " AS pool_administrators ON pools.id=pool_administrators.pool_id AND pool_administrators.user_id=? WHERE pools.id=?");
	$poolstmt->bind_param('iii', $userid, $userid, $poolid);
	$poolstmt->execute();
	$poolresult = $poolstmt->get_result();
	$data = $poolresult->fetch_assoc();
	$poolresult->close();
	$poolstmt->close();

	if (!$data) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (empty($data['user_display_name'])) {
		echo "Unknown user";
		return;
	}

	$currentadminid = $data['admin_id'];
	$currentadmintype = (int)$data['admin_type'];
	$type = (int)$type;

	if ($type == $currentadmintype)
		return;

	$adminstmt = null;
	if ($currentadmintype == 0) {
		// add admin record
		$adminstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' (pool_id, user_id, name, admin_type) VALUES (?, ?, ?, ?)');
		$adminstmt->bind_param('iisi', $poolid, $userid, $data['user_display_name'], $type);
	} else if ($type == 0) {
		// remove admin record
		$adminstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' WHERE id=?');
		$adminstmt->bind_param('i', $currentadminid);
	} else {
		// update admin record
		$adminstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' SET name=?, admin_type=? WHERE id=?');
		$adminstmt->bind_param('sii', $data['user_display_name'], $type, $currentadminid);
	}
	$adminstmt->execute();
	$adminstmt->close();

	$actionstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, admin_id, admin_username, admin_type, old_admin_type) VALUES (?, 3, UTC_TIMESTAMP(), ?, ?, ?, ?, ?, ?)');
	$actionstmt->bind_param('iisisii', $poolid, $userid, $data['user_display_name'], $user['id'], $user['display_name'], $type, $currentadmintype);
	$actionstmt->execute();
	$actionstmt->close();

}
