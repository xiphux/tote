<?php

function materialize_pool_record($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return;

	if (!is_numeric($poolid))
		return;

	$materializequery = <<<EOQ
LOCK TABLES %s WRITE, %s WRITE, %s READ;
DELETE FROM %s WHERE pool_id=%d;
INSERT INTO %s SELECT * FROM %s WHERE pool_id=%d;
UPDATE %s SET record_last_materialized=UTC_TIMESTAMP(), record_needs_materialize=0 WHERE id=%d;
UNLOCK TABLES;
EOQ;

	$materializequery = sprintf($materializequery, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOLS, TOTE_TABLE_POOL_RECORDS_VIEW, TOTE_TABLE_POOL_RECORDS, $poolid, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, $poolid, TOTE_TABLE_POOLS, $poolid);
	$mysqldb->multi_query($materializequery);
	$materializeresult = $mysqldb->store_result();
	do {
		if ($res = $mysqldb->store_result()) {
			$res->close();
		}
	} while ($mysqldb->more_results() && $mysqldb->next_result());
}
