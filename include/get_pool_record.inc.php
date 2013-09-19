<?php

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

	$recordstmt = $mysqldb->prepare("SELECT user_id, (CASE WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name) WHEN users.first_name IS NOT NULL THEN users.first_name ELSE users.username END) AS display_name, SUM(win) AS wins, SUM(loss) AS losses, SUM(tie) AS ties, SUM(spread) AS spread FROM " . TOTE_TABLE_POOL_RECORDS . " AS pool_records LEFT JOIN " . TOTE_TABLE_USERS . " AS users ON pool_records.user_id=users.id WHERE pool_id=? GROUP BY pool_records.user_id ORDER BY wins DESC, losses, spread DESC, display_name");
	$recordstmt->bind_param('i', $poolid);
	$recordstmt->execute();
	$recordresult = $recordstmt->get_result();

	$poolrecord = array();
	while ($record = $recordresult->fetch_assoc()) {
		$poolrecord[$record['user_id']] = $record;
	}

	$recordresult->close();
	$recordstmt->close();

	$recorddetailstmt = $mysqldb->prepare("SELECT pool_records.user_id, pool_records.week, pool_records.team_id AS pick_team_id, pick_teams.id AS pick_team_id, pick_teams.abbreviation AS pick_team_abbr, pool_records.game_id AS game_id, home_teams.abbreviation AS home_team_abbr, away_teams.abbreviation AS away_team_abbr, games.home_score AS home_score, games.away_score AS away_score, win, loss, tie, ABS(spread) AS spread, open FROM " . TOTE_TABLE_POOL_RECORDS . " AS pool_records LEFT JOIN " . TOTE_TABLE_TEAMS . " AS pick_teams ON pool_records.team_id=pick_teams.id LEFT JOIN " . TOTE_TABLE_GAMES . " AS games ON pool_records.game_id=games.id LEFT JOIN " . TOTE_TABLE_TEAMS . " AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN " . TOTE_TABLE_TEAMS . " AS away_teams ON games.away_team_id=away_teams.id WHERE pool_records.pool_id=? ORDER BY pool_records.week");
	$recorddetailstmt->bind_param('i', $poolid);
	$recorddetailstmt->execute();
	$recorddetailresult = $recorddetailstmt->get_result();

	while ($record = $recorddetailresult->fetch_assoc()) {
		$poolrecord[$record['user_id']]['picks'][] = $record;
	}

	$recorddetailresult->close();
	$recorddetailstmt->close();

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
