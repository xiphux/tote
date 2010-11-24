<?php

// define constants for pool collection names
define('TOTE_COLLECTION_POOLS', 'pools');
define('TOTE_COLLECTION_USERS', 'users');
define('TOTE_COLLECTION_GAMES', 'games');
define('TOTE_COLLECTION_TEAMS', 'teams');

/**
 * Gets a collection object for a given collection name
 *
 * @param string $collectionName name of collection to get
 * @return object mongo collection object
 */
function get_collection($collectionName)
{
	global $db, $tote_conf;

	if (empty($collectionName))
		return null;

	if (!empty($tote_conf['namespace']))
		$collectionName = $tote_conf['namespace'] . '.' . $collectionName;

	return $db->selectCollection($collectionName);
}
