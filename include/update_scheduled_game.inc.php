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
	$newstart = new DateTime('@' . $start);
	$newstart->setTimezone(new DateTimeZone('America/New_York'));

	echo 'Updating ' . $away . ' @ ' . $home . ' at ' . $newstart->format('D M j, Y g:i a T') . '... ';

	// find teams
	$homeid = team_abbreviation_to_id($home);
	if (empty($homeid)) {
		echo "error: Couldn't locate " . $home . "<br />\n";
		return;
	}
	$awayid = team_abbreviation_to_id($away);
	if (empty($awayid)) {
		echo "error: Couldn't locate " . $away . "<br />\n";
		return;
	}

	$games = get_collection(TOTE_COLLECTION_GAMES);

	// find the game
	$gameobj = $games->findOne(
		array(
			'season' => $season,
			'week' => (int)$week,
			'home_team' => $homeid,
			'away_team' => $awayid
		)
	);
	if (!$gameobj) {
		if ($season && $week && $homeid && $awayid && $start) {
			// game doesn't exist in the database - add it
			// (for schedule import)
			echo "adding game to database<br />\n";
			$data = array(
				'season' => $season,
				'week' => (int)$week,
				'home_team' => $homeid,
				'away_team' => $awayid,
				'start' => new MongoDate($start)
			);
			//$games->insert($data);
			clear_cache('pool');
		} else {
			// we got incomplete data
			echo "error: couldn't locate game but don't have enough information to add it to database<br />\n";
		}
		return;
	}

	if ((!isset($gameobj['start'])) || ($gameobj['start']->sec != $start)) {
		// start time in the database doesn't match, update it
		// (eg for flex scheduling change)
		echo 'updating start';
		if (isset($gameobj['start'])) {
			echo ' from ';
			$st = new DateTime('@' . $gameobj['start']->sec);
			$st->setTimezone(new DateTimeZone('America/New_York'));
			echo $st->format('D M j, Y g:i a T');
		}
		echo ' to ' . $newstart->format('D M j, Y g:i a T') . "<br />\n";
		$games->update(
			array('_id' => $gameobj['_id']),
			array('$set' => array(
				'start' => new MongoDate($start)
			))
		);
		clear_cache('pool');
	} else {
		// we're up to date
		echo "no update necessary, scheduled start up to date<br />\n";
	}
}
