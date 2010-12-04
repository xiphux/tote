<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_game_by_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');

/**
 * sort_pools
 *
 * sort pools
 *
 * @param array $a first sort pool
 * @param array $b second sort pool
 */
function sort_pool($a, $b)
{
	// first sort by year descending
	if ($a['season'] != $b['season'])
		return ($a['season'] > $b['season'] ? -1 : 1);

	// then fall back on alphabetical name
	return strcasecmp($a['name'], $b['name']);
}

/**
 * sort_poolentrant
 *
 * sort pool entrants
 *
 * @param array $a first sort entrant
 * @param array $b second sort entrant
 */
function sort_poolentrant($a, $b)
{
	// first sort by wins descending
	if ($a['wins'] != $b['wins'])
		return ($a['wins'] > $b['wins'] ? -1 : 1);

	// then sort by losses ascending
	if ($a['losses'] != $b['losses'])
		return ($a['losses'] > $b['losses'] ? 1 : -1);

	// then sort by point spread descending
	if ($a['spread'] != $b['spread'])
		return ($a['spread'] > $b['spread'] ? -1 : 1);

	// finally fall back on alphabetical
	return sort_users($a['user'], $b['user']);
}

/**
 * pool controller
 *
 * display a pool record
 *
 * @param string $poolID pool id
 */
function display_pool($poolID = null)
{
	global $tpl;

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$games = get_collection(TOTE_COLLECTION_GAMES);;

	// if pool is specified use that, otherwise find most recent pool
	$poolobj = null;
	if (empty($poolID))
		$poolobj = $pools->find()->sort(array('season' => -1))->getNext();
	else
		$poolobj = $pools->findOne(array('_id' => new MongoId($poolID)));

	if (!$poolobj) {
		// we need some pool
		echo "Pool not found";
		return;
	}

	// Find number of weeks in season
	$lastgame = $games->find(
		array(
			'season' => (int)$poolobj['season']
		),
		array('week')
	)->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	// Find weeks that are open for betting
	$poolopen = false;
	$openweeks = array();
	$currentdate = new MongoDate(time());
	$currentweek = 0;
	for ($i = 1; $i <= $weeks; $i++) {
		$opengame = $games->findOne(array('season' => $poolobj['season'], 'week' => $i, 'start' => array('$gt' => $currentdate)), array('week'));
		if ($opengame) {
			$openweeks[$i] = true;
			$poolopen = true;
			if ($currentweek < 1)
				$currentweek = $i;
		} else {
			$openweeks[$i] = false;
		}
	}

	$user = user_logged_in();

	// go through each pool entrant
	$entered = false;
	$poolrecord = array();
	if (!empty($poolobj['entries'])) {
		foreach ($poolobj['entries'] as $entrant) {
		
			// get the user record
			$record = array();
			$record['user'] = get_user($entrant['user']);

			// make sure we note if the logged in user is an entrant in the pool
			if ($user && ($record['user']['username'] == $user['username']))
				$entered = true;

			// map the user's bet indexed by week
			$bets = array();
			if (!empty($entrant['bets'])) {
				foreach ($entrant['bets'] as $bet) {
					$week = $bet['week'];
					if (!empty($week))
						$bets[(int)$week] = array('team' => $bet['team']);
				}
			}

			// do the calculations for the record for each bet game
			foreach ($bets as $week => $bet) {

				// get the game the user bet on
				$gameobj = get_game_by_team($poolobj['season'], $week, $bet['team']);

				if ($gameobj) {

					// if game has scores (game has finished),
					// do some math
					if (isset($gameobj['home_score']) && isset($gameobj['away_score'])) {
						// first calculate the spread and winner as if user bet on the home team
						// result > 0 is a win, result < 0 is a loss, result = 0 is a tie
						$result = 0;
						$gamespread = $gameobj['home_score'] - $gameobj['away_score'];
						if ($gamespread > 0)
							$result = 1;
						else if ($gamespread < 0)
							$result = -1;

						// if the user bet on the away team, invert the result
						if ($gameobj['away_team'] == $bet['team'])
							$result *= -1;

						// point spreads are displayed in absolute values (no negatives)
						$gamespread = abs($gamespread);

						$bets[$week]['result'] = $result;
						$bets[$week]['spread'] = $gamespread;
					}

					// get the data on the teams for the game
					$gameobj['home_team'] = get_team($gameobj['home_team']);
					$gameobj['away_team'] = get_team($gameobj['away_team']);
					$bets[$week]['game'] = $gameobj;
				}
			
				// also load team data
				$bets[$week]['team'] = get_team($bet['team']);
			}

			// now tabulate the full win/loss record,
			// going through all weeks in the season
			$wins = 0;
			$losses = 0;
			$pointspread = 0;
			for ($i = 1; $i <= $weeks; ++$i) {

				if (isset($bets[$i])) {
					// user bet on this week

					if (isset($bets[$i]['result'])) {
						// if we have a result for this bet (game finished)
						if ($bets[$i]['result'] > 0) {
							// user won this bet, add to wins and point spread
							$wins++;
							$pointspread += $bets[$i]['spread'];
						} else if ($bets[$i]['result'] < 0) {
							// user lost this bet, add to losses and substract from point spread
							$losses++;
							$pointspread -= $bets[$i]['spread'];
						}
					}

				} else {
					// no bet for this week

					// enter a placeholder for this week
					$bets[$i] = array();

					if (!$openweeks[$i]) {
						// if this week has no open games, week is closed
						// and user is a no pick (loss) for this week
						$bets[$i]['nopick'] = true;
						$bets[$i]['result'] = -1;
						$losses++;

						if (($weeks - $i) < 4) {
							// no pick in last 4 weeks is 10 point penalty
							$bets[$i]['spread'] = 10;
							$pointspread -= 10;
						}
					}
				}
			}

			// sort bets in week order
			ksort($bets);

			$record['bets'] = $bets;
			$record['wins'] = $wins;
			$record['losses'] = $losses;
			$record['spread'] = $pointspread;

			$poolrecord[] = $record;
		}
	}

	// sort pool according to status
	usort($poolrecord, 'sort_poolentrant');

	// get list of all pools
	$allpoolcollect = $pools->find(array(), array('season', 'name'));
	$allpools = array();
	foreach ($allpoolcollect as $p) {
		$allpools[] = $p;
	}
	// and sort them
	usort($allpools, 'sort_pool');

	// set data and display
	if (count($allpools) > 1)
		$tpl->assign('allpools', $allpools);
	if ($currentweek > 0)
		$tpl->assign('currentweek', $currentweek);
	$tpl->assign('weeks', $openweeks);
	$tpl->assign('record', $poolrecord);
	$tpl->assign('pool', $poolobj);

	if ($user) {
		$tpl->assign('user', $user);
	}
	$tpl->assign('entered', $entered);
	$tpl->assign('poolopen', $poolopen);

	$tpl->display('pool.tpl');
}

