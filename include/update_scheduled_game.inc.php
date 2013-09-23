<?php

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
	global $db;

	$modified = false;

	$newstart = new DateTime('@' . $start);
	$newstart->setTimezone(new DateTimeZone('America/New_York'));

	echo 'Updating ' . $away . ' @ ' . $home . ' at ' . $newstart->format('D M j, Y g:i a T') . '... ';

	$gamestmt = $db->prepare('SELECT games.id, games.season_id, games.start FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=games.season_id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS home_teams ON games.home_team_id=home_teams.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS away_teams ON games.away_team_id=away_teams.id WHERE seasons.year=:year AND games.week=:week AND away_teams.abbreviation=:away_team_abbr AND home_teams.abbreviation=:home_team_abbr');
	$gamestmt->bindParam(':year', $season, PDO::PARAM_INT);
	$gamestmt->bindParam(':week', $week, PDO::PARAM_INT);
	$gamestmt->bindParam(':away_team_abbr', $away);
	$gamestmt->bindParam(':home_team_abbr', $home);
	$gamestmt->execute();
	$game = $gamestmt->fetch(PDO::FETCH_ASSOC);
	$gamestmt = null;

	$oldtz = date_default_timezone_get();
	date_default_timezone_set('UTC');

	if (!$game) {
		if ($season && $week && $home && $away && $start) {
			// game doesn't exist in the database - add it
			// (for schedule import)
			echo "adding game to database<br />\n";

			$seasonid = null;
			$seasonstmt = $db->prepare('SELECT id FROM ' . TOTE_TABLE_SEASONS . ' WHERE year=:year');
			$seasonstmt->bindParam(':year', $season, PDO::PARAM_INT);
			$seasonstmt->execute();
			$seasonstmt->bindColumn(1, $seasonid);
			$found = $seasonstmt->fetch(PDO::FETCH_BOUND);
			$seasonstmt = null;

			if (!$found || ($seasonid === null)) {
				// add new season
				$newseasonstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_SEASONS . ' (year) VALUES (:year)');
				$newseasonstmt->bindParam(':year', $season, PDO::PARAM_INT);
				$newseasonstmt->execute();
				$seasonid = $db->lastInsertId();
				$newseasonstmt = null;
			}

			$newgamestmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_GAMES . ' (season_id, week, home_team_id, away_team_id, start) VALUES (:season_id, :week, (SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=:home_team_abbr), (SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=:away_team_abbr), :start)');

			$datestr = date('Y-m-d H:i:s', $start);
			$newgamestmt->bindParam(':season_id', $seasonid, PDO::PARAM_INT);
			$newgamestmt->bindParam(':week', $week, PDO::PARAM_INT);
			$newgamestmt->bindParam(':home_team_abbr', $home);
			$newgamestmt->bindParam(':away_team_abbr', $away);
			$newgamestmt->bindParam(':start', $datestr);

			$newgamestmt->execute();
			$newgamestmt = null;
		
			$modified = true;
		} else {
			// we got incomplete data
			echo "error: couldn't locate game but don't have enough information to add it to database<br />\n";
		}
	} else if (empty($game['start']) || ($game['start'] == '0000-00-00 00:00:00') || (strtotime($game['start']) != $start)) {
		// start time in the database doesn't match, update it
		// (eg for flex scheduling change)

		echo 'updating start';

		$prevlaststartstmt = $db->prepare('SELECT MAX(start) FROM ' . TOTE_TABLE_GAMES . ' AS games WHERE season_id=:season_id AND week=:week');
		$prevlaststartstmt->bindParam(':season_id', $game['season_id'], PDO::PARAM_INT);
		$prevlaststartstmt->bindParam(':week', $week, PDO::PARAM_INT);
		$prevlaststartstmt->execute();
		$prevlaststart = null;
		$prevlaststartstmt->bindColumn(1, $prevlaststart);
		$prevlaststartstmt->fetch(PDO::FETCH_BOUND);
		$prevlaststartstmt = null;

		if (!empty($game['start']) && ($game['start'] != '0000-00-00 00:00:00')) {
			echo ' from ';
			$st = new DateTime('@' . strtotime($game['start']));
			$st->setTimezone(new DateTimeZone('America/New_York'));
			echo $st->format('D M j, Y g:i a T');
		}
		echo ' to ' . $newstart->format('D M j, Y g:i a T') . "<br />\n";
		$updategamestmt = $db->prepare('UPDATE ' . TOTE_TABLE_GAMES . ' SET start=:start WHERE id=:game_id');
		$datestr = date('Y-m-d H:i:s', $start);
		$updategamestmt->bindParam(':start', $datestr);
		$updategamestmt->bindParam(':game_id', $game['id'], PDO::PARAM_INT);
		$updategamestmt->execute();
		$updategamestmt = null;

		$newlaststartstmt = $db->prepare('SELECT MAX(start) FROM ' . TOTE_TABLE_GAMES . ' AS games WHERE season_id=:season_id AND week=:week');
		$newlaststartstmt->bindParam(':season_id', $game['season_id'], PDO::PARAM_INT);
		$newlaststartstmt->bindParam(':week', $week, PDO::PARAM_INT);
		$newlaststartstmt->execute();
		$newlaststart = null;
		$newlaststartstmt->bindColumn(1, $newlaststart);
		$newlaststartstmt->fetch(PDO::FETCH_BOUND);
		$newlaststartstmt = null;

		$prevlaststartstamp = strtotime($prevlaststart);
		$newlaststartstamp = strtotime($newlaststart);

		if ($newlaststartstamp != $prevlaststartstamp) {
			$now = time();

			if ((($newlaststartstamp < $now) && ($oldlaststartstamp > $now)) || (($oldlaststartstamp < $now) && ($newlaststartstamp > $now))) {
				// refresh open statuses, since this schedule change causes a week's
				// status to change from open to closed or vice versa
				$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE, ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' READ, ' . TOTE_TABLE_POOLS . ' READ');
				$db->exec('UPDATE ' . TOTE_TABLE_POOL_RECORDS . ' AS pool_records JOIN ' . TOTE_TABLE_POOLS . ' AS pools ON pool_records.pool_id=pools.id JOIN ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' AS pool_records_view ON pool_records.pool_id=pool_records_view.pool_id AND pool_records.user_id=pool_records_view.user_id AND pool_records.week=pool_records_view.week SET pool_records.open=pool_records_view.open WHERE pools.season_id=' . $db->quote($game['season_id']) . ' AND pool_records.week=' . $db->quote($week));
				$db->exec('UNLOCK TABLES');
			}

			// refresh next materialze date of pools that were basing theirs on this game
			$updatedatestmt = $db->prepare('UPDATE ' . TOTE_TABLE_POOLS . ' SET record_next_materialize=:new_next WHERE record_next_materialize=:prev_next');
			$updatedatestmt->bindParam(':new_next', $newlaststart);
			$updatedatestmt->bindParam(':prev_next', $prevlaststart);
			$updatedatestmt->execute();
			$updatedatestmt = null;
		}

	} else {
		// we're up to date
		echo "no update necessary, scheduled start up to date<br />\n";
	}

	date_default_timezone_set($oldtz);

	return $modified;
}
