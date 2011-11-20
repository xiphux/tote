<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

/**
 * schedule controller
 *
 * displays the game schedule for a given season/week
 *
 * @param string $year season year
 * @param string $week week
 * @param string $output output format
 */
function display_schedule($season, $week = null, $output = 'html')
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

	$games = get_collection(TOTE_COLLECTION_GAMES);

	$search = null;
	$sort = null;
	if (!empty($week)) {
		$search = array(
			'season' => (int)$season,
			'week' => (int)$week
		);
		$sort = array(
			'start' => 1
		);
	} else {
		$search = array(
			'season' => (int)$season
		);
		$sort = array(
			'week' => 1,
			'start' => 1
		);
	}

	$gameobjs = $games->find(
		$search,
		array('home_team', 'away_team', 'home_score', 'away_score', 'start', 'week')
	)->sort($sort);

	$allgames = array();
	foreach ($gameobjs as $i => $gameobj) {
		$gameobj['home_team'] = get_team($gameobj['home_team']);
		$gameobj['away_team'] = get_team($gameobj['away_team']);
		$gameobj['localstart'] = get_local_datetime($gameobj['start']);
		$allgames[] = $gameobj;
	}

	if (empty($week)) {
		$weekmapped = array();
		foreach ($allgames as $i => $gameobj) {
			if (isset($gameobj['week'])) {
				$weekmapped[$gameobj['week']][] = $gameobj;
			}
		}
		$allgames = $weekmapped;
	}

	$tpl->assign('year', $season);
	$tpl->assign('week', $week);
	$tpl->assign('games', $allgames);

	if ($output == 'js')
		$tpl->assign('js', true);

	if (empty($week)) {
		$tpl->display('fullschedule.tpl');
	} else {
		$tpl->display('schedule.tpl');
	}
}
