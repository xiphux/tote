<?php

require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'mobile_browser.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

/**
 * week schedule controller
 *
 * displays the game schedule for a given season and week
 *
 * @param string $year season year
 * @param string $week week
 * @param string $output output format
 */
function display_weekschedule($season, $week, $output = 'html')
{
	global $tpl, $db;

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

	$user = user_logged_in();

	$gamesstmt = $db->prepare('SELECT games.start, home_teams.abbreviation AS home_abbr, away_teams.abbreviation AS away_abbr, games.home_score, games.away_score FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=:year AND games.week=:week ORDER BY games.start');
	$gamesstmt->bindParam(':year', $season, PDO::PARAM_INT);
	$gamesstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$gamesstmt->execute();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	$games = array();
	while ($game = $gamesstmt->fetch(PDO::FETCH_ASSOC)) {
		$game['start'] = strtotime($game['start']);
		$game['localstart'] = get_local_datetime($game['start'], (!empty($user['timezone']) ? $user['timezone'] : null));
		$games[] = $game;
	}

	date_default_timezone_set($tz);

	$gamesstmt = null;

	http_headers();

	$tpl->assign('year', $season);
	$tpl->assign('week', $week);
	$tpl->assign('games', $games);

	$mobile = mobile_browser();
	if ($mobile) {
		$tpl->assign('mobile', true);
	}

	if ($output == 'js')
		$tpl->assign('js', true);

	$tpl->display('weekschedule.tpl');
}
