<?php

require_once(TOTE_INCLUDEDIR . 'update_games_espn.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_games_nfl.inc.php');

function update_games()
{
	global $db;

	$modified = false;
	$finishedgames = array();

	if (update_games_espn($finishedgames))
		$modified = true;

	if (update_games_nfl($finishedgames))
		$modified = true;

	if ($modified) {
		$season = (int)date('Y');
		if ((int)date('n') < 3)
			$season--;

		// mark pools for this season as dirty
		$dirtystmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id SET pools.record_needs_materialize=1 WHERE seasons.year=:year');
		$dirtystmt->bindParam(':year', $season, PDO::PARAM_INT);
		$dirtystmt->execute();
		$dirtystmt = null;
	} else if (count($finishedgames) > 0) {

		for ($i = 0; $i < count($finishedgames); ++$i) {
			$finishedgames[$i] = $db->quote($finishedgames[$i]);
		}

		$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE, ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' READ');
		$db->exec('UPDATE ' . TOTE_TABLE_POOL_RECORDS . ' AS pool_records JOIN ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' AS pool_records_view ON pool_records.game_id=pool_records_view.game_id SET pool_records.win=pool_records_view.win, pool_records.loss=pool_records_view.loss, pool_records.tie=pool_records_view.tie, pool_records.spread=pool_records_view.spread WHERE pool_records.game_id IN (' . implode(', ', $finishedgames) . ')');
		$db->exec('UNLOCK TABLES');

	}

}
