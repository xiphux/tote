<?php

/**
 * Tests if user is entered in a pool
 *
 * @param object $user user id
 * @param object $pool pool id
 * @return true if user is in pool
 */
function user_in_pool($user, $pool)
{
	global $mysqldb;

	if (empty($user) || empty($pool))
		return false;

	$entryid = null;
	$userpoolstmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=? AND user_id=?');
	$userpoolstmt->bind_param('ii', $user, $pool);
	$userpoolstmt->bind_result($entryid);
	$found = $userpoolstmt->fetch();

	$userpoolstmt->close();

	if ($found && !empty($entryid))
		return true;

	return false;
}
