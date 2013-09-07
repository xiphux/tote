<?php

/**
 * Find all season years in the system
 *
 * @return array array of season years
 */
function get_seasons()
{
	global $mysqldb;

	$seasonsresult = $mysqldb->query('SELECT DISTINCT year FROM ' . TOTE_TABLE_SEASONS);

	$seasons = array();

	while ($season = $seasonsresult->fetch_assoc()) {
		$seasons[] = (int)$season['year'];
	}

	$seasonsresult->close();

	if (count($seasons) > 0)
		sort($seasons);

	return $seasons;
}
