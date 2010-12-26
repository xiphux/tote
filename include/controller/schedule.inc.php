<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

/**
 * schedule controller
 *
 * displays the game schedule for a given season/week
 *
 * @param string $year season year
 * @param string $week week
 * @param string $output output format
 */
function display_schedule($season, $week, $output = 'html')
{
	global $tpl;

	if (empty($season)) {
		// default to this year
		$season = date('Y');
		if ((int)date('n') < 3)
			$season += 1;
	}

	if (empty($week)) {
		$openweeks = get_open_weeks($season);
		$week = array_search(true, $openweeks, true);
		if ($week === false) {
			// for a closed season, default to the last week
			$week = get_season_weeks($season);
		}
	}

	if (!is_numeric($season)) {
		echo "Invalid season";
		return;
	}

	if (!is_numeric($week)) {
		echo "Invalid week";
		return;
	}

	$user = user_logged_in();

	$games = get_collection(TOTE_COLLECTION_GAMES);

	$gameobjs = $games->find(
		array(
			'season' => (int)$season,
			'week' => (int)$week
		),
		array('home_team', 'away_team', 'home_score', 'away_score', 'start')
	)->sort(array('start' => 1));

	$weekgames = array();
	foreach ($gameobjs as $i => $gameobj) {
		$gameobj['home_team'] = get_team($gameobj['home_team']);
		$gameobj['away_team'] = get_team($gameobj['away_team']);
		$st = new DateTime('@' . $gameobj['start']->sec);
		$st->setTimezone(new DateTimeZone('America/New_York'));
		if (!empty($user['timezone'])) {
			// user preference for time zone
			try {
				$st->setTimezone(new DateTimeZone($user['timezone']));
			} catch (Exception $e) {
			}
		}
		$gameobj['localstart'] = $st;
		$weekgames[] = $gameobj;
	}

	$tpl->assign('year', $season);
	$tpl->assign('week', $week);
	$tpl->assign('games', $weekgames);

	if ($output == 'js')
		$tpl->assign('js', true);

	$tpl->display('schedule.tpl');
}
