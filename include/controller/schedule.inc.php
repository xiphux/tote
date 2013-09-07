<?php

require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');
require_once(TOTE_INCLUDEDIR . 'mobile_browser.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

/**
 * schedule controller
 *
 * displays the game schedule for a given season
 *
 * @param string $year season year
 * @param string $output output format
 */
function display_schedule($season, $output = 'html')
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

	$gamesstmt = $mysqldb->prepare('SELECT games.week, games.start, home_teams.abbreviation AS home_abbr, away_teams.abbreviation AS away_abbr, games.home_score, games.away_score FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=? ORDER BY week, games.start');
	$gamesstmt->bind_param('i', $season);
	$gamesstmt->execute();
	$gamesresult = $gamesstmt->get_result();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	$games = array();
	while ($game = $gamesresult->fetch_assoc()) {
		$game['start'] = strtotime($game['start']);
		$game['localstart'] = get_local_datetime(null, $game['start']);
		$games[(int)$game['week']][] = $game;
	}

	date_default_timezone_set($tz);

	$gamesresult->close();
	$gamesstmt->close();

	http_headers();

	$tpl->assign('allseasons', array_reverse(get_seasons()));

	$tpl->assign('year', $season);
	$tpl->assign('games', $games);

	$mobile = mobile_browser();
	if ($mobile) {
		$tpl->assign('mobile', true);
	}

	if ($output == 'js')
		$tpl->assign('js', true);

	$tpl->display('schedule.tpl');
}
