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
 * teamschedule controller
 *
 * displays the game schedule by team
 *
 * @param string $year season year
 * @param string $output output format
 */
function display_teamschedule($season, $output = 'html')
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

	$gamestmt = $db->prepare("SELECT teams.id AS team_id, CONCAT(CONCAT(teams.home,' '),teams.team) AS team_name, games.week, games.start, games.home_score, games.away_score, home_teams.abbreviation AS home_team_abbr, away_teams.abbreviation AS away_team_abbr FROM " . TOTE_TABLE_TEAMS . " AS teams RIGHT JOIN " . TOTE_TABLE_GAMES . " AS games ON (games.home_team_id=teams.id OR games.away_team_id=teams.id) LEFT JOIN " . TOTE_TABLE_SEASONS . " AS seasons ON games.season_id=seasons.id LEFT JOIN " . TOTE_TABLE_TEAMS . " AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN " . TOTE_TABLE_TEAMS . " AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=:year ORDER BY teams.home, teams.team, games.week");
	$gamestmt->bindParam(':year', $season, PDO::PARAM_INT);
	$gamestmt->execute();

	$teamgames = array();
	$lastteamid = null;
	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');
	while ($game = $gamestmt->fetch(PDO::FETCH_ASSOC)) {
		if ($game['team_id'] != $lastteamid) {
			$lastteamid = $game['team_id'];
			$teamgames[$lastteamid]['team'] = $game['team_name'];
			$teamgames[$lastteamid]['games'] = array();
		}
		$game['start'] = strtotime($game['start']);
		$game['localstart'] = get_local_datetime($game['start'], (!empty($user['timezone']) ? $user['timezone'] : null));
		$teamgames[$lastteamid]['games'][(int)$game['week']] = $game;
	}
	date_default_timezone_set($tz);

	$gamestmt = null;

	$seasonweeks = get_season_weeks($season);

	foreach ($teamgames as $teamid => $teamgroup) {
		for ($i = 1; $i <= $seasonweeks; $i++) {
			if (!isset($teamgroup['games'][$i])) {
				$teamgames[$teamid]['games'][$i] = array('bye' => true);
			}
		}
		ksort($teamgames[$teamid]['games']);
	}

	http_headers();

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

	$tpl->display('teamschedule.tpl');
}
