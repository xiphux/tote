<?php

define('TOTE_COLLECTION_POOLS', 'pools');
define('TOTE_COLLECTION_USERS', 'users');
define('TOTE_COLLECTION_GAMES', 'games');
define('TOTE_COLLECTION_TEAMS', 'teams');

function get_collection($collectionName)
{
	global $db, $tote_conf;

	if (empty($collectionName))
		return null;

	if (!empty($tote_conf['namespace']))
		$collectionName = $tote_conf['namespace'] . '.' . $collectionName;

	return $db->selectCollection($collectionName);
}
