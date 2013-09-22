<?php

/**
 * Find all season years in the system
 *
 * @return array array of season years
 */
function get_seasons()
{
	global $db;

	$seasonsstmt = $db->query('SELECT DISTINCT year FROM ' . TOTE_TABLE_SEASONS);

	$seasons = array();

	while ($season = $seasonsstmt->fetch(PDO::FETCH_ASSOC)) {
		$seasons[] = (int)$season['year'];
	}

	$seasonsstmt = null;

	if (count($seasons) > 0)
		sort($seasons);

	return $seasons;
}
