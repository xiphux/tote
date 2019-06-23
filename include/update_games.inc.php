<?php

require_once(TOTE_INCLUDEDIR . 'update_games_nfl.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');

function update_games()
{
	global $db;

	$modified = false;

	$season = get_current_season();

	if (update_games_nfl($season))
		$modified = true;

	if ($modified) {

		// mark pools for this season as dirty
		$dirtystmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id SET pools.record_needs_materialize=1 WHERE seasons.year=:year');
		$dirtystmt->bindParam(':year', $season, PDO::PARAM_INT);
		$dirtystmt->execute();
		$dirtystmt = null;
	}

}
