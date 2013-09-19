<?php

require_once(TOTE_INCLUDEDIR . 'materialize_pool_record.inc.php');
require_once(TOTE_INCLUDEDIR . 'record_needs_materialize.inc.php');

/**
 * Gets the score record for a pool
 *
 * @param object $poolid pool id
 * @return array score data array
 */
function get_pool_record($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return null;

	if (record_needs_materialize($poolid))
		materialize_pool_record($poolid);

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

	return $poolrecord;
}
