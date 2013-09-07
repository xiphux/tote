<?php

/**
 * Gets the number of weeks in a season
 *
 * @param int $season season year
 * @return int number of weeks
 */
function get_season_weeks($season)
{
	global $mysqldb;

	if (empty($season))
		return null;

	$maxweekstmt = $mysqldb->prepare('SELECT MAX(games.week) AS weeks FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=?');

	$maxweekstmt->bind_param('i', $season);

	$weeks = null;
	$maxweekstmt->bind_result($weeks);
	$maxweekstmt->execute();
	$maxweekstmt->fetch();
	$maxweekstmt->close();

	if ($weeks > 0)
		return $weeks;

	// default to 17 if no schedule imported
	return 17;
}
