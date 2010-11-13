<?php

$gamecachebyteam = array();

function get_game_by_team($season, $week, $team)
{
	global $gamecachebyteam, $db, $tote_conf;

	$gamecol = 'games';
	if (!empty($tote_conf['namespace']))
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;
	$games = $db->selectCollection($gamecol);

	$key = $season . ':' . $week . ':' . $team;

	if (empty($gamecachebyteam[$key])) {
		$js = "function() {
			return ((this.home_team == '" . $team . "') || (this.away_team == '" . $team . "'));
		}";

		$gamecachebyteam[$key] = $games->findOne(array('season' => (int)$season, 'week' => (int)$week, '$where' => $js));
	}

	return $gamecachebyteam[$key];
}

