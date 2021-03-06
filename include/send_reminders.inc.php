<?php

require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');
require_once(TOTE_INCLUDEDIR . 'send_email.inc.php');

function send_reminders()
{
	global $tote_conf, $db, $tpl;

	$oldtz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	$year = get_current_season();

	// get the schedule for the next unstarted week
	$schedulequery = <<<EOQ
SELECT
games.home_team_id,
home_teams.abbreviation AS home_team_abbr,
games.away_team_id,
away_teams.abbreviation AS away_team_abbr,
games.week,
games.start
FROM %s AS games
LEFT JOIN %s AS seasons ON games.season_id=seasons.id
LEFT JOIN %s AS home_teams ON games.home_team_id=home_teams.id
LEFT JOIN %s AS away_teams ON games.away_team_id=away_teams.id
WHERE seasons.year=:year
AND games.week=((
	SELECT COALESCE(MAX(games.week),0)
	FROM %s AS games
	LEFT JOIN %s AS seasons ON games.season_id=seasons.id
	WHERE seasons.year=:weekyear
	AND games.start<=UTC_TIMESTAMP()
)+1)
ORDER BY games.start
EOQ;

	$schedulequery = sprintf($schedulequery, TOTE_TABLE_GAMES, TOTE_TABLE_SEASONS, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS, TOTE_TABLE_GAMES, TOTE_TABLE_SEASONS);
	$schedulestmt = $db->prepare($schedulequery);
	$schedulestmt->bindParam(':year', $year, PDO::PARAM_INT);
	$schedulestmt->bindParam(':weekyear', $year, PDO::PARAM_INT);
	$schedulestmt->execute();

	$schedule = array();
	$weekstart = null;
	$week = null;
	while ($game = $schedulestmt->fetch(PDO::FETCH_ASSOC)) {
		if (empty($weekstart)) {
			$weekstart = $game['start'];
			$week = $game['week'];
		}
		$game['startstamp'] = strtotime($game['start']);
		$game['localstart'] = new DateTime('@' . $game['startstamp']);
		$schedule[] = $game;
	}
	$schedulestmt = null;

	if ((count($schedule) < 1) || !$weekstart) {
		// nothing upcoming
		date_default_timezone_set($oldtz);
		return;
	}

	// set up data needed to generate reminder email
	$sitename = getenv('TOTE_SITE_NAME');
	if (empty($sitename)) {
		$sitename = $tote_conf['sitename'];
	}
	$tpl->assign('sitename', $sitename);
	$tpl->assign('week', $week);
	$tpl->assign('year', $year);
	$subject = 'Reminder from ' . $sitename . ': Week ' . $week . ' is starting';

	// get all users due for a reminder
	$userquery = <<<EOQ
SELECT
DISTINCT users.id,
users.email,
(CASE
 WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name)
 WHEN users.first_name IS NOT NULL THEN users.first_name
 ELSE users.username
END) AS user_display_name,
users.reminder,
users.reminder_time,
users.last_reminder,
users.timezone
FROM %s AS users
RIGHT JOIN %s AS pool_entries ON pool_entries.user_id=users.id
LEFT JOIN %s AS pools ON pools.id=pool_entries.pool_id
LEFT JOIN %s AS seasons ON seasons.id=pools.season_id
WHERE users.reminder=1
AND users.email IS NOT NULL
AND seasons.year=:year
AND (
 DATE_ADD(UTC_TIMESTAMP(), INTERVAL users.reminder_time second)>:week_start1
 AND (
  users.reminder_time IS NULL
  OR (DATE_ADD(users.last_reminder, INTERVAL users.reminder_time second) < :week_start2)
 )
)
EOQ;

	$userquery = sprintf($userquery, TOTE_TABLE_USERS, TOTE_TABLE_POOL_ENTRIES, TOTE_TABLE_POOLS, TOTE_TABLE_SEASONS);
	$userstmt = $db->prepare($userquery);
	$userstmt->bindParam(':year', $year, PDO::PARAM_INT);
	$userstmt->bindParam(':week_start1', $weekstart);
	$userstmt->bindParam(':week_start2', $weekstart);
	$userstmt->execute();

	$updatestmt = $db->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET last_reminder=UTC_TIMESTAMP() WHERE id=:user_id');

	while ($user = $userstmt->fetch(PDO::FETCH_ASSOC)) {
		// use the user's preferred timezone, otherwise default to Eastern
		$tz = 'America/New_York';
		if (!empty($user['timezone']))
			$tz = $user['timezone'];

		foreach ($schedule as $i => $game) {
			$schedule[$i]['localstart']->setTimezone(new DateTimeZone($tz));
		}

		// set up user-specific data for the mail template
		$tpl->clear_assign('games');
		$tpl->assign('games', $schedule);
		$tpl->clear_assign('user');
		$tpl->assign('user', $user);
		
		// send the mail
		$message = $tpl->fetch('reminderemail.tpl');
		send_email($user['email'], $subject, $message, true);

		// mark the user as messaged
		$updatestmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
		$updatestmt->execute();
	}
	$updatestmt = null;
	$userstmt = null;

	date_default_timezone_set($oldtz);
}
