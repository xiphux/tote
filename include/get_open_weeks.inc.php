<?php

/**
 * For a season figure out which weeks are still
 * open for betting
 *
 * @param int $season season year
 * @return array array of weeks: index is number, value is true if open
 */
function get_open_weeks($season)
{
	global $mysqldb;

	if (empty($season))
		return null;

	$weekstmt = $mysqldb->prepare('SELECT games.week, MAX(games.start>UTC_TIMESTAMP()) FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=? GROUP BY games.week ORDER BY games.week');
	$weekstmt->bind_param('i', $season);

	$week = null;
	$open = null;

	$weekstmt->bind_result($week, $open);
	$weekstmt->execute();

	$openweeks = array();
	while ($weekstmt->fetch()) {
		$openweeks[(int)$week] = ($open == 1);
	}
	$weekstmt->close();

	return $openweeks;
}
