<?php

require_once(TOTE_INCLUDEDIR . 'send_email.inc.php');

/**
 * For users that have bet on this game and have notifications
 * turned on, let them know
 *
 * @param int $season season
 * @param int $week week
 * @param object $hometeam home team id
 * @param int $homescore home team score
 * @param int $awayteam away team id
 * @param int $awayscore away team score
 */
function notify_finished_game($season, $week, $hometeam, $homescore, $awayteam, $awayscore)
{
	global $tpl, $tote_conf, $db;

	$sitename = getenv('TOTE_SITE_NAME');
	if (empty($sitename)) {
		$sitename = $tote_conf['sitename'];
	}

	$tpl->assign('week', $week);
	$tpl->assign('homescore', $homescore);
	$tpl->assign('awayscore', $awayscore);

	$notifyquery = <<<EOQ
SELECT
pool_entry_picks.team_id AS pick_id,
(CONCAT(CONCAT(pick_teams.home,' '),pick_teams.team)) AS pick_team,
away_teams.abbreviation AS away_team_abbr,
home_teams.abbreviation AS home_team_abbr,
users.email,
pools.name AS pool_name,
seasons.year AS season
FROM %s AS pool_entry_picks
LEFT JOIN %s AS pool_entries ON pool_entry_picks.pool_entry_id=pool_entries.id
LEFT JOIN %s AS pools ON pool_entries.pool_id=pools.id
LEFT JOIN %s AS seasons ON pools.season_id=seasons.id
LEFT JOIN %s AS users ON pool_entries.user_id=users.id
LEFT JOIN %s AS games ON games.season_id=pools.season_id AND games.week=pool_entry_picks.week AND (games.away_team_id=pool_entry_picks.team_id OR games.home_team_id=pool_entry_picks.team_id)
LEFT JOIN %s AS pick_teams ON pool_entry_picks.team_id=pick_teams.id
LEFT JOIN %s AS home_teams ON games.home_team_id=home_teams.id
LEFT JOIN %s AS away_teams ON games.away_team_id=away_teams.id
WHERE seasons.year=:year AND pool_entry_picks.week=:week AND (pool_entry_picks.team_id=:home_team_id OR pool_entry_picks.team_id=:away_team_id) AND users.email IS NOT NULL AND users.result_notification=1
EOQ;
	$notifyquery = sprintf($notifyquery, TOTE_TABLE_POOL_ENTRY_PICKS, TOTE_TABLE_POOL_ENTRIES, TOTE_TABLE_POOLS, TOTE_TABLE_SEASONS, TOTE_TABLE_USERS, TOTE_TABLE_GAMES, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS, TOTE_TABLE_TEAMS);
	$notifystmt = $db->prepare($notifyquery);
	$notifystmt->bindParam(':year', $season, PDO::PARAM_INT);
	$notifystmt->bindParam(':week', $week, PDO::PARAM_INT);
	$notifystmt->bindParam(':home_team_id', $hometeam, PDO::PARAM_INT);
	$notifystmt->bindParam(':away_team_id', $awayteam, PDO::PARAM_INT);
	$notifystmt->execute();

	while ($notify = $notifystmt->fetch(PDO::FETCH_ASSOC)) {
		$win = false;
		$loss = false;
		if (
			(($notify['pick_id'] == $hometeam) && ($homescore > $awayscore)) ||
			(($notify['pick_id'] == $awayteam) && ($awayscore > $homescore))
		) {
			$win = true;
		} else if (
			(($notify['pick_id'] == $hometeam) && ($homescore < $awayscore)) ||
			(($notify['pick_id'] == $awayteam) && ($awayscore < $homescore))
		) {
			$loss = true;
		}

		$subject = '';
		if ($win) {
			$subject = 'Notification from ' . $sitename . ': Week ' . $week . ' pick won';
		} else if ($loss) {
			$subject = 'Notification from ' . $sitename . ': Week ' . $week . ' pick lost';
		} else {
			$subject = 'Notification from ' . $sitename . ': Week ' . $week . ' pick pushed';
		}

		$tpl->assign('win', $win);
		$tpl->assign('loss', $loss);
		$tpl->assign('data', $notify);
		$message = $tpl->fetch('notificationemail.tpl');
		send_email($notify['email'], $subject, $message, true);
	}

	$notifystmt = null;

}

