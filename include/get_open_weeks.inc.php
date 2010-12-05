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
	if (empty($season))
		return null;

	$games = GET_COLLECTION(TOTE_COLLECTION_GAMES);

	$weeks = get_season_weeks($season);
	$openweeks = array_fill(1, $weeks, false);

	$opengames = $games->find(
		array(
			'season' => (int)$season,
			'start' => array(
				'$gt' => new MongoDate(time())
			)
		),
		array('week')
	);

	foreach ($opengames as $game) {
		if ($game['week'] > 0) {
			$openweeks[$game['week']] = true;
		}
	}

	return $openweeks;
}
