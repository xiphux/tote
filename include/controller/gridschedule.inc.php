<?php

require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

/**
 * gridschedule controller
 *
 * displays the season schedule as a grid
 *
 * @param string $year season year
 */
function display_gridschedule($season)
{
	global $tpl, $mysqldb;

	if (empty($season)) {
		// default to this year
		$season = date('Y');
		if ((int)date('n') < 3)
			$season -= 1;
	}

	if (!is_numeric($season)) {
		display_message("Invalid season", SCHEDULE_HEADER);
		return;
	}

	$gamesstmt = $mysqldb->prepare('SELECT games.week, games.start, home_teams.abbreviation AS home_abbr, away_teams.abbreviation AS away_abbr, games.home_score, games.away_score FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=?');
	$gamesstmt->bind_param('i', $season);
	$gamesstmt->execute();
	$gamesresult = $gamesstmt->get_result();

	$gamemap = array();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	while ($game = $gamesresult->fetch_assoc()) {
		if (!isset($gamemap[$game['home_abbr']])) {
			$gamemap[$game['home_abbr']] = array();
		}
		if (!isset($gamemap[$game['away_abbr']])) {
			$gamemap[$game['away_abbr']] = array();
		}

		$game['start'] = get_local_datetime(null, strtotime($game['start']));

		$gamemap[$game['home_abbr']][(int)$game['week']] = $game;
		$gamemap[$game['away_abbr']][(int)$game['week']] = $game;
	}

	$gamesresult->close();
	$gamesstmt->close();

	date_default_timezone_set($tz);

	$seasonweeks = get_season_weeks($season);

	foreach ($gamemap as $team => $weeks) {
		for ($i = 1; $i <= $seasonweeks; ++$i) {
			if (!isset($gamemap[$team][$i]))
				$gamemap[$team][$i] = null;
		}
		ksort($gamemap[$team]);
	}

	ksort($gamemap);

	http_headers();
	
	$tpl->assign('games', $gamemap);

	$tpl->assign('year', $season);
	$tpl->assign('allseasons', array_reverse(get_seasons()));

	$tpl->assign('openweeks', get_open_weeks($season));

	$tpl->display('gridschedule.tpl');
}
