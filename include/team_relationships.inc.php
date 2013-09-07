<?php

/**
 * Gets team relationships
 *
 * @return array team relationship data
 */
function team_relationships()
{
	global $mysqldb;

	$teamresults = $mysqldb->query('SELECT teams.id, teams.team, teams.home, teams.abbreviation, divisions.division, conferences.abbreviation AS conference FROM ' . TOTE_TABLE_TEAMS . ' AS teams LEFT JOIN ' . TOTE_TABLE_DIVISIONS . ' AS divisions ON teams.division_id=divisions.id LEFT JOIN ' . TOTE_TABLE_CONFERENCES . ' AS conferences ON divisions.conference_id=conferences.id ORDER BY conferences.abbreviation, divisions.division');

	$teamdata = array();
	$teamindex = array();

	$teamcount = 0;
	while ($team = $teamresults->fetch_assoc()) {
		$teamdata[$teamcount] = $team;
		$teamindex[$team['id']] = $teamcount;
		++$teamcount;
	}

	$teamresults->close();

	$gameresults = $mysqldb->query('SELECT games.home_team_id, games.away_team_id, games.home_score, games.away_score, seasons.year AS season FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE games.home_score IS NOT NULL OR games.away_score IS NOT NULL ORDER BY seasons.year DESC');

	$gamedata = array();

	while ($game = $gameresults->fetch_assoc()) {
		if (!isset($gamedata[$game['season']])) {
			for ($i = 0; $i < $teamcount; $i++) {
				$gamedata[$game['season']][$i] = array_fill(0, $teamcount, 0);
			}
		}

		$homeindex = $teamindex[$game['home_team_id']];
		$awayindex = $teamindex[$game['away_team_id']];

		if ($game['home_score'] > $game['away_score']) {
			$gamedata[$game['season']][$homeindex][$awayindex] += 1;
		} else if ($game['away_score'] > $game['home_score']) {
			$gamedata[$game['season']][$awayindex][$homeindex] += 1;
		}
	}

	$gameresults->close();

	return array( 'teams' => $teamdata, 'games' => $gamedata );
}
