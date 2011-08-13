<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
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
	global $db;

	if (empty($season))
		return null;

	$weeks = get_season_weeks($season);
	$openweeks = array_fill(1, $weeks, false);

	$weekdata = $db->command(
		array(
			'distinct' => TOTE_COLLECTION_GAMES,
			'key' => 'week',
			'query' => array(
				'season' => (int)$season,
				'start' => array(
					'$gt' => new MongoDate(time())
				)
			)
		)
	);

	if (isset($weekdata['values'])) {
		for ($i = 0; $i < count($weekdata['values']); $i++) {
			if ($weekdata['values'][$i] > 0) {
				$openweeks[$weekdata['values'][$i]] = true;
			}
		}
	}

	return $openweeks;
}
