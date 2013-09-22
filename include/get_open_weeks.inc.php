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
	global $db;

	if (empty($season))
		return null;

	$weekstmt = $db->prepare('SELECT games.week, MAX(games.start>UTC_TIMESTAMP()) FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=:year GROUP BY games.week ORDER BY games.week');
	$weekstmt->bindParam(':year', $season, PDO::PARAM_INT);
	$weekstmt->execute();

	$week = null;
	$open = null;

	$weekstmt->bindColumn(1, $week);
	$weekstmt->bindColumn(2, $open);

	$openweeks = array();
	while ($weekstmt->fetch(PDO::FETCH_BOUND)) {
		$openweeks[(int)$week] = ($open == 1);
	}
	$weekstmt = null;

	return $openweeks;
}
