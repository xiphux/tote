<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Caches team objects
 */
$teamcache = null;

/**
 * Gets a team object given the team ID
 *
 * @param object $id team id
 * @return object team object
 */
function get_team($id)
{
	global $teamcache;

	if ($teamcache === null) {
		$teams = get_collection(TOTE_COLLECTION_TEAMS);

		$allteams = $teams->find(
			array(),
			array('team', 'home', 'abbreviation')
		);

		$teamcache = array();

		foreach ($allteams as $team) {
			$teamcache[(string)$team['_id']] = $team;
		}
	}

	return $teamcache[(string)$id];
}

