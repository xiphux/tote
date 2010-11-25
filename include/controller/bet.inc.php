<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

/**
 * sort teams according to full team name (home & team)
 *
 * @param object $a first team object
 * @param object $b second team object
 */
function sort_teams($a, $b)
{
	return strcmp(($a['home'] . ' ' . $a['team']), ($b['home'] . ' ' . $b['team']));
}

/**
 * bet controller
 *
 * displays betting page for users
 *
 * @param string $poolID pool id
 * @param string $week week
 */
function display_bet($poolID, $week)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (empty($poolID)) {
		// need to know the pool
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$games = get_collection(TOTE_COLLECTION_GAMES);

	$pool = $pools->findOne(
		array('_id' => new MongoId($poolID)),
		array('season', 'entries')
	);
	if (!$pool) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (empty($week)) {
		// week is required
		echo "Week is required";
		return;
	}

	// find the user's entry in the pool
	$userentry = null;
	foreach ($pool['entries'] as $entry) {
		if ($entry['user'] == $user['_id']) {
			$userentry = $entry;
			break;
		}
	}

	if (!$userentry) {
		// can't bet if you aren't in the pool
		echo "You are not entered in this pool";
		return;
	}

	// check if user already bet this week
	if (!empty($userentry['bets'])) {
		foreach ($userentry['bets'] as $bet) {
			if (($bet['week'] == $week) && (!empty($bet['team']))) {
				$prevbet = get_team($bet['team']);
				echo 'You already bet on the ' . $prevbet['home'] . ' ' . $prevbet['team'] . ' for week ' . $week;
				return;
			}
		}
	}

	// find all games for this week in chronological order
	$gameobjs = $games->find(
		array(
			'season' => (int)$pool['season'],
			'week' => (int)$week
		),
		array('home_team', 'away_team', 'home_score', 'away_score', 'start')
	)->sort(array('start' => 1));


	$availableteams = array();
	$weekgames = array();
	$now = time();
	foreach ($gameobjs as $i => $gameobj) {

		// Make a list of games and teams playing this week

		// load info for each game
		$home = get_team($gameobj['home_team']);
		$away = get_team($gameobj['away_team']);
		$gameobj['home_team'] = $home;
		$gameobj['away_team'] = $away;
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

		// if game hasn't started yet, add teams to the list
		// of available teams to bet on
		if ($gameobj['start']->sec > $now) {
			$availableteams[(string)$home['_id']] = $home;
			$availableteams[(string)$away['_id']] = $away;
		}
	}

	$bets = array();
	if (!empty($userentry['bets'])) {
		foreach ($userentry['bets'] as $bet) {
			// remove teams player has already bet on from
			// the list of available teams
			$team = get_team($bet['team']);
			$bets[(int)$bet['week']] = $team;
			unset($availableteams[(string)$team['_id']]);
		}
	}

	// sort available teams
	uasort($availableteams, 'sort_teams');

	// set data for display
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('teams', $availableteams);
	$tpl->assign('week', $week);
	if (count($bets) > 0) {
		$tpl->assign('bets', $bets);
	}
	$tpl->assign('games', $weekgames);
	$tpl->assign('pool', $pool);
	$tpl->display('bet.tpl');
}

