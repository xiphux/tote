<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

$teamcache = array();

function get_team($id)
{
	global $teamcache;

	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	if (empty($teamcache[(string)$id])) {
		$teamcache[(string)$id] = $teams->findOne(array('_id' => $id), array('team', 'home', 'abbreviation'));
	}

	return $teamcache[(string)$id];
}

