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

	$entrantcount = 0;
	$entrantcountstmt = $mysqldb->prepare('SELECT COUNT(id) FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=?');
	$entrantcountstmt->bind_param('i', $poolid);
	$entrantcountstmt->bind_result($entrantcount);
	$entrantcountstmt->execute();
	$entrantcountstmt->fetch();
	$entrantcountstmt->close();

	$percents = array();

	$percentstmt = $mysqldb->prepare('SELECT place, percent FROM ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' WHERE payout_id=(SELECT id FROM ' . TOTE_TABLE_POOL_PAYOUTS . ' WHERE pool_id=? AND ((minimum IS NULL) OR (minimum<=?)) AND ((maximum IS NULL) OR (maximum>=?)))');
	$percentstmt->bind_param('iii', $poolid, $entrantcount, $entrantcount);
	$percentstmt->execute();
	$percentresult = $percentstmt->get_result();

	while ($place = $percentresult->fetch_assoc()) {
		$percents[(int)$place['place']] = (float)$place['percent'];
	}

	$percentresult->close();
	$percentstmt->close();

	return $percents;
}
