<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

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
	global $tpl, $mysqldb;

	if (empty($poolid)) {
		echo "Pool is required";
		return;
	}

	$poolstmt = $mysqldb->prepare('SELECT pools.id, pools.name, seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=pools.season_id WHERE pools.id=?');
	$poolstmt->bind_param('i', $poolid);
	$poolstmt->execute();
	$poolresult = $poolstmt->get_result();
	$poolobj = $poolresult->fetch_assoc();
	$poolresult->close();
	$poolstmt->close();

	if (!$poolobj) {
		// we need some pool
		echo "Pool not found";
		return;
	}

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
WHERE pool_actions.pool_id=?
ORDER BY pool_actions.time DESC
EOQ;

	$actionquery = sprintf($actionquery, TOTE_TABLE_POOL_ACTIONS, TOTE_TABLE_USERS, TOTE_TABLE_USERS, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS);
	$actionstmt = $mysqldb->prepare($actionquery);
	$actionstmt->bind_param('i', $poolid);
	$actionstmt->execute();
	$actionresult = $actionstmt->get_result();

	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	$actions = array();
	$updated = null;

	while ($action = $actionresult->fetch_assoc()) {
		$action['time'] = strtotime($action['time']);
		$action['timelocal'] = get_local_datetime($action['time']);
		$action['time'] = new DateTime('@' . $action['time']);
		if (!$updated)
			$updated = $action['time'];
		$actions[] = $action;
	}

	$tz = date_default_timezone_set($tz);

	$actionresult->close();
	$actionstmt->close();

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
