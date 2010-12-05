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

	$currentdate = new MongoDate(time());
	$weeks = get_season_weeks($season);
	$openweeks = array();
	for ($i = 1; $i <= $weeks; $i++) {
		$opengame = $games->findOne(
			array(
				'season' => (int)$season,
				'week' => $i,
				'start' => array(
					'$gt' => $currentdate
				)
			),
			array('week')
		);
		$openweeks[$i] = ($opengame != null);
	}

	return $openweeks;
}
