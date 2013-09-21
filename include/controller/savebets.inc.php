<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

/**
 * Sort bets by week
 *
 * @param array $a first sort item
 * @param array $b second sort item
 */
function sort_bets($a, $b)
{
	return ($a['week'] > $b['week'] ? 1 : -1);
}

define('SAVEBETS_HEADER', "Edit A User's Bets");

/**
 * savebets controller
 *
 * after editing a user's bets, save the changes into the database
 *
 * @param string $poolid pool id
 * @param string $entrant entrant user id
 * @param string $weekbets array of bets for the week
 * @param string $comment edit comment
 * @param string $csrftoken CSRF request token
 */
function display_savebets($poolid, $entrant, $weekbets, $comment, $csrftoken)
{
	global $tpl, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to save bets
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", SAVEBETS_HEADER);
		return;
	}

	if (empty($poolid)) {
		// need the pool
		display_message("Pool is required", SAVEBETS_HEADER);
		return;
	}

	if (empty($entrant)) {
		// need the pool
		display_message("Entrant is required", SAVEBETS_HEADER);
		return;
	}

	$entrantstmt = $mysqldb->prepare("SELECT seasons.year AS season, pool_entries.id AS entry_id, (CASE WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name) WHEN users.first_name IS NOT NULL THEN users.first_name ELSE users.username END) AS display_name  FROM " . TOTE_TABLE_POOLS . " AS pools LEFT JOIN " . TOTE_TABLE_SEASONS . " AS seasons ON pools.season_id=seasons.id LEFT JOIN " . TOTE_TABLE_POOL_ENTRIES . " AS pool_entries ON pool_entries.pool_id=pools.id AND pool_entries.user_id=? LEFT JOIN " . TOTE_TABLE_USERS . " ON users.id=pool_entries.user_id WHERE pools.id=?");
	$entrantstmt->bind_param('ii', $entrant, $poolid);
	$entrantstmt->execute();
	$entrantresult = $entrantstmt->get_result();
	$entrantobj = $entrantresult->fetch_assoc();
	$entrantresult->close();
	$entrantstmt->close();
	
	if (!$entrantobj) {
		// must be a valid pool
		display_message("Unknown pool", SAVEBETS_HEADER);
		return;
	}

	if (empty($entrantobj['entry_id'])) {
		// user needs to be in the pool
		display_message("Entrant not in pool", SAVEBETS_HEADER);
		return;
	}

	// get all user picks
	$picksstmt = $mysqldb->prepare('SELECT team_id, week FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks WHERE pool_entry_id=? ORDER BY week');
	$picksstmt->bind_param('i', $entrantobj['entry_id']);
	$picksstmt->execute();
	$picksresult = $picksstmt->get_result();

	$oldpicks = array();
	while ($pick = $picksresult->fetch_assoc()) {
		$oldpicks[(int)$pick['week']] = $pick['team_id'];
	}
	$picksresult->close();
	$picksstmt->close();

	// go through all weeks and resolve differences
	$addpickstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' (pool_entry_id, week, team_id, edited) VALUES (?, ?, ?, UTC_TIMESTAMP())');
	$delpickstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' WHERE pool_entry_id=? AND week=?');
	$modpickstmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' SET team_id=?, edited=UTC_TIMESTAMP() WHERE pool_entry_id=? AND week=?');
	$actionstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, admin_id, admin_username, week, team_id, old_team_id, comment) VALUES (?, 5, UTC_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?, ?)');

	$weeks = get_season_weeks($entrantobj['season']);

	$comment = trim($comment);
	$comment = !empty($comment) ? $comment : null;
	$adminid = $user['id'];
	$adminname = $user['display_name'];
	$entrantname = $entrantobj['display_name'];
	$entrantid = $entrantobj['entry_id'];

	$modifiedweeks = array();

	for ($i = 1; $i <= $weeks; ++$i) {

		if (empty($oldpicks[$i]) && empty($weekbets[$i])) {
			// no pick and no change
			continue;
		}

		if (empty($oldpicks[$i]) && !empty($weekbets[$i])) {
			// newly added pick

			$oldteam = null;
			$newteam = $weekbets[$i];

			$addpickstmt->bind_param('iii', $entrantid, $i, $newteam);
			$addpickstmt->execute();

			$actionstmt->bind_param('iisisiiis', $poolid, $entrant, $entrantname, $adminid, $adminname, $i, $newteam, $oldteam, $comment);
			$actionstmt->execute();
			$modifiedweeks[] = $i;

		} else if (!empty($oldpicks[$i]) && empty($weekbets[$i])) {
			// deleted pick

			$oldteam = $oldpicks[$i];
			$newteam = null;

			$delpickstmt->bind_param('ii', $entrantid, $i);
			$delpickstmt->execute();

			$actionstmt->bind_param('iisisiiis', $poolid, $entrant, $entrantname, $adminid, $adminname, $i, $newteam, $oldteam, $comment);
			$actionstmt->execute();
			$modifiedweeks[] = $i;

		} else if (!empty($oldpicks[$i]) && !empty($weekbets[$i]) && ($oldpicks[$i] != $weekbets[$i])) {
			// modified pick

			$oldteam = $oldpicks[$i];
			$newteam = $weekbets[$i];

			$modpickstmt->bind_param('iii', $newteam, $entrantid, $i);
			$modpickstmt->execute();

			$actionstmt->bind_param('iisisiiis', $poolid, $entrant, $entrantname, $adminid, $adminname, $i, $newteam, $oldteam, $comment);
			$actionstmt->execute();
			$modifiedweeks[] = $i;

		}

	}

	$addpickstmt->close();
	$delpickstmt->close();
	$modpickstmt->close();
	$actionstmt->close();

	if (count($modifiedweeks) > 0) {
		$updaterecordquery = <<<EOQ
LOCK TABLES %s WRITE, %s READ;
UPDATE %s AS pool_records JOIN %s AS pool_records_view ON pool_records.pool_id=pool_records_view.pool_id AND pool_records.user_id=pool_records_view.user_id AND pool_records.week=pool_records_view.week SET pool_records.team_id=pool_records_view.team_id, pool_records.game_id=pool_records_view.game_id, pool_records.win=pool_records_view.win, pool_records.loss=pool_records_view.loss, pool_records.tie=pool_records_view.tie, pool_records.spread=pool_records_view.spread WHERE pool_records.pool_id=%d AND pool_records.user_id=%d AND pool_records.week IN (%s);
UNLOCK TABLES;
EOQ;
		$updaterecordquery = sprintf($updaterecordquery, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, TOTE_TABLE_POOL_RECORDS, TOTE_TABLE_POOL_RECORDS_VIEW, $poolid, $entrant, implode(', ', $modifiedweeks));
		$mysqldb->multi_query($updaterecordquery);
		$updaterecordresult = $mysqldb->store_result();
		do {
			if ($res = $mysqldb->store_result()) {
				$res->close();
			}
		} while ($mysqldb->more_results() && $mysqldb->next_result());
	}

	// go home
	redirect();
}
