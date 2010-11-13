<?php

$teamcache = array();

function get_team($id)
{
	global $teamcache, $tote_conf, $db;

	$teamcol = 'teams';
	if (!empty($tote_conf['namespace']))
		$teamcol = $tote_conf['namespace'] . '.' . $teamcol;
	$teams = $db->selectCollection($teamcol);

	if (empty($teamcache[(string)$id])) {
		$teamcache[(string)$id] = $teams->findOne(array('_id' => $id), array('team', 'home', 'abbreviation'));
	}

	return $teamcache[(string)$id];
}

