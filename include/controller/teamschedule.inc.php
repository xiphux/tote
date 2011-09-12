<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

/**
 * teamschedule controller
 *
 * displays the game schedule for a given team
 *
 * @param string $year season year
 * @param string $team team id
 * @param string $output output format
 */
function display_teamschedule($season, $team, $output = 'html')
{
	global $tpl;

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

	if (empty($team)) {
		display_message("Team is required", SCHEDULE_HEADER);
		return;
	}

	$games = get_collection(TOTE_COLLECTION_GAMES);

	$gameobjs = $games->find(
		array(
			'season' => (int)$season,
			'$or' => array(
				array('home_team' => new MongoId($team)),
				array('away_team' => new MongoId($team))
			)
		),
		array('home_team', 'away_team', 'home_score', 'away_score', 'start', 'week')
	)->sort(array('start' => 1));

	$teamgames = array();
	foreach ($gameobjs as $i => $gameobj) {
		$gameobj['home_team'] = get_team($gameobj['home_team']);
		$gameobj['away_team'] = get_team($gameobj['away_team']);
		$gameobj['localstart'] = get_local_datetime($gameobj['start']);
		$teamgames[$gameobj['week']] = $gameobj;
	}

	$seasonweeks = get_season_weeks($season);
	for ($i = 1; $i <= $seasonweeks; $i++) {
		if (!isset($teamgames[$i])) {
			$teamgames[$i] = array('bye' => true);
		}
	}
	ksort($teamgames);

	$tpl->assign('team', get_team($team));
	$tpl->assign('year', $season);
	$tpl->assign('games', $teamgames);

	if ($output == 'js')
		$tpl->assign('js', true);

	$tpl->display('teamschedule.tpl');
}
