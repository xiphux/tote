<?php

function send_reminders()
{
	global $tote_conf, $mysqldb, $tpl;

	$oldtz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	// find running season's year
	$year = (int)date('Y');
	if ((int)date('n') < 2) {
		// January is part of the previous year's season
		$year--;
	}

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
WHERE seasons.year=?
AND games.week=((
	SELECT COALESCE(MAX(games.week),0)
	FROM %s AS games
	LEFT JOIN %s AS seasons ON games.season_id=seasons.id
	WHERE seasons.year=?
	AND games.start<=UTC_TIMESTAMP()
)+1)
ORDER BY games.start
EOQ;

	$schedulequery = sprintf($schedulequery, TOTE_TABLE_GAMES, TOTE_TABLE_SEASONS, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS, TOTE_TABLE_GAMES, TOTE_TABLE_SEASONS);
	$schedulestmt = $mysqldb->prepare($schedulequery);
	$schedulestmt->bind_param('ii', $year, $year);
	$schedulestmt->execute();
	$scheduleresult = $schedulestmt->get_result();

	$schedule = array();
	$weekstart = null;
	$week = null;
	while ($game = $scheduleresult->fetch_assoc()) {
		if (empty($weekstart)) {
			$weekstart = $game['start'];
			$week = $game['week'];
		}
		$game['startstamp'] = strtotime($game['start']);
		$game['localstart'] = new DateTime('@' . $game['startstamp']);
		$schedule[] = $game;
	}
	$scheduleresult->close();
	$schedulestmt->close();

	if ((count($schedule) < 1) || !$weekstart) {
		// nothing upcoming
		date_default_timezone_set($oldtz);
		return;
	}

	// set up data needed to generate reminder email
	$tpl->assign('sitename', $tote_conf['sitename']);
	$tpl->assign('week', $week);
	$tpl->assign('year', $year);
	$subject = 'Reminder from ' . $tote_conf['sitename'] . ': Week ' . $week . ' is starting';
	$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
		'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	if (!empty($tote_conf['bccemail']))
		$headers .= "\r\nBcc: " . $tote_conf['bccemail'];

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
AND seasons.year=?
AND (
 DATE_ADD(UTC_TIMESTAMP(), INTERVAL users.reminder_time second)>?
 AND (
  users.reminder_time IS NULL
  OR (DATE_ADD(users.last_reminder, INTERVAL users.reminder_time second) < ?)
 )
)
EOQ;

	$userquery = sprintf($userquery, TOTE_TABLE_USERS, TOTE_TABLE_POOL_ENTRIES, TOTE_TABLE_POOLS, TOTE_TABLE_SEASONS);
	$userstmt = $mysqldb->prepare($userquery);
	$userstmt->bind_param('iss', $year, $weekstart, $weekstart);
	$userstmt->execute();
	$userresult = $userstmt->get_result();

	$updatestmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET last_reminder=UTC_TIMESTAMP() WHERE id=?');

	while ($user = $userresult->fetch_assoc()) {
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
		mail($user['email'], $subject, $message, $headers);

		// mark the user as messaged
		$updatestmt->bind_param('i', $user['id']);
		$updatestmt->execute();
	}
	$updatestmt->close();
	$userresult->close();
	$userstmt->close();

	date_default_timezone_set($oldtz);
}
