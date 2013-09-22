<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('ADDBET_HEADER', 'Make A Pick');

/**
 * addbet controller
 *
 * add a bet to the database
 *
 * @param string $poolid pool ID
 * @param string $week week number
 * @param string $team team ID
 * @param string $csrftoken CSRF request token
 */
function display_addbet($poolid, $week, $team, $csrftoken)
{
	global $tpl, $db;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", ADDBET_HEADER);
		return;
	}

	if (empty($poolid)) {
		// need to know the pool
		display_message("Pool is required", ADDBET_HEADER);
		return;
	}

	if (empty($week)) {
		// week is required
		display_message("Week is required", ADDBET_HEADER);
		return;
	}

	if (empty($team)) {
		// bet is required
		display_message("A pick is required", ADDBET_HEADER);
		return;
	}

	$poolstmt = $db->prepare('SELECT seasons.year, pool_entries.id, pool_entry_picks.team_id, teams.home, teams.team FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entries.pool_id=pools.id AND pool_entries.user_id=:user_id LEFT JOIN ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks ON pool_entries.id=pool_entry_picks.pool_entry_id AND pool_entry_picks.week=:week LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS teams ON pool_entry_picks.team_id=teams.id WHERE pools.id=:pool_id');
	$poolstmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
	$poolstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();

	$poolseason = null;
	$entryid = null;
	$prevpickid = null;
	$prevpickhome = null;
	$prevpickteam = null;
	$poolstmt->bindColumn(1, $poolseason);
	$poolstmt->bindColumn(2, $entryid);
	$poolstmt->bindColumn(3, $prevpickid);
	$poolstmt->bindColumn(4, $prevpickhome);
	$poolstmt->bindColumn(5, $prevpickteam);
	$found = $poolstmt->fetch();
	$poolstmt = null;

	if (!($found && $poolseason)) {
		// pool must exist
		display_message("Unknown pool", ADDBET_HEADER);
		return;
	}
	
	if (!$entryid) {
		// can't bet if you aren't in the pool
		display_message("You are not entered in this pool", ADDBET_HEADER);
		return;
	}

	if ($prevpickid) {
		// user already bet on this week
		display_message("You already picked the " . $prevpickhome . ' ' . $prevpickteam . " for week " . $week, ADDBET_HEADER);
		return;
	}

	$teamstmt = $db->prepare('SELECT teams.home, teams.team, games.start, games.id, pool_entry_picks.id, pool_entry_picks.week FROM ' . TOTE_TABLE_TEAMS . ' AS teams LEFT JOIN ' . TOTE_TABLE_GAMES . ' AS games ON games.week=:week AND (games.away_team_id=teams.id OR games.home_team_id=teams.id) AND games.season_id IN (SELECT id FROM ' . TOTE_TABLE_SEASONS . ' WHERE year=:year) LEFT JOIN pool_entry_picks ON pool_entry_picks.team_id=teams.id AND pool_entry_picks.pool_entry_id=:entry_id WHERE teams.id=:team_id');
	$teamstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$teamstmt->bindParam(':year', $poolseason, PDO::PARAM_INT);
	$teamstmt->bindParam(':entry_id', $entryid, PDO::PARAM_INT);
	$teamstmt->bindParam(':team_id', $team, PDO::PARAM_INT);
	$teamstmt->execute();
	$pickhome = null;
	$pickteam = null;
	$pickgamestart = null;
	$pickgameid = null;
	$prevteampickid = null;
	$prevteampickweek = null;
	$teamstmt->bindColumn(1, $pickhome);
	$teamstmt->bindColumn(2, $pickteam);
	$teamstmt->bindColumn(3, $pickgamestart);
	$teamstmt->bindColumn(4, $pickgameid);
	$teamstmt->bindColumn(5, $prevteampickid);
	$teamstmt->bindColumn(6, $prevteampickweek);
	$found = $teamstmt->fetch();
	$teamstmt = null;

	if (!($found && $pickteam)) {
		// need to bet on a valid team
		display_message("Invalid team", ADDBET_HEADER);
		return;
	}

	if (!$pickgameid) {
		// user bet on a bye team
		display_message($pickhome . ' ' . $pickteam . " aren't playing this week", ADDBET_HEADER);
		return;
	}

	if ($prevteampickid) {
		// user already bet on this team
		display_message("You already picked the " . $pickhome . ' ' . $pickteam . ' in week ' . $prevteampickweek, ADDBET_HEADER);
		return;
	}

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$starttimestamp = strtotime($pickgamestart);
	date_default_timezone_set($tz);

	if ($starttimestamp < time()) {
		// can't bet after the game has started
		display_message("This game has already started", ADDBET_HEADER);
		return;
	}

	$pickstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' (pool_entry_id, week, team_id, placed) VALUES (:entry_id, :week, :team_id, UTC_TIMESTAMP())');
	$pickstmt->bindParam(':entry_id', $entryid, PDO::PARAM_INT);
	$pickstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$pickstmt->bindParam(':team_id', $team, PDO::PARAM_INT);
	$pickstmt->execute();
	$pickstmt = null;

	$actionstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, week, team_id) VALUES (:pool_id, 4, UTC_TIMESTAMP(), :user_id, :username, :week, :team_id)');
	$actionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$actionstmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
	$actionstmt->bindParam(':username', $user['display_name']);
	$actionstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$actionstmt->bindParam(':team_id', $team, PDO::PARAM_INT);
	$actionstmt->execute();
	$actionstmt = null;

	$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE, ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' READ');
	$db->exec('UPDATE ' . TOTE_TABLE_POOL_RECORDS . ' AS pool_records JOIN ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' AS pool_records_view ON pool_records.pool_id=pool_records_view.pool_id AND pool_records.user_id=pool_records_view.user_id AND pool_records.week=pool_records_view.week SET pool_records.team_id=pool_records_view.team_id, pool_records.game_id=pool_records_view.game_id, pool_records.win=pool_records_view.win, pool_records.loss=pool_records_view.loss, pool_records.tie=pool_records_view.tie, pool_records.spread=pool_records_view.spread WHERE pool_records.pool_id=' . $poolid . ' AND pool_records.user_id=' . $user['id'] . ' AND pool_records.week=' . $week);
	$db->exec('UNLOCK TABLES');

	// go back to the pool view
	redirect(array('p' => $poolid));
}

