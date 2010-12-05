<?php

// define constants for pool collection names
define('TOTE_COLLECTION_POOLS', 'pools');
define('TOTE_COLLECTION_USERS', 'users');
define('TOTE_COLLECTION_GAMES', 'games');
define('TOTE_COLLECTION_TEAMS', 'teams');

$collections = array();

/**
 * Gets a collection object for a given collection name
 *
 * @param string $collectionName name of collection to get
 * @return object mongo collection object
 */
function get_collection($collectionName)
{
	global $db, $tote_conf, $collections;

	if (empty($collectionName))
		return null;

	if (empty($collections[$collectionName])) {
		$fullName = $collectionName;
		
		if (!empty($tote_conf['namespace']))
			$fullName = $tote_conf['namespace'] . '.' . $fullName;

		$collections[$collectionName] = $db->selectCollection($fullName);
	}

	return $collections[$collectionName];
}
