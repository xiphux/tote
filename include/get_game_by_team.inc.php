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

	if (empty($season) || empty($week) || empty($team))
		return null;

	$season = (int)$season;
	$week = (int)$week;

	if (empty($gamecachebyteam[$season][$week])) {
		
		$games = get_collection(TOTE_COLLECTION_GAMES);

		$weekgames = $games->find(
			array(
				'season' => $season,
				'week' => $week
			)
		);

		foreach ($weekgames as $game) {
			if ($game['away_team']) {
				$gamecachebyteam[$season][$week][(string)$game['away_team']] = $game;
			}
			if ($game['home_team']) {
				$gamecachebyteam[$season][$week][(string)$game['home_team']] = $game;
			}
		}

	}

	return $gamecachebyteam[$season][$week][(string)$team];
}

