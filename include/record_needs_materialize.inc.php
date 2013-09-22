<?php

function record_needs_materialize($poolid)
{
	global $db;

	if (empty($poolid))
		return;

	$needstmt = $db->prepare('SELECT (record_last_materialized IS NULL OR record_needs_materialize=1 OR (record_next_materialize IS NOT NULL AND record_last_materialized<record_next_materialize AND UTC_TIMESTAMP()>record_next_materialize)) AS materialize FROM ' . TOTE_TABLE_POOLS . ' AS pools WHERE id=:pool_id');
	$needstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$materialize = null;
	$needstmt->execute();
	$needstmt->bindColumn(1, $materialize);
	$found = $needstmt->fetch(PDO::FETCH_BOUND);
	$needstmt = null;

	if (!$found || ($materialize === null))
		return false;

	return $materialize == 1;
}
