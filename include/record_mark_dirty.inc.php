<?php

function record_mark_dirty($poolid)
{
	global $db;

	if (empty($poolid))
		return;

	$dirtystmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' SET record_needs_materialize=1 WHERE id=:pool_id');
	$dirtystmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$dirtystmt->execute();
	$dirtystmt = null;
}
