<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('EDITBETS_HEADER', "Edit A User's Bets");

/**
 * editbets controller
 *
 * edit all of a user's bets
 *
 * @param string $poolid pool id
 * @param string $entrant entrant id
 */
function display_editbets($poolid, $entrant)
{
	global $tpl, $db;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to edit bets
		return redirect();
	}

	if (empty($poolid)) {
		// need to know the pool
		display_message("Pool is required", EDITBETS_HEADER);
		return;
	}

	if (empty($entrant)) {
		// need to know the user
		display_message("User is required", EDITBETS_HEADER);
		return;
	}

	$entrantstmt = $db->prepare("SELECT id, (CASE WHEN (first_name IS NOT NULL AND last_name IS NOT NULL) THEN CONCAT(CONCAT(first_name,' '),last_name) WHEN first_name IS NOT NULL THEN first_name ELSE username END) AS display_name FROM " . TOTE_TABLE_USERS . " WHERE id=:entrant_id");
	$entrantstmt->bindParam(':entrant_id', $entrant, PDO::PARAM_INT);
	$entrantstmt->execute();
	$entrantobj = $entrantstmt->fetch(PDO::FETCH_ASSOC);
	$entrantstmt = null;

	if (!$entrantobj) {
		display_message('Invalid user', EDITBETS_HEADER);
		return;
	}

	$poolstmt = $db->prepare('SELECT seasons.year AS season, pools.name, pools.id, pool_entries.id AS entry_id FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entries.pool_id=pools.id AND pool_entries.user_id=:user_id WHERE pools.id=:pool_id');
	$poolstmt->bindParam(':user_id', $entrant, PDO::PARAM_INT);
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();
	$pool = $poolstmt->fetch(PDO::FETCH_ASSOC);
	$poolstmt = null;

	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", EDITBETS_HEADER);
		return;
	}

	if (empty($pool['entry_id'])) {
		// entrant being edited needs to be in the pool
		display_message("User not in pool", EDITBETS_HEADER);
		return;
	}

	// make a list of the user's bets
	$userbets = array();
	$picksstmt = $db->prepare('SELECT week, team_id FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' WHERE pool_entry_id=:entry_id ORDER BY week');
	$picksstmt->bindParam(':entry_id', $pool['entry_id'], PDO::PARAM_INT);
	$picksstmt->execute();

	while ($pick = $picksstmt->fetch(PDO::FETCH_ASSOC)) {
		$userbets[(int)$pick['week']] = $pick['team_id'];
	}

	$picksstmt = null;

	// for any weeks user hasn't bet on, set a placeholder
	// so we can provide the option to add a bet there
	$weeks = get_season_weeks($pool['season']);
	for ($i = 1; $i <= $weeks; $i++) {
		if (!isset($userbets[$i])) {
			$userbets[$i] = '';
		}
	}
	ksort($userbets);

	// make a list of all teams available
	$teamsstmt = $db->query('SELECT id, home, team FROM ' . TOTE_TABLE_TEAMS . ' ORDER BY home, team');
	$allteams = array();
	while ($team = $teamsstmt->fetch(PDO::FETCH_ASSOC)) {
		$allteams[] = $team;
	}
	$teamsstmt = null;

	// provide data and display
	http_headers();
	$tpl->assign('pool', $pool);
	$tpl->assign('entrant', $entrantobj);
	$tpl->assign('teams', $allteams);
	$tpl->assign('bets', $userbets);
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);

	$tpl->display('editbets.tpl');
}
