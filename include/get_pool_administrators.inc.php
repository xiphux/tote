<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');

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
	if (empty($poolid))
		return null;

	if (is_string($poolid))
		$poolid = new MongoId($poolid);

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(
		array('_id' => $poolid),
		array('administrators')
	);

	if (!pool)
		return null;

	if (empty($pool['administrators']))
		return null;

	$admins = array();
	foreach ($pool['administrators'] as $admin) {
		$user = null;
		if (!empty($admin['user'])) {
			$user = get_user($admin['user']);
		}
		if ((!$user || (count($user) < 1)) && (!empty($admin['name']))) {
			// if user doesn't exist (was deleted), use the stored user name for historical purposes
			$user = $admin['name'];
		}

		if (empty($user)) {
			// no name and no user - assume this is bad data and skip it
			continue;
		}

		if (isset($admin['secondary']) && ($admin['secondary'] === true)) {
			$admins['secondary'][] = $user;
		} else {
			$admins['primary'][] = $user;
		}
	}

	if (count($admins) > 0)
		return $admins;

	return null;
}
