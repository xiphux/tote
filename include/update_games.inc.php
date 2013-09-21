<?php

require_once(TOTE_INCLUDEDIR . 'update_games_espn.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_games_nfl.inc.php');

function update_games()
{
	global $mysqldb;

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
		$dirtystmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id SET pools.record_needs_materialize=1 WHERE seasons.year=?');
		$dirtystmt->bind_param('i', $season);
		$dirtystmt->execute();
		$dirtystmt->close();
	} else if (count($finishedgames) > 0) {

		$updatefinishedquery = <<<EOQ
LOCK TABLES %s WRITE, %s READ;
UPDATE %s AS pool_records JOIN %s AS pool_records_view ON pool_records.game_id=pool_records_view.game_id SET pool_records.win=pool_records_view.win, pool_records.loss=pool_records_view.loss, pool_records.tie=pool_records_view.tie, pool_records.spread=pool_records_view.spread WHERE pool_records.game_id IN (%s);
UNLOCK TABLES;
EOQ;

		$updatefinishedquery = sprintf($updatefinishedquery, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, implode(', ', $finishedgames));

		$mysqldb->multi_query($updatefinishedquery);
		$updatefinishedresult = $mysqldb->store_result();
		do {
			if ($res = $mysqldb->store_result()) {
				$res->close();
			}
		} while ($mysqldb->more_results() && $mysqldb->next_result());
	
	}

}
