<?php

require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');

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
	return strcasecmp($a['user']['display_name'], $b['user']['display_name']);
}

/**
 * Gets the score record for a pool
 *
 * @param object $poolid pool id
 * @return array score data array
 */
function get_pool_record($poolid)
{
	global $tote_conf, $mysqldb;

	if (empty($poolid))
		return null;

	$cachetpl = null;
	$cachekey = 'pool|' . $poolid;
	if (!empty($tote_conf['cache']) && ($tote_conf['cache'] === true)) {
		// if caching is turned on, try deserializing the calculated
		// record from the cache
		$cachetpl = new Smarty;
		$cachetpl->caching = 2;
		if ($cachetpl->is_cached('data.tpl', $cachekey)) {
			return unserialize($cachetpl->fetch('data.tpl', $cachekey));
		}
	}

	$season = null;
	$seasonstmt = $mysqldb->prepare('SELECT seasons.year FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE pools.id=?');
	$seasonstmt->bind_param('i', $poolid);
	$seasonstmt->bind_result($season);
	$seasonstmt->execute();
	$found = $seasonstmt->fetch();
	$seasonstmt->close();

	if (!$found)
		return null;

	// Find number of weeks in season
	$weeks = get_season_weeks($season);
	// Find weeks that are open for betting
	$openweeks = get_open_weeks($season);

	// get all picks this season, as well as user, game, and team info
	// TODO: can we make this perform better?
	$pickquery = <<<EOQ
SELECT
users.id AS user_id,
(CASE
 WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name)
 WHEN users.first_name IS NOT NULL THEN users.first_name
 ELSE users.username
END) AS display_name,
pool_entry_picks.team_id AS pick_team_id,
pick_teams.abbreviation AS pick_team_abbr,
pool_entry_picks.week AS week,
home_teams.id AS home_team_id,
home_teams.abbreviation AS home_team_abbr,
away_teams.id AS away_team_id,
away_teams.abbreviation AS away_team_abbr,
games.home_score AS home_score,
games.away_score AS away_score
FROM %s AS pool_entry_picks
RIGHT JOIN %s AS pool_entries
ON pool_entry_picks.pool_entry_id=pool_entries.id
LEFT JOIN %s AS pick_teams
ON pool_entry_picks.team_id=pick_teams.id
LEFT JOIN %s AS users
ON pool_entries.user_id=users.id
LEFT JOIN %s AS pools
ON pool_entries.pool_id=pools.id
LEFT JOIN %s AS games
ON pools.season_id=games.season_id AND pool_entry_picks.week=games.week AND (pool_entry_picks.team_id=games.away_team_id OR pool_entry_picks.team_id=games.home_team_id)
LEFT JOIN %s AS home_teams
ON games.home_team_id=home_teams.id
LEFT JOIN %s AS away_teams
ON games.away_team_id=away_teams.id
WHERE pool_entries.pool_id=?
ORDER BY users.id, pool_entry_picks.week
EOQ;
	$pickquery = sprintf($pickquery, TOTE_TABLE_POOL_ENTRY_PICKS, TOTE_TABLE_POOL_ENTRIES, TOTE_TABLE_TEAMS, TOTE_TABLE_USERS, TOTE_TABLE_POOLS, TOTE_TABLE_GAMES, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS);
	$pickstmt = $mysqldb->prepare($pickquery);
	$pickstmt->bind_param('i', $poolid);
	$pickstmt->execute();
	$pickresult = $pickstmt->get_result();

	// go through each pool entrant
	$poolrecord = array();

	$lastuserid = null;
	$lastidx = -1;
	while ($pick = $pickresult->fetch_assoc()) {
		if ($pick['user_id'] != $lastuserid) {
			// bootstrap user entry
			++$lastidx;
			$poolrecord[$lastidx] = array(
				'user' => array(
					'id' => $pick['user_id'],
					'display_name' => $pick['display_name']
				),
				'wins' => 0,
				'losses' => 0,
				'ties' => 0,
				'spread' => 0,
				'bets' => array()
			);
			for ($i = 1; $i <= $weeks; ++$i) {
				$poolrecord[$lastidx]['bets'][$i] = array();
			}
			$lastuserid = $pick['user_id'];
		}

		if (!$pick['pick_team_id']) {
			// user hasn't picked anything
			continue;
		}

		// if game has scores (game has finished),
		// do some math
		if (isset($pick['home_score']) && isset($pick['away_score'])) {
			// first calculate the spread and winner as if user picked the home team
			// result > 0 is a win, result < 0 is a loss, result = 0 is a tie
			$result = null;
			$gamespread = (int)$pick['home_score'] - (int)$pick['away_score'];
			if ($gamespread > 0)
				$result = 1;
			else if ($gamespread < 0)
				$result = -1;
			else if ($gamespread == 0)
				$result = 0;

			// if the user picked the away team, invert the result
			if ($pick['pick_team_id'] == $pick['away_team_id'])
				$result *= -1;

			// point spreads are always positive
			$gamespread = abs($gamespread);

			if ($result !== null)
				$pick['result'] = $result;
			$pick['spread'] = $gamespread;

			if ($result > 0) {
				// user won this pick, add to wins and point spread
				$poolrecord[$lastidx]['wins']++;
				$poolrecord[$lastidx]['spread'] += $gamespread;
			} else if ($result < 0) {
				// user lost this pick, add to losses and subtract from point spread
				$poolrecord[$lastidx]['losses']++;
				$poolrecord[$lastidx]['spread'] -= $gamespread;
			} else if ($result === 0) {
				// user tied, add to ties (no point spread)
				$poolrecord[$lastidx]['ties']++;
			}
		}

		$pickdata = array(
			'team' => array(
				'id' => $pick['pick_team_id'],
				'abbreviation' => $pick['pick_team_abbr']
			),
			'game' => array(
				'home_team' => array(
					'id' => $pick['home_team_id'],
					'abbreviation' => $pick['home_team_abbr']
				),
				'away_team' => array(
					'id' => $pick['away_team_id'],
					'abbreviation' => $pick['away_team_abbr']
				)
			)
		);
		
		if (isset($pick['home_score']) && ($pick['home_score'] !== null))
			$pickdata['game']['home_score'] = $pick['home_score'];
		if (isset($pick['away_score']) && ($pick['away_score'] !== null))
			$pickdata['game']['away_score'] = $pick['away_score'];

		if (isset($pick['result']))
			$pickdata['result'] = $pick['result'];
		if (isset($pick['spread']))
			$pickdata['spread'] = $pick['spread'];

		$poolrecord[$lastidx]['bets'][(int)$pick['week']] = $pickdata;
				
	}

	$pickresult->close();
	$pickstmt->close();

	foreach ($poolrecord as $useridx => $user) {

		// check for no pick weeks
		for ($i = 1; $i <= $weeks; ++$i) {

			if (!isset($poolrecord[$useridx]['bets'][$i]['team'])) {

				if (!$openweeks[$i]) {
					// if this week has no open games, week is closed
					// and user is a no pick (loss) for this week
					$poolrecord[$useridx]['bets'][$i]['nopick'] = true;
					$poolrecord[$useridx]['bets'][$i]['result'] = -1;
					$poolrecord[$useridx]['losses'] += 1;

					if (($weeks - $i) < 4) {
						// no pick in last 4 weeks is 10 point penalty
						$poolrecord[$useridx]['bets'][$i]['spread'] = 10;
						$poolrecord[$useridx]['spread'] -= 10;
					}
				}
			}
		}

	}

	// sort pool according to status
	usort($poolrecord, 'sort_poolentrant');

	if (!empty($tote_conf['cache']) && ($tote_conf['cache'] === true)) {
		// if cache is enabled, store calculated record into the cache
		// so we don't have to recalculate it
	
		$currentweek = array_search(true, $openweeks, true);
		if ($currentweek === false) {
			// season is over, no need to have cache expire
			$cachetpl->cache_lifetime = -1;
		} else {
			// set cache to expire as soon as the last game of the
			// week starts
			// (so we can recalculate 'No Pick' players)
			$laststart = null;
			$laststartstmt = $mysqldb->prepare('SELECT MAX(games.start) FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=? AND games.week=?');
			$laststartstmt->bind_param('ii', $season, $currentweek);
			$laststartstmt->bind_result($laststart);
			$laststartstmt->execute();
			$laststartstmt->fetch();
			$laststartstmt->close();

			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$cachetpl->cache_lifetime = strtotime($laststart) - time();
			date_default_timezone_set($tz);
		}
		$cachetpl->assign('data', serialize($poolrecord));
		
		// force into cache
		$tmp = $cachetpl->fetch('data.tpl', $cachekey);
		unset($tmp);
	}

	return $poolrecord;
}
