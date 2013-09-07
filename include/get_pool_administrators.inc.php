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
	global $mysqldb;

	if (empty($poolid))
		return null;

	$adminstmt = $mysqldb->prepare('SELECT pool_administrators.user_id, pool_administrators.name, pool_administrators.admin_type, users.first_name, users.last_name, users.username FROM ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' AS pool_administrators LEFT JOIN ' . TOTE_TABLE_USERS . ' AS users ON pool_administrators.user_id=users.id WHERE pool_administrators.pool_id=?');
	$adminstmt->bind_param('i', $poolid);
	$adminstmt->execute();
	$adminresult = $adminstmt->get_result();

	$admins = array();

	while ($admin = $adminresult->fetch_assoc()) {
		if ($admin['admin_type'] == 2) {
			$admins['secondary'][] = $admin;
		} else if ($admin['admin_type'] == 1) {
			$admins['primary'][] = $admin;
		}
	}

	$adminresult->close();
	$adminstmt->close();

	if (count($admins) > 0)
		return $admins;

	return null;
}
