<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * usercache
 *
 * Caches user objects
 */
$usercache = array();

/**
 * Given a user ID, get the user object
 *
 * @param $id user id
 * @return object user object
 */
function get_user($id)
{
	global $usercache;

	$stringid = '';
	$objid = null;

	// handle both string id and id object
	if (is_string($id)) {
		$stringid = $id;
	} else if ($id instanceof MongoId) {
		$objid = $id;
		$stringid = (string)$id;
	} else {
		return;
	}

	if (empty($usercache[$stringid])) {
		// load from database if not already fetched and cached
		$users = get_collection(TOTE_COLLECTION_USERS);
		if (!$objid)
			$objid = new MongoId($stringid);
		$usercache[$stringid] = $users->findOne(
			array('_id' => $objid),		// match on id
			array('password' => 0, 'salt' => 0)
		);
	}

	return $usercache[$stringid];
}
