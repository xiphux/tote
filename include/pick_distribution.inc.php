<?php

/**
 * Gets pick distribution across pools
 *
 * @return array pick distribution data
 */
function pick_distribution()
{
	global $mysqldb;

	$distresult = $mysqldb->query('SELECT pools.id AS pool_id, pools.name AS name, seasons.year AS season, teams.abbreviation AS team, COUNT(pool_entries.user_id) AS count FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entry_picks.pool_entry_id=pool_entries.id LEFT JOIN ' . TOTE_TABLE_POOLS . ' AS pools ON pool_entries.pool_id=pools.id LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS teams ON pool_entry_picks.team_id=teams.id GROUP BY pools.id, teams.id ORDER BY seasons.year DESC, pools.name');

	$distdata = array();

	$poolidx = 0;
	$lastpoolid = -1;
	while ($dist = $distresult->fetch_assoc()) {
		if ($lastpoolid != $dist['pool_id']) {
			++$poolidx;
			$distdata[$poolidx] = array(
				'name' => $dist['name'],
				'season' => (int)$dist['season'],
				'picks' => array()
			);
			$lastpoolid = $dist['pool_id'];
		}

		$distdata[$poolidx]['picks'][$dist['team']] = (int)$dist['count'];
	}

	$distresult->close();

	return $distdata;
}
