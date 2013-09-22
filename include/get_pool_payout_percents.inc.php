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
	global $db;

	if (empty($poolid))
		return null;

	$percents = array();

	$percentstmt = $db->prepare('SELECT place, percent FROM ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' WHERE payout_id=(SELECT id FROM ' . TOTE_TABLE_POOL_PAYOUTS . ' WHERE pool_id=:payout_pool_id AND (SELECT COUNT(id) FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=:count_pool_id) BETWEEN COALESCE(minimum,0) AND COALESCE(maximum,65535))');
	$percentstmt->bindParam(':payout_pool_id', $poolid, PDO::PARAM_INT);
	$percentstmt->bindParam(':count_pool_id', $poolid, PDO::PARAM_INT);
	$percentstmt->execute();

	while ($place = $percentstmt->fetch(PDO::FETCH_ASSOC)) {
		$percents[(int)$place['place']] = (float)$place['percent'];
	}

	$percentstmt = null;

	return $percents;
}
