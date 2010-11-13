<?php

$poolcol = 'pools';
$usercol = 'users';
$gamecol = 'games';
$teamcol = 'teams';
if (!empty($tote_conf['namespace'])) {
	$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
	$usercol = $tote_conf['namespace'] . '.' . $usercol;
	$gamecol = $tote_conf['namespace'] . '.' . $gamecol;
	$teamcol = $tote_conf['namespace'] . '.' . $teamcol;
}

$pools = $db->selectCollection($poolcol);
$users = $db->selectCollection($usercol);
$games = $db->selectCollection($gamecol);
$teams = $db->selectCollection($teamcol);

$teamcache = array();

function get_team($id)
{
	global $teamcache, $teams;

	if (empty($teamcache[(string)$id])) {
		$teamcache[(string)$id] = $teams->findOne(array('_id' => $id), array('team', 'home', 'abbreviation'));
	}

	return $teamcache[(string)$id];
}

function sort_teams($a, $b)
{
	return strcmp(($a['home'] . ' ' . $a['team']), ($b['home'] . ' ' . $b['team']));
}

if (!empty($_GET['p'])) {
	$pool = $pools->findOne(array('_id' => new MongoId($_GET['p'])), array('season', 'entries'));
	if ($pool) {
		$user = $users->findOne(array('username' => $_SESSION['user']), array('username'));
		if ($user) {
			if (!empty($_GET['w'])) {
				$userentry = null;
				foreach ($pool['entries'] as $entry) {
					if ($entry['user'] == $user['_id']) {
						$userentry = $entry;
						break;
					}
				}
				if ($userentry) {
					$availableteams = array();
					$gameobjs = $games->find(array('season' => (int)$pool['season'], 'week' => (int)$_GET['w']), array('home_team', 'away_team', 'home_score', 'away_score', 'start'))->sort(array('start' => 1));
					$now = time();
					$weekgames = array();
					foreach ($gameobjs as $i => $gameobj) {
						$home = get_team($gameobj['home_team']);
						$away = get_team($gameobj['away_team']);
						$gameobj['home_team'] = $home;
						$gameobj['away_team'] = $away;
						$weekgames[] = $gameobj;
						if ($gameobj['start']->sec > $now) {
							$availableteams[(string)$home['_id']] = $home;
							$availableteams[(string)$away['_id']] = $away;
						}
					}

					$bets = array();
					foreach ($userentry['bets'] as $bet) {
						$team = get_team($bet['team']);
						$bets[(int)$bet['week']] = $team;
						unset($availableteams[(string)$team['_id']]);
					}

					$tpl->assign('week', $_GET['w']);
					if (count($bets) > 0) {
						$tpl->assign('bets', $bets);
					}
					uasort($availableteams, 'sort_teams');
					$tpl->assign('teams', $availableteams);
					$tpl->assign('games', $weekgames);
					$tpl->assign('pool', $pool);
					$tpl->display('bet.tpl');
				} else {
					echo "You are not entered in this pool";
				}
			} else {
				echo "Week is required";
			}
		} else {
			echo "User not found";
		}
	} else {
		echo "Unknown pool";
	}
} else {
	echo "Pool is required";
}
