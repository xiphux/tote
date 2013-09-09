<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');
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
	global $tpl, $mysqldb;

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

	$poolstmt = $mysqldb->prepare('SELECT seasons.year, pool_entries.id, pool_entry_picks.team_id, teams.home, teams.team FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entries.pool_id=pools.id AND pool_entries.user_id=? LEFT JOIN ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks ON pool_entries.id=pool_entry_picks.pool_entry_id AND pool_entry_picks.week=? LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS teams ON pool_entry_picks.team_id=teams.id WHERE pools.id=?');
	$poolstmt->bind_param('iii', $user['id'], $week, $poolid);

	$poolseason = null;
	$entryid = null;
	$prevpickid = null;
	$prevpickhome = null;
	$prevpickteam = null;
	$poolstmt->bind_result($poolseason, $entryid, $prevpickid, $prevpickhome, $prevpickteam);
	$poolstmt->execute();
	$found = $poolstmt->fetch();
	$poolstmt->close();

	if (!$found) {
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

	$teamstmt = $mysqldb->prepare('SELECT teams.home, teams.team, games.start, games.id, pool_entry_picks.id, pool_entry_picks.week FROM ' . TOTE_TABLE_TEAMS . ' AS teams LEFT JOIN ' . TOTE_TABLE_GAMES . ' AS games ON games.week=? AND (games.away_team_id=teams.id OR games.home_team_id=teams.id) AND games.season_id IN (SELECT id FROM ' . TOTE_TABLE_SEASONS . ' WHERE year=?) LEFT JOIN pool_entry_picks ON pool_entry_picks.team_id=teams.id AND pool_entry_picks.pool_entry_id=? WHERE teams.id=?');
	$teamstmt->bind_param('iiii', $week, $poolseason, $entryid, $team);
	$pickhome = null;
	$pickteam = null;
	$pickgamestart = null;
	$pickgameid = null;
	$prevteampickid = null;
	$prevteampickweek = null;
	$teamstmt->bind_result($pickhome, $pickteam, $pickgamestart, $pickgameid, $prevteampickid, $prevteampickweek);
	$teamstmt->execute();
	$found = $teamstmt->fetch();
	$teamstmt->close();

	if (!$found) {
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

	$username = user_readable_name($user);

	$pickstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' (pool_entry_id, week, team_id, placed) VALUES (?, ?, ?, UTC_TIMESTAMP())');
	$pickstmt->bind_param('iii', $entryid, $week, $team);
	$pickstmt->execute();
	$pickstmt->close();

	$actionstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, week, team_id) VALUES (?, 4, UTC_TIMESTAMP(), ?, ?, ?, ?)');
	$actionstmt->bind_param('iisii', $poolid, $user['id'], $username, $week, $team);
	$actionstmt->execute();
	$actionstmt->close();

	clear_cache('pool|' . (string)$poolid);

	// go back to the pool view
	redirect(array('p' => $poolid));
}

