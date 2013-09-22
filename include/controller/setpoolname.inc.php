<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('SETPOOLNAME_HEADER', 'Manage Your Pool');

/**
 * setpoolname controller
 *
 * change the name of a pool
 *
 * @param string $poolid pool id
 * @param string $poolname new name
 * @param string $csrftoken CSRF request token
 */
function display_setpoolname($poolid, $poolname, $csrftoken)
{
	global $db;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to change the name
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", SETPOOLNAME_HEADER);
		return;
	}

	if (empty($poolid)) {
		// need to know the pool
		display_message("Pool is required", SETPOOLNAME_HEADER);
		return;
	}

	$poolname = trim($poolname);

	if (empty($poolname)) {
		// we need a name
		display_message("Pool must have a name", SETPOOLNAME_HEADER);
		return;
	}
	
	$poolstmt = $db->prepare('SELECT seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE pools.id=:pool_id');
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolseason = null;
	$poolstmt->bindColumn(1, $poolseason);
	$poolstmt->execute();
	$found = $poolstmt->fetch(PDO::FETCH_BOUND);
	$poolstmt = null;

	if (!$found) {
		// pool must exist
		display_message("Unknown pool", SETPOOLNAME_HEADER);
		return;
	}

	$dupstmt = $db->prepare('SELECT pools.id FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE seasons.year=:year AND pools.name=:poolname AND pools.id!=:pool_id');
	$dupstmt->bindParam(':year', $poolseason, PDO::PARAM_INT);
	$dupstmt->bindParam(':poolname', $poolname);
	$dupstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$dupid = null;
	$dupstmt->bindColumn(1, $dupid);
	$dupstmt->execute();
	$founddup = $dupstmt->fetch(PDO::FETCH_BOUND);
	$dupstmt = null;

	if ($founddup) {
		// don't allow duplicate pool names - not for technical reasons,
		// just because it makes no sense since you can't differentiate
		display_message("There is already a pool with the name \"" . $poolname . "\" for this season", SETPOOLNAME_HEADER);
		return;
	}

	$namestmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' SET name=:poolname WHERE id=:pool_id');
	$namestmt->bindParam(':poolname', $poolname);
	$namestmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$namestmt->execute();
	$namestmt = null;

	// go back to edit pool page
	return redirect(array('a' => 'editpool', 'p' => $poolid));
}
