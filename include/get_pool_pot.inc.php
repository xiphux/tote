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
	global $db;

	if (empty($poolid))
		return null;

	$pot = null;
	$potstmt = $db->prepare('SELECT COUNT(pool_entries.id)*pools.fee AS pot FROM ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries LEFT JOIN ' . TOTE_TABLE_POOLS . ' AS pools ON pool_entries.pool_id=pools.id WHERE pool_entries.pool_id=:pool_id');
	$potstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$potstmt->execute();

	$potstmt->bindColumn(1, $pot);
	$found = $potstmt->fetch(PDO::FETCH_BOUND);
	$potstmt = null;

	if ($found)
		return (float)$pot;

	return null;
}
