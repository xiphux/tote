<?php

require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

function sort_teams($a, $b)
{
	return strcmp(($a['home'] . ' ' . $a['team']), ($b['home'] . ' ' . $b['team']));
}

function display_bet($poolID, $week)
{
	global $db, $tote_conf, $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$poolcol = 'pools';
	$usercol = 'users';
	$gamecol = 'games';
	if (!empty($tote_conf['namespace'])) {
		$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;
	}

	$pools = $db->selectCollection($poolcol);
	$users = $db->selectCollection($usercol);
	$games = $db->selectCollection($gamecol);

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'timezone'));
	if (!$user) {
		echo "User not found";
		return;
	}

	if (empty($week)) {
		echo "Week is required";
		return;
	}

	$userentry = null;
	foreach ($pool['entries'] as $entry) {
		if ($entry['user'] == $user['_id']) {
			$userentry = $entry;
			break;
		}
	}

	if (!$userentry) {
		echo "You are not entered in this pool";
		return;
	}

	$gameobjs = $games->find(array('season' => (int)$pool['season'], 'week' => (int)$week), array('home_team', 'away_team', 'home_score', 'away_score', 'start'))->sort(array('start' => 1));
	$availableteams = array();
	$weekgames = array();
	$now = time();
	foreach ($gameobjs as $i => $gameobj) {
		// Make a list of games and teams playing this week
		$home = get_team($gameobj['home_team']);
		$away = get_team($gameobj['away_team']);
		$gameobj['home_team'] = $home;
		$gameobj['away_team'] = $away;
		$st = new DateTime('@' . $gameobj['start']->sec);
		$st->setTimezone(new DateTimeZone('America/New_York'));
		if (!empty($user['timezone'])) {
			try {
				$st->setTimezone(new DateTimeZone($user['timezone']));
			} catch (Exception $e) {
			}
		}
		$gameobj['localstart'] = $st;
		$weekgames[] = $gameobj;
		if ($gameobj['start']->sec > $now) {
			$availableteams[(string)$home['_id']] = $home;
			$availableteams[(string)$away['_id']] = $away;
		}
	}

	$bets = array();
	foreach ($userentry['bets'] as $bet) {
		// Don't allow teams player already bet on
		$team = get_team($bet['team']);
		$bets[(int)$bet['week']] = $team;
		unset($availableteams[(string)$team['_id']]);
	}

	uasort($availableteams, 'sort_teams');
	$tpl->assign('teams', $availableteams);
	$tpl->assign('week', $week);
	if (count($bets) > 0) {
		$tpl->assign('bets', $bets);
	}
	$tpl->assign('games', $weekgames);
	$tpl->assign('pool', $pool);
	$tpl->display('bet.tpl');
}

