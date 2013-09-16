<?php

/**
 * Gets pick risk history across pools
 *
 * @return array pick risk
 */
function pick_risk()
{
	global $mysqldb;

	$seasonweeks = array();
	$seasonweekstmt = $mysqldb->prepare('SELECT seasons.year, MAX(games.week) AS weeks FROM games AS games LEFT JOIN seasons AS seasons ON games.season_id=seasons.id GROUP BY seasons.year');
	$season = null;
	$week = null;
	$seasonweekstmt->bind_result($season, $week);
	$seasonweekstmt->execute();
	while ($seasonweekstmt->fetch()) {
		$seasonweeks[$season] = (int)$week;
	}
	$seasonweekstmt->close();

	$openweeks = array();
	$openweekstmt = $mysqldb->prepare('SELECT seasons.year, games.week, MAX(games.start>UTC_TIMESTAMP()) AS open FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id GROUP BY seasons.year, games.week ORDER BY seasons.year, games.week');
	$openweekstmt->execute();
	$openweekresult = $openweekstmt->get_result();
	while ($openweek = $openweekresult->fetch_assoc()) {
		$openweeks[$openweek['year']][(int)$openweek['week']] = ($openweek['open'] == 1);
	}
	$openweekresult->close();
	$openweekstmt->close();

	$dataquery = <<<EOQ
SELECT
seasons.year AS season,
pools.id AS pool_id,
pools.name AS pool_name,
users.id AS user_id,
users.username AS username,
users.first_name AS user_first_name,
users.last_name AS user_last_name,
games.id AS game_id,
pool_entry_picks.week AS pick_week,
pool_entry_picks.team_id AS team_id,
games.home_team_id AS home_team_id,
games.away_team_id AS away_team_id,
games.home_score AS home_score,
games.away_score AS away_score,
games.week AS game_week,
games.favorite_id AS favorite_id,
games.point_spread AS point_spread
FROM %s AS pool_entry_picks
LEFT JOIN %s AS pool_entries ON pool_entry_picks.pool_entry_id=pool_entries.id
LEFT JOIN %s AS pools ON pool_entries.pool_id=pools.id
LEFT JOIN %s AS seasons ON seasons.id=pools.season_id
LEFT JOIN %s AS games ON seasons.id=games.season_id AND pool_entry_picks.week=games.week AND (pool_entry_picks.team_id=games.home_team_id OR pool_entry_picks.team_id=games.away_team_id)
LEFT JOIN %s AS users ON pool_entries.user_id=users.id
ORDER BY seasons.year DESC, pools.name, users.id, pool_entry_picks.week
EOQ;
	$dataquery = sprintf($dataquery, TOTE_TABLE_POOL_ENTRY_PICKS, TOTE_TABLE_POOL_ENTRIES, TOTE_TABLE_POOLS, TOTE_TABLE_SEASONS, TOTE_TABLE_GAMES, TOTE_TABLE_USERS);
	$datastmt = $mysqldb->prepare($dataquery);
	$datastmt->execute();
	$dataresult = $datastmt->get_result();

	$pooldata = array();
	$entrantidx = -1;
	$lastentrantid = null;
	while ($data = $dataresult->fetch_assoc()) {
		if ($openweeks[$data['season']][2]) {
			// not useful with one full week of data (week 2 is still in progress)
			continue;
		}

		if ($openweeks[$data['season']][(int)$data['pick_week']]) {
			// don't show spreads for weeks still in progress
			continue;
		}

		$poolid = 'p' . $data['pool_id'];
		if (!isset($pooldata[$poolid])) {
			// store data about pool
			$pooldata[$poolid] = array(
				'name' => $data['pool_name'],
				'season' => (int)$data['season'],
				'weeks' => $seasonweeks[$data['season']],
				'entries' => array(),
				'games' => array()
			);

			$lastentrantid = null;
			$entrantidx = -1;
		}

		if ($lastentrantid != $data['user_id']) {
			$lastentrantid = $data['user_id'];
			$entrantidx++;

			$pooldata[$poolid]['entries'][$entrantidx] = array(
				// store data about entrant
				'user' => array(
					'username' => $data['username'],
					'first_name' => $data['user_first_name'],
					'last_name' => $data['user_last_name']
				),
				'picks' => array()
			);
			
		}

		$gameid = 'g' . $data['game_id'];
		$pooldata[$poolid]['entries'][$entrantidx]['picks'][] = array(
			// store data about pick
			'game' => $gameid,
			'team' => $data['team_id'],
			'week' => $data['pick_week']
		);

		if (!isset($pooldata[$poolid]['games'][$gameid])) {
			// store data for any games that have been picked
			$gamedata = array(
				'home_team' => $data['home_team_id'],
				'away_team' => $data['away_team_id'],
				'week' => $data['game_week']
			);
			if ($data['home_score'] !== null)
				$gamedata['home_score'] = $data['home_score'];
			if ($data['away_score'] !== null)
				$gamedata['away_score'] = $data['away_score'];
			if ($data['favorite_id'] !== null)
				$gamedata['favorite'] = $data['favorite_id'];
			if ($data['point_spread'] !== null)
				$gamedata['point_spread'] = $data['point_spread'];

			$pooldata[$poolid]['games'][$gameid] = $gamedata;
		}

	}

	$dataresult->close();
	$datastmt->close();

	return $pooldata;
}
