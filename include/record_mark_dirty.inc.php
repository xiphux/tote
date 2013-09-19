<?php

function record_mark_dirty($poolid)
{
	global $mysqldb;

	if (empty($poolid))
		return;

	$dirtystmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' SET record_needs_materialize=1 WHERE id=?');
	$dirtystmt->bind_param('i', $poolid);
	$dirtystmt->execute();
	$dirtystmt->close();
}
