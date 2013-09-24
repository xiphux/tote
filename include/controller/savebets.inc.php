<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

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
	global $tpl, $db;

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

	$entrantstmt = $db->prepare("SELECT seasons.year AS season, pool_entries.id AS entry_id, (CASE WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name) WHEN users.first_name IS NOT NULL THEN users.first_name ELSE users.username END) AS display_name  FROM " . TOTE_TABLE_POOLS . " AS pools LEFT JOIN " . TOTE_TABLE_SEASONS . " AS seasons ON pools.season_id=seasons.id LEFT JOIN " . TOTE_TABLE_POOL_ENTRIES . " AS pool_entries ON pool_entries.pool_id=pools.id AND pool_entries.user_id=:user_id LEFT JOIN " . TOTE_TABLE_USERS . " ON users.id=pool_entries.user_id WHERE pools.id=:pool_id");
	$entrantstmt->bindParam(':user_id', $entrant, PDO::PARAM_INT);
	$entrantstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$entrantstmt->execute();
	$entrantobj = $entrantstmt->fetch(PDO::FETCH_ASSOC);
	$entrantstmt = null;;
	
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
	$picksstmt = $db->prepare('SELECT team_id, week FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks WHERE pool_entry_id=:entry_id ORDER BY week');
	$picksstmt->bindParam(':entry_id', $entrantobj['entry_id'], PDO::PARAM_INT);
	$picksstmt->execute();

	$oldpicks = array();
	while ($pick = $picksstmt->fetch(PDO::FETCH_ASSOC)) {
		$oldpicks[(int)$pick['week']] = $pick['team_id'];
	}
	$picksstmt = null;;

	$comment = trim($comment);
	$comment = !empty($comment) ? $comment : null;
	$adminid = $user['id'];
	$adminname = $user['display_name'];
	$entrantname = $entrantobj['display_name'];
	$entrantid = $entrantobj['entry_id'];

	// go through all weeks and resolve differences
	$addpickstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' (pool_entry_id, week, team_id, edited) VALUES (:entry_id, :week, :team_id, UTC_TIMESTAMP())');
	$addpickstmt->bindParam(':entry_id', $entrantid, PDO::PARAM_INT);

	$delpickstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' WHERE pool_entry_id=:entry_id AND week=:week');
	$delpickstmt->bindParam(':entry_id', $entrantid, PDO::PARAM_INT);

	$modpickstmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' SET team_id=:team_id, edited=UTC_TIMESTAMP() WHERE pool_entry_id=:entry_id AND week=:week');
	$modpickstmt->bindParam(':entry_id', $entrantid, PDO::PARAM_INT);

	$actionstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, admin_id, admin_username, week, team_id, old_team_id, comment) VALUES (:pool_id, 5, UTC_TIMESTAMP(), :user_id, :username, :admin_id, :admin_username, :week, :team_id, :old_team_id, :comment)');
	$actionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$actionstmt->bindParam(':user_id', $entrant, PDO::PARAM_INT);
	$actionstmt->bindParam(':username', $entrantname);
	$actionstmt->bindParam(':admin_id', $adminid, PDO::PARAM_INT);
	$actionstmt->bindParam(':admin_username', $adminname);
	$actionstmt->bindParam(':comment', $comment);

	$weeks = get_season_weeks($entrantobj['season']);

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

			$addpickstmt->bindParam(':week', $i, PDO::PARAM_INT);
			$addpickstmt->bindParam(':team_id', $newteam, PDO::PARAM_INT);
			$addpickstmt->execute();

			$actionstmt->bindParam(':week', $i, PDO::PARAM_INT);
			$actionstmt->bindParam(':team_id', $newteam, PDO::PARAM_INT);
			$actionstmt->bindParam(':old_team_id', $oldteam, PDO::PARAM_INT);
			$actionstmt->execute();
			$modifiedweeks[] = $db->quote($i);

		} else if (!empty($oldpicks[$i]) && empty($weekbets[$i])) {
			// deleted pick

			$oldteam = $oldpicks[$i];
			$newteam = null;

			$delpickstmt->bindParam(':week', $i);
			$delpickstmt->execute();

			$actionstmt->bindParam(':week', $i, PDO::PARAM_INT);
			$actionstmt->bindParam(':team_id', $newteam, PDO::PARAM_INT);
			$actionstmt->bindParam(':old_team_id', $oldteam, PDO::PARAM_INT);
			$actionstmt->execute();
			$modifiedweeks[] = $db->quote($i);

		} else if (!empty($oldpicks[$i]) && !empty($weekbets[$i]) && ($oldpicks[$i] != $weekbets[$i])) {
			// modified pick

			$oldteam = $oldpicks[$i];
			$newteam = $weekbets[$i];

			$modpickstmt->bindParam(':week', $i, PDO::PARAM_INT);
			$modpickstmt->bindParam(':team_id', $newteam, PDO::PARAM_INT);
			$modpickstmt->execute();

			$actionstmt->bindParam(':week', $i, PDO::PARAM_INT);
			$actionstmt->bindParam(':team_id', $newteam, PDO::PARAM_INT);
			$actionstmt->bindParam(':old_team_id', $oldteam, PDO::PARAM_INT);
			$actionstmt->execute();
			$modifiedweeks[] = $db->quote($i);

		}

	}

	$addpickstmt = null;
	$delpickstmt = null;
	$modpickstmt = null;
	$actionstmt = null;

	if (count($modifiedweeks) > 0) {
		$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE, ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' READ');
		$db->exec('SET foreign_key_checks=0');
		$db->exec('SET unique_checks=0');
		$db->exec('UPDATE ' . TOTE_TABLE_POOL_RECORDS . ' AS pool_records JOIN ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' AS pool_records_view ON pool_records.pool_id=pool_records_view.pool_id AND pool_records.user_id=pool_records_view.user_id AND pool_records.week=pool_records_view.week SET pool_records.team_id=pool_records_view.team_id, pool_records.game_id=pool_records_view.game_id, pool_records.win=pool_records_view.win, pool_records.loss=pool_records_view.loss, pool_records.tie=pool_records_view.tie, pool_records.spread=pool_records_view.spread WHERE pool_records.pool_id=' . $db->quote($poolid) . ' AND pool_records.user_id=' . $db->quote($entrant) . ' AND pool_records.week IN (' . implode(', ', $modifiedweeks) . ')');
		$db->exec('SET foreign_key_checks=1');
		$db->exec('SET unique_checks=1');
		$db->exec('UNLOCK TABLES');
	}

	// go home
	redirect();
}
