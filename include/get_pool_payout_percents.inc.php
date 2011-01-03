<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

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
	if (empty($poolid))
		return null;

	if (is_string($poolid))
		$poolid = new MongoId($poolid);

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(
		array('_id' => $poolid),
		array('payout', 'entries')
	);

	if (!$pool)
		return null;

	if (empty($pool['payout']))
		return null;

	$entrantcount = count($pool['entries']);

	foreach ($pool['payout'] as $payoutrule) {
		// match against payout rules based on number of entrants
		if ((!empty($payoutrule['min'])) && ($payoutrule['min'] > $entrantcount)) {
			// less than the min number of entrants for this rule, skip it
			continue;
		}
		if ((!empty($payoutrule['max'])) && ($payoutrule['max'] < $entrantcount)) {
			// more than the max number of entrants for this rule, skip it
			continue;
		}

		// passed both min and max criteria, use this rule
		if ((!empty($payoutrule['percents'])) && (count($payoutrule['percents']) > 0)) {
			$place = 1;
			$percents = array();
			foreach ($payoutrule['percents'] as $percent) {
				// remap according to place - 1st, 2nd, etc
				$percents[$place++] = $percent;
			}
			return $percents;
		}
		break;
	}

	return null;
}
