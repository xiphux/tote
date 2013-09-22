<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');

define('BET_HEADER', 'Make A Pick');

/**
 * bet controller
 *
 * displays betting page for users
 *
 * @param string $poolid pool id
 * @param string $week week
 */
function display_bet($poolid, $week)
{
	global $tpl, $db;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (empty($poolid)) {
		// need to know the pool
		display_message("Pool is required", BET_HEADER);
		return;
	}

	$poolstmt = $db->prepare('SELECT seasons.year AS season, pools.name, pools.id FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE pools.id=:pool_id');
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();
	$pool = $poolstmt->fetch(PDO::FETCH_ASSOC);
	$poolstmt = null;
	
	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", BET_HEADER);
		return;
	}

	if (empty($week)) {
		// week is required
		display_message("Week is required", BET_HEADER);
		return;
	}

	$openweeks = get_open_weeks($pool['season']);
	if (!$openweeks[$week]) {
		display_message("Week " . $week . " is closed for picks", BET_HEADER);
		return;
	}

	// find the user's entry in the pool
	$entryid = null;
	$pickid = null;
	$pickhome = null;
	$pickteam = null;
	$entrystmt = $db->prepare('SELECT pool_entries.id, pool_entry_picks.team_id, teams.home, teams.team FROM ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries LEFT JOIN ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks ON pool_entries.id=pool_entry_picks.pool_entry_id AND pool_entry_picks.week=:week LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS teams ON teams.id=pool_entry_picks.team_id WHERE pool_entries.pool_id=:pool_id AND pool_entries.user_id=:user_id');
	$entrystmt->bindParam(':week', $week, PDO::PARAM_INT);
	$entrystmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$entrystmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
	$entrystmt->execute();
	$entrystmt->bindColumn(1, $entryid);
	$entrystmt->bindColumn(2, $pickid);
	$entrystmt->bindColumn(3, $pickhome);
	$entrystmt->bindColumn(4, $pickteam);
	$found = $entrystmt->fetch(PDO::FETCH_BOUND);
	$entrystmt = null;

	if (!($found && $entryid)) {
		// can't bet if you aren't in the pool
		display_message("You are not entered in this pool", BET_HEADER);
		return;
	}

	if (!empty($pickid)) {
		display_message('You already picked the ' . $pickhome . ' ' . $pickteam . ' for week ' . $week, BET_HEADER);
		return;
	}

	// find all games for this week in chronological order
	$gamesstmt = $db->prepare('SELECT games.id, games.home_team_id, home_teams.abbreviation AS home_team_abbr, games.away_team_id, away_teams.abbreviation AS away_team_abbr, games.start, games.home_score, games.away_score FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=:year AND games.week=:week ORDER BY games.start');
	$gamesstmt->bindParam(':year', $pool['season'], PDO::PARAM_INT);
	$gamesstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$gamesstmt->execute();

	$weekgames = array();
	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');
	while ($game = $gamesstmt->fetch(PDO::FETCH_ASSOC)) {

		$game['start'] = strtotime($game['start']);
		$game['localstart'] = get_local_datetime($game['start'], (!empty($user['timezone']) ? $user['timezone'] : null));
		$weekgames[] = $game;

	}
	date_default_timezone_set($tz);

	$gamesstmt = null;

	// find all teams the user can pick
	$availableteams = array();

	$teamsquery = <<<EOQ
SELECT teams.id, teams.home, teams.team
FROM %s AS teams
CROSS JOIN %s AS games
ON (games.home_team_id=teams.id OR games.away_team_id=teams.id)
LEFT JOIN %s AS seasons
ON games.season_id=seasons.id
WHERE seasons.year=:year
AND games.week=:week
AND games.start>UTC_TIMESTAMP()
AND teams.id NOT IN
(
SELECT team_id
FROM %s AS pool_entry_picks
LEFT JOIN %s AS pool_entries
ON pool_entry_picks.pool_entry_id=pool_entries.id
WHERE pool_entries.pool_id=:pool_id
AND pool_entries.user_id=:user_id
)
ORDER BY home, team
EOQ;
	$teamsquery = sprintf($teamsquery, TOTE_TABLE_TEAMS, TOTE_TABLE_GAMES, TOTE_TABLE_SEASONS, TOTE_TABLE_POOL_ENTRY_PICKS, TOTE_TABLE_POOL_ENTRIES);
	$teamsstmt = $db->prepare($teamsquery);
	$teamsstmt->bindParam(':year', $pool['season'], PDO::PARAM_INT);
	$teamsstmt->bindParam(':week', $week, PDO::PARAM_INT);
	$teamsstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$teamsstmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
	$teamsstmt->execute();

	while ($team = $teamsstmt->fetch(PDO::FETCH_ASSOC)) {
		$availableteams[$team['id']] = $team['home'] . ' ' . $team['team'];
	}

	$teamsstmt = null;

	// find teams the user's picked already
	$picksstmt = $db->prepare('SELECT pool_entry_picks.week, teams.abbreviation FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' AS pool_entry_picks LEFT JOIN ' . TOTE_TABLE_POOL_ENTRIES . ' AS pool_entries ON pool_entry_picks.pool_entry_id=pool_entries.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS teams ON pool_entry_picks.team_id=teams.id WHERE pool_entries.pool_id=:pool_id AND pool_entries.user_id=:user_id ORDER BY pool_entry_picks.week');
	$picksstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$picksstmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
	$picksstmt->execute();
	$picks = array();
	while ($pick = $picksstmt->fetch(PDO::FETCH_ASSOC)) {
		$picks[] = $pick;
	}
	$picksstmt = null;

	// set data for display
	http_headers();
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('teams', $availableteams);
	$tpl->assign('week', $week);
	if (count($picks) > 0) {
		$tpl->assign('bets', $picks);
	}
	$tpl->assign('games', $weekgames);
	$tpl->assign('pool', $pool);
	$tpl->display('bet.tpl');
}

