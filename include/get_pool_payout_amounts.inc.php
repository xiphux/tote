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
function get_pool_payout_amounts($poolid)
{
	if (empty($poolid))
		return null;

	if (is_string($poolid))
		$poolid = new MongoId($poolid);

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(
		array('_id' => $poolid),
		array('fee', 'entries')
	);

	if (!$pool)
		return null;

	$percents = get_pool_payout_percents($poolid);
	if (count($percents) <= 0)
		return null;

	$pot = get_pool_pot($poolid);
	if ($pot < 1)
		return null;

	$payout = array();

	$entryfeeplace = array_search(0, $percents);
	if ($entryfeeplace !== false) {
		$payout[$entryfeeplace] = $pool['fee'];
		$pot -= $pool['fee'];
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
