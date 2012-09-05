<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

function teamcmp($a, $b)
{
	$teama = get_team($a);
	$teamb = get_team($b);

	return strcmp($teama['abbreviation'], $teamb['abbreviation']);
}

/**
 * gridschedule controller
 *
 * displays the season schedule as a grid
 *
 * @param string $year season year
 */
function display_gridschedule($season)
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

	$gameobjs = $games->find(
		array(
			'season' => (int)$season
		),
		array(
			'home_team', 'away_team', 'home_score', 'away_score', 'week', 'start'
		)
	);

	$seasonweeks = get_season_weeks($season);

	$teamgames = array();
	$teamabbrs = array();
	foreach ($gameobjs as $i => $gameobj) {
		$gameobj['home_team'] = get_team($gameobj['home_team']);
		$gameobj['away_team'] = get_team($gameobj['away_team']);
		$gameobj['localstart'] = get_local_datetime($gameobj['start']);
		$teamgames[(string)$gameobj['home_team']['_id']][$gameobj['week']] = $gameobj;
		$teamgames[(string)$gameobj['away_team']['_id']][$gameobj['week']] = $gameobj;
	}
	foreach ($teamgames as $eachteam => $teamsched) {
		for ($i = 1; $i <= $seasonweeks; $i++) {
			if (!isset($teamsched[$i])) {
				$teamgames[$eachteam][$i] = array('bye' => true);
			}
		}
		ksort($teamgames[$eachteam]);

		$teamobj = get_team($eachteam);
		$teamabbrs[$eachteam] = $teamobj['abbreviation'];
	}
	uksort($teamgames, 'teamcmp');
	
	$tpl->assign('teamabbrs', $teamabbrs);
	$tpl->assign('games', $teamgames);

	$tpl->assign('year', $season);
	$tpl->assign('allseasons', array_reverse(get_seasons()));

	$tpl->assign('openweeks', get_open_weeks($season));

	$tpl->display('gridschedule.tpl');
}
