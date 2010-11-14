<?php

require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_game_by_team.inc.php');

function sort_pool($a, $b)
{
	// first wins descending
	if ($a['wins'] != $b['wins'])
		return ($a['wins'] > $b['wins'] ? -1 : 1);

	// then losses ascending
	if ($a['losses'] != $b['losses'])
		return ($a['losses'] > $b['losses'] ? 1 : -1);

	// then spread descending
	if ($a['spread'] != $b['spread'])
		return ($a['spread'] > $b['spread'] ? -1 : 1);

	$user1 = $a['user']['username'];
	if (!empty($a['user']['first_name'])) {
		$user1 = $a['user']['first_name'];
		if (!empty($a['user']['last_name']))
			$user1 .= ' ' . $a['user']['last_name'];
		$user1 = trim($user1);
	}
	$user2 = $b['user']['username'];
	if (!empty($b['user']['first_name'])) {
		$user2 = $b['user']['first_name'];
		if (!empty($b['user']['last_name']))
			$user2 .= ' ' . $b['user']['last_name'];
		$user2 = trim($user2);
	}

	return strcmp($user1, $user2);
}

function display_pool($poolID = null)
{
	global $db, $tote_conf, $tpl;

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

	$poolobj = null;

	if (empty($poolID))
		$poolobj = $pools->find()->sort(array('season' => -1))->getNext();
	else
		$poolobj = $pools->findOne(array('_id' => new MongoId($poolID)));

	if (!$poolobj) {
		echo "Pool not found";
		return;
	}

	// Find number of weeks
	$lastgame = $games->find(array('season' => (int)$poolobj['season']), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	// Find weeks that are open for betting
	$poolopen = false;
	$openweeks = array();
	$currentdate = new MongoDate(time());
	for ($i = 1; $i <= $weeks; $i++) {
		$opengame = $games->findOne(array('season' => $poolobj['season'], 'week' => $i, 'start' => array('$gt' => $currentdate)), array('week'));
		if ($opengame) {
			$openweeks[$i] = true;
			$poolopen = true;
		} else {
			$openweeks[$i] = false;
		}
	}

	$entered = false;
	$poolrecord = array();
	foreach ($poolobj['entries'] as $entrant) {
		
		$record = array();
		$record['user'] = $users->findOne(array('_id' => $entrant['user']), array('username', 'first_name', 'last_name'));
		if (!empty($_SESSION['user']) && ($record['user']['username'] == $_SESSION['user']))
			$entered = true;

		$bets = array();
		foreach ($entrant['bets'] as $bet) {
			// remap bets indexed by week
			$week = $bet['week'];
			if (!empty($week))
				$bets[(int)$week] = array('team' => $bet['team']);
		}

		foreach ($bets as $week => $bet) {
			// find the result of each bet
	
			$gameobj = get_game_by_team($poolobj['season'], $week, $bet['team']);

			if ($gameobj) {
				if (isset($gameobj['home_score']) && isset($gameobj['away_score'])) {
					$result = 0;
					$gamespread = $gameobj['home_score'] - $gameobj['away_score'];
					if ($gamespread > 0)
						$result = 1;
					else if ($gamespread < 0)
						$result = -1;
					if ($gameobj['away_team'] == $bet['team'])
						$result *= -1;
					$gamespread = abs($gamespread);

					$bets[$week]['result'] = $result;
					$bets[$week]['spread'] = $gamespread;
				}

				$gameobj['home_team'] = get_team($gameobj['home_team']);
				$gameobj['away_team'] = get_team($gameobj['away_team']);
				$bets[$week]['game'] = $gameobj;
			}
		
			// also load team object
			$bets[$week]['team'] = get_team($bet['team']);
		}

		$wins = 0;
		$losses = 0;
		$pointspread = 0;
		for ($i = 1; $i <= $weeks; ++$i) {
			// tabulate
			if (isset($bets[$i])) {
				// has bet
				if (isset($bets[$i]['result'])) {
					if ($bets[$i]['result'] > 0) {
						$wins++;
						$pointspread += $bets[$i]['spread'];
					} else if ($bets[$i]['result'] < 0) {
						$losses++;
						$pointspread -= $bets[$i]['spread'];
					}
				}
			} else {
				// no bet
				$bets[$i] = array();
				if (!$openweeks[$i]) {
					$bets[$i]['nopick'] = true;
					$bets[$i]['result'] = -1;
					$losses++;

					if (($weeks - $i) < 4) {
						// no picks in last 4 weeks is 10 point penalty
						$bets[$i]['spread'] = 10;
						$pointspread -= 10;
					}
				}
			}
		}

		ksort($bets);

		$record['bets'] = $bets;
		$record['wins'] = $wins;
		$record['losses'] = $losses;
		$record['spread'] = $pointspread;

		$poolrecord[] = $record;
	}

	usort($poolrecord, 'sort_pool');

	$tpl->assign('weeks', $openweeks);
	$tpl->assign('record', $poolrecord);
	$tpl->assign('pool', $poolobj);

	if (!empty($_SESSION['user'])) {
		$loginuser = $users->findOne(array('username' => $_SESSION['user']), array('first_name', 'last_name', 'username', 'admin'));
		$tpl->assign('user', $loginuser);
	}
	$tpl->assign('entered', $entered);
	$tpl->assign('poolopen', $poolopen);

	$tpl->display('pool.tpl');
}

