<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

$gamecachebyteam = array();

function get_game_by_team($season, $week, $team)
{
	global $gamecachebyteam;

	$games = get_collection(TOTE_COLLECTION_GAMES);

	$key = $season . ':' . $week . ':' . $team;

	if (empty($gamecachebyteam[$key])) {
		$gamecachebyteam[$key] = $games->findOne(array('season' => (int)$season, 'week' => (int)$week, '$or' => array(array('home_team' => $team), array('away_team' => $team))));
	}

	return $gamecachebyteam[$key];
}

