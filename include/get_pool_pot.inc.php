<?php

/**
 * get_pool_pot
 *
 * for a pool get the pot
 *
 * @param object $poolid pool id
 * @return int pool pot
 */
function get_pool_pot($poolid)
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

	if (empty($pool['fee']) || ($pool['fee'] <= 0))
		return null;

	$entrantcount = count($pool['entries']);
	return $entrantcount * $pool['fee'];
}
