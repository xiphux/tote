<?php

/**
 * Gets the number of weeks in a season
 *
 * @param int $season season year
 * @return int number of weeks
 */
function get_season_weeks($season)
{
	global $db;

	if (empty($season))
		return null;

	$maxweekstmt = $db->prepare('SELECT MAX(games.week) AS weeks FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id WHERE seasons.year=:year');

	$maxweekstmt->bindParam(':year', $season, PDO::PARAM_INT);
	$maxweekstmt->execute();

	$weeks = null;
	$maxweekstmt->bindColumn(1, $weeks);
	$maxweekstmt->fetch(PDO::FETCH_BOUND);
	$maxweekstmt = null;

	if ($weeks > 0)
		return $weeks;

	// default to 17 if no schedule imported
	return 17;
}
