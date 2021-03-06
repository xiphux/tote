<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

/**
 * feed controller
 *
 * displays pool audit history in a variety of formats
 *
 * @param string $format format to display
 * @param string $poolid pool to display
 */
function display_feed($format, $poolid)
{
	global $tpl, $db;

	if (empty($poolid)) {
		echo "Pool is required";
		return;
	}

	$poolstmt = $db->prepare('SELECT pools.id, pools.name, seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=pools.season_id WHERE pools.id=:pool_id');
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();
	$poolobj = $poolstmt->fetch(PDO::FETCH_ASSOC);
	$poolstmt = null;

	if (!$poolobj) {
		// we need some pool
		echo "Pool not found";
		return;
	}

	$user = user_logged_in();

	$actionquery = <<<EOQ
SELECT
pool_actions.time,
pool_actions.user_id,
(CASE
 WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name)
 WHEN (users.first_name IS NOT NULL) THEN users.first_name
 WHEN (users.username IS NOT NULL) THEN users.username
 ELSE pool_actions.username
END) AS username,
users.email AS user_email,
pool_actions.admin_id,
(CASE
 WHEN (admins.first_name IS NOT NULL AND admins.last_name IS NOT NULL) THEN CONCAT(CONCAT(admins.first_name,' '),admins.last_name)
 WHEN (admins.first_name IS NOT NULL) THEN admins.first_name
 WHEN (admins.username IS NOT NULL) THEN admins.username
 ELSE pool_actions.admin_username
END) AS admin_username,
admins.email AS admin_email,
pool_actions.week,
pool_actions.action,
pool_actions.team_id,
(CONCAT(CONCAT(teams.home,' '),teams.team)) AS team_name,
teams.abbreviation AS team_abbr,
pool_actions.old_team_id,
(CONCAT(CONCAT(old_teams.home,' '),old_teams.team)) AS old_team_name,
old_teams.abbreviation AS old_team_abbr,
pool_actions.admin_type,
pool_actions.old_admin_type,
pool_actions.comment
FROM %s AS pool_actions
LEFT JOIN %s AS users
ON pool_actions.user_id=users.id
LEFT JOIN %s AS admins
ON pool_actions.admin_id=admins.id
LEFT JOIN %s AS teams
ON pool_actions.team_id=teams.id
LEFT JOIN %s AS old_teams
ON pool_actions.old_team_id=old_teams.id
WHERE pool_actions.pool_id=:pool_id %s
ORDER BY pool_actions.time DESC
EOQ;

	$actionquery = sprintf($actionquery, TOTE_TABLE_POOL_ACTIONS, TOTE_TABLE_USERS, TOTE_TABLE_USERS, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS, ((($format == 'atom') || ($format == 'rss')) ? 'AND pool_actions.time>DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 WEEK)'  : ''));
	$actionstmt = $db->prepare($actionquery);
	$actionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$actionstmt->execute();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	$actions = array();
	$updated = null;

	while ($action = $actionstmt->fetch(PDO::FETCH_ASSOC)) {
		$action['time'] = strtotime($action['time']);
		$action['timelocal'] = get_local_datetime($action['time'], (!empty($user['timezone']) ? $user['timezone'] : null));
		$action['time'] = new DateTime('@' . $action['time']);
		if (!$updated)
			$updated = $action['time'];
		$actions[] = $action;
	}

	$tz = date_default_timezone_set($tz);

	$actionstmt = null;

	// set data
	$tpl->assign('pool', $poolobj);
	if ($updated)
		$tpl->assign('updated', $updated);
	if (count($actions) > 0)
		$tpl->assign('actions', $actions);
	$tpl->assign('domain', trim($_SERVER['HTTP_HOST'], '/'));
	$tpl->assign('self', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
	
	if ($format == 'atom') {
		// display atom feed
		header('Content-type: application/atom+xml; charset=UTF-8');
		$tpl->display('atom.tpl');
	} else if ($format == 'rss') {
		// display rss feed
		header('Content-type: application/rss+xml; charset=UTF-8');
		$tpl->display('rss.tpl');
	} else if ($format == 'js') {
		http_headers();
		$tpl->assign('js', true);
		$tpl->display('history.tpl');
	} else if ($format == 'html') {
		// display history html page
		http_headers();
		$tpl->display('history.tpl');
	}

}
