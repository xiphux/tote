<?php

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
	global $mysqldb;

	if (!$skipmsg) {
		echo 'Updating ' . $team1 . ' ' . $team1score . ', ' . $team2 . ' ' . $team2score . '... ';
	}

	$gamestmt = $mysqldb->prepare('SELECT games.id, games.home_team_id, home_teams.abbreviation AS home_team_abbr, games.away_team_id, away_teams.abbreviation AS away_team_abbr, games.home_score, games.away_score FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=? AND games.week=? AND ((home_teams.abbreviation=? AND away_teams.abbreviation=?) OR (home_teams.abbreviation=? AND away_teams.abbreviation=?))');
	$gamestmt->bind_param('iissss', $season, $week, $team1, $team2, $team2, $team1);
	$gamestmt->execute();
	$gameresult = $gamestmt->get_result();
	$game = $gameresult->fetch_assoc();
	$gameresult->close();
	$gamestmt->close();

	if (!$game) {
		// these teams aren't playing this week
		echo "error: Couldn't locate " . $team1 . " vs " . $team2 . " for week " . $week . "<br />\n";
		return false;
	}

	$modified = false;

	$hometeam = $game['home_team_abbr'];
	$awayteam = $game['away_team_abbr'];
	$homeid = $game['home_team_id'];
	$awayid = $game['away_team_id'];
	$homescore = '';
	$awayscore = '';

	if ($hometeam == $team1) {
		$homescore = $team1score;
		$awayscore = $team2score;
	} else if ($hometeam == $team2) {
		$homescore = $team2score;
		$awayscore = $team1score;
	} else {
		// should never happen
		echo "error during update<br />\n";
		return;
	}

	if (($game['home_score'] != $homescore) || ($game['away_score'] != $awayscore)) {

		// scores don't match what we have in database - update it
		echo 'updating from ' . $awayteam . (isset($game['away_score']) ? ' ' . $game['away_score'] : '') . ' @ ' . $hometeam . (isset($game['home_score']) ? ' ' . $game['home_score'] : '') . ' to ' . $awayteam . ' ' . $awayscore . ' @ ' . $hometeam . ' ' . $homescore . "<br />\n";
		if (!(isset($game['home_score']) || isset($game['away_score']))) {
			// send notification emails if recording scores for the first time
			notify_finished_game((int)$season, (int)$week, $homeid, $homescore, $awayid, $awayscore);
		}

		$updatestmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_GAMES . ' SET home_score=?, away_score=? WHERE id=?');
		$updatestmt->bind_param('iii', $homescore, $awayscore, $game['id']);
		$updatestmt->execute();
		$updatestmt->close();
	
		$modified = $game['id'];
	} else {
		// we're up to date
		echo "no update necessary, scores up to date<br />\n";
	}

	return $modified;
}

