<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

/**
 * Gets the number of weeks in a season
 *
 * @param int $season season year
 * @return int number of weeks
 */
function get_season_weeks($season)
{
	if (empty($season))
		return null;

	$games = get_collection(TOTE_COLLECTION_GAMES);

	$lastgame = $games->find(
		array(
			'season' => (int)$season
		),
		array('week')
	)->sort(array('week' => -1))->getNext();

	if ($lastgame) {
		return (int)$lastgame['week'];
	}

	// default to 17 if no schedule imported
	return 17;
}
