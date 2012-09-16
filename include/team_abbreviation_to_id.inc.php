<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

// cache team abbreviation to id mapping
$teamids = null;

/**
 * Given a team abbreviation, get the team object id
 *
 * @param string $team team abbreviation
 * @return object team id
 */
function team_abbreviation_to_id($team)
{
	global $teamids;

	if ($teamids == null) {
		$teams = get_collection(TOTE_COLLECTION_TEAMS);

		$teamcol = $teams->find(array());

		foreach ($teamcol as $teamobj) {
			if (!empty($teamobj['abbreviation'])) {
				$teamids[$teamobj['abbreviation']] = $teamobj['_id'];
			}
		}
	}

	return $teamids[$team];
}

