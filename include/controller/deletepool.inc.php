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
	global $tpl, $db;

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

	$removeactionstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ACTIONS . ' WHERE pool_id=:pool_id');
	$removeactionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removeactionstmt->execute();
	$removeactionstmt = null;

	$removeadminstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' WHERE pool_id=:pool_id');
	$removeadminstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removeadminstmt->execute();
	$removeadminstmt = null;

	$removepickstmt = $db->prepare('DELETE pool_entry_picks FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entry_picks.pool_entry_id=pool_entries.id WHERE pool_entries.pool_id=:pool_id');
	$removepickstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removepickstmt->execute();
	$removepickstmt = null;

	$removeentrystmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=:pool_id');
	$removeentrystmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removeentrystmt->execute();
	$removeentrystmt = null;

	$removepercentstmt = $db->prepare('DELETE pool_payout_percents FROM ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' AS pool_payout_percents LEFT JOIN ' . TOTE_TABLE_POOL_PAYOUTS . ' AS pool_payouts ON pool_payout_percents.payout_id=pool_payouts.id WHERE pool_payouts.pool_id=:pool_id');
	$removepercentstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removepercentstmt->execute();
	$removepercentstmt = null;

	$removepayoutstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_PAYOUTS . ' WHERE pool_id=:pool_id');
	$removepayoutstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removepayoutstmt->execute();
	$removepayoutstmt = null;

	$removerecordstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_RECORDS . ' WHERE pool_id=:pool_id');
	$removerecordstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removerecordstmt->execute();
	$removerecordstmt = null;

	$removepoolstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOLS . ' WHERE id=:pool_id');
	$removepoolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$removepoolstmt->execute();
	$removepoolstmt = null;

	redirect();
}
