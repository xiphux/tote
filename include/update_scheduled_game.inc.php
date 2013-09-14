<?php

require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * Update the time on a scheduled game that hasn't started yet
 *
 * @param string $season season
 * @param string $week week
 * @param string $away away team abbreviation
 * @param string $home home team abbreviation
 * @param int $start game start timestamp
 */
function update_scheduled_game($season, $week, $away, $home, $start)
{
	global $mysqldb;

	$newstart = new DateTime('@' . $start);
	$newstart->setTimezone(new DateTimeZone('America/New_York'));

	echo 'Updating ' . $away . ' @ ' . $home . ' at ' . $newstart->format('D M j, Y g:i a T') . '... ';

	$gamestmt = $mysqldb->prepare('SELECT games.id, games.start FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=games.season_id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=? AND games.week=? AND away_teams.abbreviation=? AND home_teams.abbreviation=?');
	$gamestmt->bind_param('iiss', $season, $week, $away, $home);
	$gamestmt->execute();
	$gameresult = $gamestmt->get_result();
	$game = $gameresult->fetch_assoc();
	$gameresult->close();
	$gamestmt->close();

	$oldtz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	if (!$game) {
		if ($season && $week && $home && $away && $start) {
			// game doesn't exist in the database - add it
			// (for schedule import)
			echo "adding game to database<br />\n";

			$seasonid = null;
			$seasonstmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_SEASONS . ' WHERE year=?');
			$seasonstmt->bind_param('i', $season);
			$seasonstmt->bind_result($seasonid);
			$seasonstmt->execute();
			$found = $seasonstmt->fetch();
			$seasonstmt->close();

			if (!$found) {
				// add new season
				$newseasonstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_SEASONS . ' (year) VALUES (?)');
				$newseasonstmt->bind_param('i', $season);
				$newseasonstmt->execute();
				$seasonid = $mysqldb->insert_id;
				$newseasonstmt->close();
			}

			$newgamestmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_GAMES . ' (season_id, week, home_team_id, away_team_id, start) VALUES (?, ?, (SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=?), (SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=?), ?)');

			$datestr = date('Y-m-d H:i:s', $start);
			$newgamestmt->bind_param('iisss', $seasonid, $week, $home, $away, $datestr);

			$newgamestmt->execute();
			$newgamestmt->close();
			
			clear_cache('pool');
		} else {
			// we got incomplete data
			echo "error: couldn't locate game but don't have enough information to add it to database<br />\n";
		}
	} else if (empty($game['start']) || ($game['start'] == '0000-00-00 00:00:00') || (strtotime($game['start']) != $start)) {
		// start time in the database doesn't match, update it
		// (eg for flex scheduling change)
		echo 'updating start';
		if (!empty($game['start']) && ($game['start'] != '0000-00-00 00:00:00')) {
			echo ' from ';
			$st = new DateTime('@' . strtotime($game['start']));
			$st->setTimezone(new DateTimeZone('America/New_York'));
			echo $st->format('D M j, Y g:i a T');
		}
		echo ' to ' . $newstart->format('D M j, Y g:i a T') . "<br />\n";
		$updategamestmt = $mysqldb->prepare('UPDATE ' . TOTE_TABLE_GAMES . ' SET start=? WHERE id=?');
		$datestr = date('Y-m-d H:i:s', $start);
		$updategamestmt->bind_param('si', $datestr, $game['id']);
		$updategamestmt->execute();
		$updategamestmt->close();
		
		clear_cache('pool');
	} else {
		// we're up to date
		echo "no update necessary, scheduled start up to date<br />\n";
	}

	date_default_timezone_set($oldtz);
}