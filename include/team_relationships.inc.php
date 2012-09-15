<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

/**
 * Gets team relationships
 *
 * @return array team relationship data
 */
function team_relationships()
{
	$teams = get_collection(TOTE_COLLECTION_TEAMS);
	$games = get_collection(TOTE_COLLECTION_GAMES);

	$teamobjects = $teams->find(
		array(),
		array('team', 'home', 'conference', 'division', 'abbreviation')
	)->sort(array('conference' => 1, 'division' => 1));
	$gameobjects = $games->find(
		array('$and' => array( array('home_score' => array( '$exists' => true )), array('away_score' => array( '$exists' => true )))),
		array('home_team', 'away_team', 'home_score', 'away_score', 'season')
	)->sort(array('season' => -1));

	$teamdata = array();
	$teamindex = array();
	$gamedata = array();

	$count = 0;
	foreach ($teamobjects as $team) {
		$teamdata[$count] = $team;
		$teamindex[(string)$team['_id']] = $count;
		$count++;
	}

	foreach ($gameobjects as $game) {
		if (!isset($gamedata[$game['season']])) {
			for ($i = 0; $i < $count; $i++) {
				$gamedata[$game['season']][$i] = array_fill(0, $count, 0);
			}
		}

		$homeindex = $teamindex[(string)$game['home_team']];
		$awayindex = $teamindex[(string)$game['away_team']];

		if ($game['home_score'] > $game['away_score']) {
			$gamedata[$game['season']][$homeindex][$awayindex] += 1;
		} else if ($game['away_score'] > $game['home_score']) {
			$gamedata[$game['season']][$awayindex][$homeindex] += 1;
		}
	}

	return array( 'teams' => $teamdata, 'games' => $gamedata );
}
