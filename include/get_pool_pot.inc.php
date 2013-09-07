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
	global $mysqldb;

	if (empty($poolid))
		return null;

	$pot = null;
	$potstmt = $mysqldb->prepare('SELECT COUNT(pool_entries.id)*pools.fee AS pot FROM ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries LEFT JOIN ' . TOTE_TABLE_POOLS . ' AS pools ON pool_entries.pool_id=pools.id WHERE pool_entries.pool_id=?');
	$potstmt->bind_param('i', $poolid);
	$potstmt->bind_result($pot);
	$potstmt->execute();
	$found = $potstmt->fetch();
	$potstmt->close();

	if ($found)
		return $pot;

	return null;
}
