<?php

function materialize_pool_record($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return;

	if (!is_numeric($poolid))
		return;

	$expirestmt = $mysqldb->prepare('SELECT MAX(start) FROM ' . TOTE_TABLE_GAMES . ' AS games WHERE season_id=(SELECT season_id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?) AND week=(SELECT MIN(week) FROM ' . TOTE_TABLE_GAMES . ' AS opengames WHERE opengames.season_id=(SELECT season_id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?) AND opengames.start>UTC_TIMESTAMP())');
	$expirestmt->bind_param('ii', $poolid, $poolid);
	$expire = null;
	$expirestmt->bind_result($expire);
	$expirestmt->execute();
	$expirestmt->fetch();
	$expirestmt->close();

	$materializequery = <<<EOQ
LOCK TABLES %s WRITE, %s WRITE, %s READ;
DELETE FROM %s WHERE pool_id=%d;
INSERT INTO %s SELECT * FROM %s WHERE pool_id=%d;
UPDATE %s SET record_last_materialized=UTC_TIMESTAMP(), record_needs_materialize=0, record_next_materialize=%s WHERE id=%d;
UNLOCK TABLES;
EOQ;

	$materializequery = sprintf($materializequery, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOLS, TOTE_TABLE_POOL_RECORDS_VIEW, TOTE_TABLE_POOL_RECORDS, $poolid, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, $poolid, TOTE_TABLE_POOLS, $expire === null ? 'NULL' : ("'" . $expire . "'"), $poolid);
	$mysqldb->multi_query($materializequery);
	$materializeresult = $mysqldb->store_result();
	do {
		if ($res = $mysqldb->store_result()) {
			$res->close();
		}
	} while ($mysqldb->more_results() && $mysqldb->next_result());
}
