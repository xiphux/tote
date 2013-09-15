<?php

require_once(TOTE_INCLUDEDIR . 'get_pool_pot.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_payout_percents.inc.php');

/**
 * get_pool_payout_amounts
 *
 * for a pool get the payout amounts
 * 
 * @param object $poolid pool id
 * @return array array of placement payout amounts
 */
function get_pool_payout_amounts($poolid, $fee = null, $pot = null)
{
	global $mysqldb;

	if (empty($poolid))
		return null;

	if ($fee == null) {
		$feestmt = $mysqldb->prepare('SELECT fee FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?');
		$feestmt->bind_param('i', $poolid);
		$feestmt->bind_result($fee);
		$feestmt->execute();
		$found = $feestmt->fetch();
		$feestmt->close();
		if (!$found)
			return null;
	}

	$fee = (float)$fee;

	$percents = get_pool_payout_percents($poolid);
	if (count($percents) <= 0)
		return null;

	if ($pot == null) {
		$pot = get_pool_pot($poolid);
		if ($pot < 1)
			return null;
	}

	$payout = array();

	$entryfeeplace = array_search(0, $percents);
	if ($entryfeeplace !== false) {
		$payout[$entryfeeplace] = $fee;
		$pot -= $fee;
	}

	foreach ($percents as $place => $percent) {
		if (($entryfeeplace !== false) && ($place == $entryfeeplace))
			continue;
		$payout[$place] = round(($pot * $percent), 2);
	}
	ksort($payout);

	if (count($payout) > 0)
		return $payout;

	return null;
}
