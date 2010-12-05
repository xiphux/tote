<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * gamecachebyteam
 *
 * Caches game objects mapped by team
 */
$gamecachebyteam = array();

/**
 * For a given season/week and a team playing that week,
 * find the game object
 *
 * @param integer $season season year
 * @param integer $week number
 * @param object $team team object ID
 * @return object game object
 */
function get_game_by_team($season, $week, $team)
{
	global $gamecachebyteam;

	$games = get_collection(TOTE_COLLECTION_GAMES);

	$subkey = $season . ':' . $week;
	$key = $subkey . ':' . $team;

	if (empty($gamecachebyteam[$key])) {
		// load from database if not already fetched and cached
		$game = $games->findOne(
			array(
				'season' => (int)$season,	// season
				'week' => (int)$week,		// week
				'$or' => array(
					array('home_team' => $team),	// home team
					array('away_team' => $team)	// away team
					)
				)
		);

		// cache for both teams
		$awaykey = $subkey . ':' . $game['away_team'];
		$homekey = $subkey . ':' . $game['home_team'];

		if (empty($gamecachebyteam[$awaykey]))
			$gamecachebyteam[$awaykey] = $game;

		if (empty($gamecachebyteam[$homekey]))
			$gamecachebyteam[$homekey] = $game;
	}

	return $gamecachebyteam[$key];
}

