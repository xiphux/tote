<?php

function record_needs_materialize($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return;

	$needstmt = $mysqldb->prepare('SELECT (record_last_materialized IS NULL OR record_needs_materialize=1 OR (record_next_materialize IS NOT NULL AND record_last_materialized<record_next_materialize AND UTC_TIMESTAMP()>record_next_materialize)) AS materialize FROM ' . TOTE_TABLE_POOLS . ' AS pools WHERE id=?');
	$needstmt->bind_param('i', $poolid);
	$materialize = null;
	$needstmt->bind_result($materialize);
	$needstmt->execute();
	$found = $needstmt->fetch();
	$needstmt->close();

	if (!$found)
		return false;

	return $materialize == 1;
}
