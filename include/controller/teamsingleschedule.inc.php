<?php

require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');
require_once(TOTE_INCLUDEDIR . 'mobile_browser.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

/**
 * teamsingleschedule controller
 *
 * displays the game schedule for a given team
 *
 * @param string $year season year
 * @param string $team team id
 * @param string $output output format
 */
function display_teamsingleschedule($season, $team, $output = 'html', $week = null)
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

	$gamesstmt = $mysqldb->prepare('SELECT games.week, games.start, games.home_score, games.away_score, home_teams.abbreviation AS home_team_abbr, away_teams.abbreviation AS away_team_abbr FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=? AND (games.away_team_id=? OR games.home_team_id=?) ORDER BY week');
	$gamesstmt->bind_param('iii', $season, $team, $team);
	$gamesstmt->execute();
	$gamesresult = $gamesstmt->get_result();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$teamgames = array();
	while ($game = $gamesresult->fetch_assoc()) {
		$game['start'] = strtotime($game['start']);
		$game['localstart'] = get_local_datetime($game['start']);
		$teamgames[(int)$game['week']] = $game;
	}
	date_default_timezone_set($tz);
	
	$gamesresult->close();
	$gamesstmt->close();

	$seasonweeks = get_season_weeks($season);

	for ($i = 1; $i <= $seasonweeks; $i++) {
		if (!isset($teamgames[$i])) {
			$teamgames[$i] = array('bye' => true);
		}
	}
	ksort($teamgames);

	$teamstmt = $mysqldb->prepare('SELECT teams.home, teams.team FROM . ' . TOTE_TABLE_TEAMS . ' WHERE id=?');
	$teamstmt->bind_param('i', $team);
	$teamstmt->execute();
	$teamresult = $teamstmt->get_result();
	$teamobj = $teamresult->fetch_assoc();
	$teamresult->close();
	$teamstmt->close();

	http_headers();

	$tpl->assign('team', $teamobj);
	if (empty($week)) {
		$tpl->assign('allseasons', array_reverse(get_seasons()));
	}
	$mobile = mobile_browser();
	if ($mobile) {
		$tpl->assign('mobile', true);
	}

	$tpl->assign('year', $season);
	$tpl->assign('games', $teamgames);
	if (!empty($week))
		$tpl->assign('week', $week);

	if ($output == 'js')
		$tpl->assign('js', true);

	$tpl->display('teamsingleschedule.tpl');
}
