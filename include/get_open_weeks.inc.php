<?php

require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');

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

	$weeks = get_season_weeks($season);
	$openweeks = array_fill(1, $weeks, false);

	$weeksstmt = $mysqldb->prepare('SELECT DISTINCT week FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=? AND games.start>UTC_TIMESTAMP()');
	$weeksstmt->bind_param('i', $season);
	$weeksstmt->execute();
	$weeksresult = $weeksstmt->get_result();

	while ($week = $weeksresult->fetch_assoc()) {
		$openweeks[(int)$week['week']] = true;
	}

	$weeksresult->close();
	$weeksstmt->close();

	return $openweeks;
}
