<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Caches team objects
 */
$teamcache = array();

/**
 * Gets a team object given the team ID
 *
 * @param object $id team id
 * @return object team object
 */
function get_team($id)
{
	global $teamcache;

	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	if (empty($teamcache[(string)$id])) {
		// load from database if not already fetched and cached
		$teamcache[(string)$id] = $teams->findOne(
			array('_id' => $id),		// match by id
			array('team', 'home', 'abbreviation')	// load these fields
		);
	}

	return $teamcache[(string)$id];
}

