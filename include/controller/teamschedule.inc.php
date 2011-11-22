<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');

define('SCHEDULE_HEADER', 'View Game Schedule');

function teamcmp($a, $b)
{
	$teama = get_team($a);
	$teamb = get_team($b);

	$teamnamea = $teama['home'] . ' ' . $teama['team'];
	$teamnameb = $teamb['home'] . ' ' . $teamb['team'];

	return strcmp($teamnamea, $teamnameb);
}

/**
 * teamschedule controller
 *
 * displays the game schedule for a given team
 *
 * @param string $year season year
 * @param string $team team id
 * @param string $output output format
 */
function display_teamschedule($season, $team = null, $output = 'html', $week = null)
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

	$search = array(
		'season' => (int)$season
	);
	if (!empty($team)) {
		$search['$or'] = array(
			array('home_team' => new MongoId($team)),
			array('away_team' => new MongoId($team))
		);
	}

	$gameobjs = $games->find(
		$search,
		array('home_team', 'away_team', 'home_score', 'away_score', 'start', 'week')
	)->sort(array('start' => 1));

	$seasonweeks = get_season_weeks($season);

	$teamgames = array();
	if (!empty($team)) {

		foreach ($gameobjs as $i => $gameobj) {
			$gameobj['home_team'] = get_team($gameobj['home_team']);
			$gameobj['away_team'] = get_team($gameobj['away_team']);
			$gameobj['localstart'] = get_local_datetime($gameobj['start']);
			$teamgames[$gameobj['week']] = $gameobj;
		}

		for ($i = 1; $i <= $seasonweeks; $i++) {
			if (!isset($teamgames[$i])) {
				$teamgames[$i] = array('bye' => true);
			}
		}
		ksort($teamgames);
	} else {
		$teammapped = array();
		$teamnames = array();
		foreach ($gameobjs as $i => $gameobj) {
			$gameobj['home_team'] = get_team($gameobj['home_team']);
			$gameobj['away_team'] = get_team($gameobj['away_team']);
			$gameobj['localstart'] = get_local_datetime($gameobj['start']);
			$teammapped[(string)$gameobj['home_team']['_id']][$gameobj['week']] = $gameobj;
			$teammapped[(string)$gameobj['away_team']['_id']][$gameobj['week']] = $gameobj;
		}
		foreach ($teammapped as $eachteam => $teamsched) {
			for ($i = 1; $i <= $seasonweeks; $i++) {
				if (!isset($teamsched[$i])) {
					$teammapped[$eachteam][$i] = array('bye' => true);
				}
			}
			ksort($teammapped[$eachteam]);

			$teamobj = get_team($eachteam);
			$teamnames[$eachteam] = $teamobj['home'] . ' ' . $teamobj['team'];
		}
		uksort($teammapped, 'teamcmp');
		$teamgames = $teammapped;
		$tpl->assign('teamnames', $teamnames);
	}

	if (!empty($team)) {
		$tpl->assign('team', get_team($team));
	}
	if (empty($week)) {
		$tpl->assign('allseasons', array_reverse(get_seasons()));
	}
	$tpl->assign('year', $season);
	$tpl->assign('games', $teamgames);
	if (!empty($week))
		$tpl->assign('week', $week);

	if ($output == 'js')
		$tpl->assign('js', true);

	if (empty($team)) {
		$tpl->display('fullteamschedule.tpl');
	} else {
		$tpl->display('teamschedule.tpl');
	}
}
