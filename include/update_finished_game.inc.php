<?php

require_once(TOTE_INCLUDEDIR . 'team_abbreviation_to_id.inc.php');
require_once(TOTE_INCLUDEDIR . 'notify_finished_game.inc.php');

/**
 * For a game that has finished and has scores,
 * update it in the database if necessary
 *
 * ESPN doesn't tell us which team is home and which is away
 *
 * @param string $season season
 * @param string $week week number
 * @param string $team1 team 1 abbreviation
 * @param string $team1score team 1 score
 * @param string $team2 team 2 abbreviation
 * @param string $team2score team 2 score
 * @param boolean $skipmsg true to skip "Updating..." message
 */
function update_finished_game($season, $week, $team1, $team1score, $team2, $team2score, $skipmsg = false)
{
	if (!$skipmsg) {
		echo 'Updating ' . $team1 . ' ' . $team1score . ', ' . $team2 . ' ' . $team2score . '... ';
	}

	// find the teams we're updating
	$team1id = team_abbreviation_to_id($team1);
	if (empty($team1id)) {
		echo "error: Couldn't locate " . $team1 . "<br />\n";
		return;
	}
	$team2id = team_abbreviation_to_id($team2);
	if (empty($team2id)) {
		echo "error: Couldn't locate " . $team2 . "<br />\n";
		return;
	}

	$games = get_collection(TOTE_COLLECTION_GAMES);

	// find the game where these two teams are playing this week
	$js = "function() {
		return ((this.home_team == '" . $team1id . "') && (this.away_team == '" . $team2id . "')) || ((this.home_team == '" . $team2id . "') && (this.away_team == '" . $team1id . "'));
	}";
	$gameobj = $games->findOne(
		array(
			'season' => (int)$season,
			'week' => (int)$week,
			'$where' => $js
		)
	);
	if (!$gameobj) {
		// these teams aren't playing this week
		echo "error: Couldn't locate " . $team1 . " vs " . $team2 . " for week " . $week . "<br />\n";
		return;
	}

	$hometeam = '';
	$awayteam = '';
	$homeid = '';
	$awayid = '';
	$homescore = '';
	$awayscore = '';

	// figure out which team is home and which is away
	if ($gameobj['home_team'] == $team1id) {
		$hometeam = $team1;
		$homeid = $team1id;
		$homescore = $team1score;
		$awayteam = $team2;
		$awayid = $team2id;
		$awayscore = $team2score;
	} else if ($gameobj['home_team'] == $team2id) {
		$hometeam = $team2;
		$homeid = $team2id;
		$homescore = $team2score;
		$awayteam = $team1;
		$awayid = $team1id;
		$awayscore = $team1score;
	} else {
		// should never happen
		echo "error during update<br />\n";
		return;
	}

	if (!isset($gameobj['home_score']) || !isset($gameobj['away_score']) || ($gameobj['home_score'] != $homescore) || ($gameobj['away_score'] != $awayscore)) {

		// scores don't match what we have in database - update it
		echo 'updating from ' . $awayteam . (isset($gameobj['away_score']) ? ' ' . $gameobj['away_score'] : '') . ' @ ' . $hometeam . (isset($gameobj['home_score']) ? ' ' . $gameobj['home_score'] : '') . ' to ' . $awayteam . ' ' . $awayscore . ' @ ' . $hometeam . ' ' . $homescore . "<br />\n";
		if (!(isset($gameobj['home_score']) || isset($gameobj['away_score']))) {
			// send notification emails if recording scores for the first time
			notify_finished_game((int)$season, (int)$week, $homeid, $homescore, $awayid, $awayscore);
		}
		$games->update(
			array('_id' => $gameobj['_id']),
			array('$set' => array(
				'home_score' => (int)$homescore,
				'away_score' => (int)$awayscore
			))
		);
		clear_cache('pool');
	} else {
		// we're up to date
		echo "no update necessary, scores up to date<br />\n";
	}
}

