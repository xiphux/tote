<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Find all season years in the system
 *
 * @return array array of season years
 */
function get_seasons()
{
	global $db;

	$seasondata = $db->command(
		array(
			'distinct' => TOTE_COLLECTION_GAMES,
			'key' => 'season'
		)
	);

	$seasons = array();

	if (isset($seasondata['values'])) {
		$seasons = $seasondata['values'];
		sort($seasons);
	}

	return $seasons;
}
