<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_game_by_team.inc.php');

/**
 * Gets pick risk history across pools
 *
 * @return array pick risk
 */
function pick_risk()
{
	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$poolobjects = $pools->find(
		array(),
		array('entries', 'name', 'season')
	)->sort(array('season' => -1, 'name' => 1));

	$pooldata = array();

	foreach ($poolobjects as $pool) {
		if (empty($pool['entries']) || (count($pool['entries']) < 1))
			continue;

		$poolid = (string)$pool['_id'];
		$openweeks = get_open_weeks($pool['season']);
		if (isset($openweeks[2]) && ($openweeks[2] == true)) {
			// only one week of data to show, not very useful
			continue;
		}

		$games = array();

		foreach ($pool['entries'] as $entrant) {

			if (empty($entrant['bets']) || (count($entrant['bets']) < 1))
				continue;

			$user = get_user($entrant['user']);
			$userid = (string)$user['_id'];

			if (!$userid)
				continue;

			$userdata = array();
			if (isset($user['username']))
				$userdata['username'] = $user['username'];
			if (isset($user['first_name']))
				$userdata['first_name'] = $user['first_name'];
			if (isset($user['last_name']))
				$userdata['last_name'] = $user['last_name'];
			
			$entrantdata = array(
				'picks' => array(),
				'user' => $userdata
			);

			$entrantgames = array();

			foreach ($entrant['bets'] as $bet) {
				if (empty($bet['team']))
					continue;

				if (isset($openweeks[$bet['week']]) && ($openweeks[$bet['week']] == true)) {
					// don't give away point spreads for open weeks
					continue;
				}

				$game = get_game_by_team($pool['season'], $bet['week'], $bet['team']);
				if (!$game)
					continue;

				$gameid = (string)$game['_id'];

				$gamedata = array(
					'home_team' => (string)$game['home_team'],
					'away_team' => (string)$game['away_team'],
					'week' => $game['week']
				);
				if (isset($game['home_score']))
					$gamedata['home_score'] = $game['home_score'];
				if (isset($game['away_score']))
					$gamedata['away_score'] = $game['away_score'];
				if (isset($game['favorite']))
					$gamedata['favorite'] = (string)$game['favorite'];
				if (isset($game['point_spread']))
					$gamedata['point_spread'] = $game['point_spread'];

				$entrantgames[$gameid] = $gamedata;

				$entrantdata['picks'][] = array(
					'game' => $gameid,
					'team' => (string)$bet['team'],
					'week' => $bet['week']
				);
			}

			if (count($entrantdata['picks']) < 1)
				continue;

			$games = array_merge($games, $entrantgames);

			if (!isset($pooldata[$poolid])) {
				$pooldata[$poolid] = array(
					'name' => $pool['name'],
					'season' => $pool['season'],
					'weeks' => get_season_weeks($pool['season']),
					'entries' => array()
				);
			}

			$pooldata[$poolid]['entries'][] = $entrantdata;

		}

		if (isset($pooldata[$poolid])) {
			$pooldata[$poolid]['games'] = $games;
		}

	}

	return $pooldata;
}
