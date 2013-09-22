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
	global $db;

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

	$poolstmt = $db->prepare("SELECT pools.id AS pool_id, (CASE WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name) WHEN users.first_name IS NOT NULL THEN users.first_name ELSE users.username END) AS user_display_name, pool_administrators.id AS admin_id, pool_administrators.admin_type AS admin_type FROM " . TOTE_TABLE_POOLS . " AS pools LEFT JOIN " . TOTE_TABLE_USERS . " AS users ON users.id=:user_id LEFT JOIN " . TOTE_TABLE_POOL_ADMINISTRATORS . " AS pool_administrators ON pools.id=pool_administrators.pool_id AND pool_administrators.user_id=:admin_user_id WHERE pools.id=:pool_id");
	$poolstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$poolstmt->bindParam(':admin_user_id', $userid, PDO::PARAM_INT);
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();
	$data = $poolstmt->fetch(PDO::FETCH_ASSOC);
	$poolstmt = null;

	if (!$data) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (empty($data['user_display_name'])) {
		echo "Unknown user";
		return;
	}

	$currentadminid = (int)$data['admin_id'];
	$currentadmintype = (int)$data['admin_type'];
	$type = (int)$type;

	if ($type == $currentadmintype)
		return;

	$adminstmt = null;
	if ($currentadmintype == 0) {
		// add admin record
		$adminstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' (pool_id, user_id, name, admin_type) VALUES (:pool_id, :user_id, :name, :admin_type)');
		$adminstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
		$adminstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
		$adminstmt->bindParam(':name', $data['user_display_name']);
		$adminstmt->bindParam(':admin_type', $type, PDO::PARAM_INT);
	} else if ($type == 0) {
		// remove admin record
		$adminstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' WHERE id=:admin_id');
		$adminstmt->bindParam(':admin_id', $currentadminid, PDO::PARAM_INT);
	} else {
		// update admin record
		$adminstmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' SET name=:name, admin_type=:admin_type WHERE id=:admin_id');
		$adminstmt->bindParam(':name', $data['user_display_name']);
		$adminstmt->bindParam(':admin_type', $type, PDO::PARAM_INT);
		$adminstmt->bindParam(':admin_id', $currentadminid, PDO::PARAM_INT);
	}
	$adminstmt->execute();
	$adminstmt = null;

	$actionstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, admin_id, admin_username, admin_type, old_admin_type) VALUES (:pool_id, 3, UTC_TIMESTAMP(), :user_id, :username, :admin_id, :admin_username, :admin_type, :old_admin_type)');
	$actionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$actionstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
	$actionstmt->bindParam(':username', $data['user_display_name']);
	$actionstmt->bindParam(':admin_id', $user['id'], PDO::PARAM_INT);
	$actionstmt->bindParam(':admin_username', $user['display_name']);
	$actionstmt->bindParam(':admin_type', $type, PDO::PARAM_INT);
	$actionstmt->bindParam(':old_admin_type', $currentadmintype, PDO::PARAM_INT);
	$actionstmt->execute();
	$actionstmt = null;

}
