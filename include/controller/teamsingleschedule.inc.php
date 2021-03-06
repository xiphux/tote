<?php

require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');
require_once(TOTE_INCLUDEDIR . 'mobile_browser.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');

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
	global $tpl, $db;

	if (empty($season)) {
		// default to this year
		$season = get_current_season();
	}

	if (!is_numeric($season)) {
		display_message("Invalid season", SCHEDULE_HEADER);
		return;
	}

	$user = user_logged_in();

	$gamesstmt = $db->prepare('SELECT games.week, games.start, games.home_score, games.away_score, home_teams.abbreviation AS home_team_abbr, away_teams.abbreviation AS away_team_abbr FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=:year AND (games.away_team_id=:away_team_id OR games.home_team_id=:home_team_id) ORDER BY week');
	$gamesstmt->bindParam(':year', $season, PDO::PARAM_INT);
	$gamesstmt->bindParam(':away_team_id', $team, PDO::PARAM_INT);
	$gamesstmt->bindParam(':home_team_id', $team, PDO::PARAM_INT);
	$gamesstmt->execute();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$teamgames = array();
	while ($game = $gamesstmt->fetch(PDO::FETCH_ASSOC)) {
		$game['start'] = strtotime($game['start']);
		$game['localstart'] = get_local_datetime($game['start'], (!empty($user['timezone']) ? $user['timezone'] : null));
		$teamgames[(int)$game['week']] = $game;
	}
	date_default_timezone_set($tz);
	
	$gamesstmt = null;

	$seasonweeks = get_season_weeks($season);

	for ($i = 1; $i <= $seasonweeks; $i++) {
		if (!isset($teamgames[$i])) {
			$teamgames[$i] = array('bye' => true);
		}
	}
	ksort($teamgames);

	$teamstmt = $db->prepare('SELECT teams.home, teams.team FROM . ' . TOTE_TABLE_TEAMS . ' WHERE id=:team_id');
	$teamstmt->bindParam(':team_id', $team, PDO::PARAM_INT);
	$teamstmt->execute();
	$teamobj = $teamstmt->fetch(PDO::FETCH_ASSOC);
	$teamstmt = null;

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
