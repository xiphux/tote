<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Tests if user is entered in a pool
 *
 * @param object $user user id
 * @param object $pool pool id
 * @return true if user is in pool
 */
function user_in_pool($user, $pool)
{
	if (empty($user) || empty($pool))
		return false;

	if (is_string($user))
		$user = new MongoId($user);

	if (is_string($pool))
		$pool = new MongoId($pool);

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$poolobj = $pools->findOne(
		array(
			'_id' => $pool,
			'entries.user' => $user
		),
		array('name')
	);

	return ($poolobj != null);
}
