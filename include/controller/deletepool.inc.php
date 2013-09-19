<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('DELETEPOOL_HEADER', 'Manage Your Pool');

/**
 * deletepool controller
 *
 * deletes a pool from the database
 *
 * @param string $poolid pool id to delete
 * @param string $csrftoken CSRF request token
 */
function display_deletepool($poolid, $csrftoken)
{
	global $tpl, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to delete a pool
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", DELETEPOOL_HEADER);
		return;
	}

	if (empty($poolid)) {
		// need to know which pool to delete
		display_message("Pool to delete is required", DELETEPOOL_HEADER);
		return;
	}

	$removeactionstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ACTIONS . ' WHERE pool_id=?');
	$removeactionstmt->bind_param('i', $poolid);
	$removeactionstmt->execute();
	$removeactionstmt->close();

	$removeadminstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' WHERE pool_id=?');
	$removeadminstmt->bind_param('i', $poolid);
	$removeadminstmt->execute();
	$removeadminstmt->close();

	$removepickstmt = $mysqldb->prepare('DELETE pool_entry_picks FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entry_picks.pool_entry_id=pool_entries.id WHERE pool_entries.pool_id=?');
	$removepickstmt->bind_param('i', $poolid);
	$removepickstmt->execute();
	$removepickstmt->close();

	$removeentrystmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=?');
	$removeentrystmt->bind_param('i', $poolid);
	$removeentrystmt->execute();
	$removeentrystmt->close();

	$removepercentstmt = $mysqldb->prepare('DELETE pool_payout_percents FROM ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' AS pool_payout_percents LEFT JOIN ' . TOTE_TABLE_POOL_PAYOUTS . ' AS pool_payouts ON pool_payout_percents.payout_id=pool_payouts.id WHERE pool_payouts.pool_id=?');
	$removepercentstmt->bind_param('i', $poolid);
	$removepercentstmt->execute();
	$removepercentstmt->close();

	$removepayoutstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_PAYOUTS . ' WHERE pool_id=?');
	$removepayoutstmt->bind_param('i', $poolid);
	$removepayoutstmt->execute();
	$removepayoutstmt->close();

	$removerecordstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_RECORDS . ' WHERE pool_id=?');
	$removerecordstmt->bind_param('i', $poolid);
	$removerecordstmt->execute();
	$removerecordstmt->close();

	$removepoolstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?');
	$removepoolstmt->bind_param('i', $poolid);
	$removepoolstmt->execute();
	$removepoolstmt->close();

	redirect();
}
