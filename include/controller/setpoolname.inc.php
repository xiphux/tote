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
	global $mysqldb;

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
	
	$poolstmt = $mysqldb->prepare('SELECT seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE pools.id=?');
	$poolstmt->bind_param('i', $poolid);
	$poolseason = null;
	$poolstmt->bind_result($poolseason);
	$poolstmt->execute();
	$found = $poolstmt->fetch();
	$poolstmt->close();

	if (!$found) {
		// pool must exist
		display_message("Unknown pool", SETPOOLNAME_HEADER);
		return;
	}

	$dupstmt = $mysqldb->prepare('SELECT pools.id FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE seasons.year=? AND pools.name=? AND pools.id!=?');
	$dupstmt->bind_param('isi', $poolseason, $poolname, $poolid);
	$dupid = null;
	$dupstmt->bind_result($dupid);
	$dupstmt->execute();
	$founddup = $dupstmt->fetch();
	$dupstmt->close();

	if ($founddup) {
		// don't allow duplicate pool names - not for technical reasons,
		// just because it makes no sense since you can't differentiate
		display_message("There is already a pool with the name \"" . $poolname . "\" for this season", SETPOOLNAME_HEADER);
		return;
	}

	$namestmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' SET name=? WHERE id=?');
	$namestmt->bind_param('si', $poolname, $poolid);
	$namestmt->execute();
	$namestmt->close();

	// go back to edit pool page
	return redirect(array('a' => 'editpool', 'p' => $poolid));
}
