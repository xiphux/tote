<?php

/**
 * get_pool_payout_percents
 *
 * for a pool get the appropriate payout percents to use
 * 
 * @param object $poolid pool id
 * @return array array of placement payout percents
 */
function get_pool_payout_percents($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return null;

	$percents = array();

	$percentstmt = $mysqldb->prepare('SELECT place, percent FROM ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' WHERE payout_id=(SELECT id FROM ' . TOTE_TABLE_POOL_PAYOUTS . ' WHERE pool_id=? AND (SELECT COUNT(id) FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=?) BETWEEN COALESCE(minimum,0) AND COALESCE(maximum,65535))');
	$percentstmt->bind_param('ii', $poolid, $poolid);
	$percentstmt->execute();
	$percentresult = $percentstmt->get_result();

	while ($place = $percentresult->fetch_assoc()) {
		$percents[(int)$place['place']] = (float)$place['percent'];
	}

	$percentresult->close();
	$percentstmt->close();

	return $percents;
}
