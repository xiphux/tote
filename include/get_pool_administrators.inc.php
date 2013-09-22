<?php

/**
 * get_pool_administrators
 *
 * for a pool, get the primary and secondary administrators
 *
 * @param object $poolid pool id
 * @return array array of primary and secondary administrators
 */
function get_pool_administrators($poolid)
{
	global $db;

	if (empty($poolid))
		return null;

	$adminstmt = $db->prepare("SELECT pool_administrators.user_id, pool_administrators.name, pool_administrators.admin_type, (CASE WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name) WHEN users.first_name IS NOT NULL THEN users.first_name ELSE users.username END) AS display_name FROM " . TOTE_TABLE_POOL_ADMINISTRATORS . " AS pool_administrators LEFT JOIN " . TOTE_TABLE_USERS . " AS users ON pool_administrators.user_id=users.id WHERE pool_administrators.pool_id=:pool_id");
	$adminstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$adminstmt->execute();

	$admins = array();

	while ($admin = $adminstmt->fetch(PDO::FETCH_ASSOC)) {
		if ($admin['admin_type'] == 2) {
			$admins['secondary'][] = $admin;
		} else if ($admin['admin_type'] == 1) {
			$admins['primary'][] = $admin;
		}
	}

	$adminstmt = null;

	if (count($admins) > 0)
		return $admins;

	return null;
}
