<?php

require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');

function point_spread_team_to_abbr($team)
{
	switch ($team) {
		case 'Dallas Cowboys':
			return 'DAL';
		case 'New York Giants':
			return 'NYG';
		case 'Indianapolis Colts':
			return 'IND';
		case 'Chicago Bears':
			return 'CHI';
		case 'Philadelphia Eagles':
			return 'PHI';
		case 'Cleveland Browns':
			return 'CLE';
		case 'Buffalo Bills':
			return 'BUF';
		case 'New York Jets':
			return 'NYJ';
		case 'Washington Redskins':
			return 'WAS';
		case 'New Orleans Saints':
			return 'NO';
		case 'New England Patriots':
			return 'NE';
		case 'Tennessee Titans':
			return 'TEN';
		case 'Jacksonville Jaguars':
			return 'JAC';
		case 'Minnesota Vikings':
			return 'MIN';
		case 'Miami Dolphins':
			return 'MIA';
		case 'Houston Texans':
			return 'HOU';
		case 'St Louis Rams':
			return 'STL';
		case 'Detroit Lions':
			return 'DET';
		case 'Atlanta Falcons':
			return 'ATL';
		case 'Kansas City Chiefs':
			return 'KC';
		case 'San Francisco 49ers';
			return 'SF';
		case 'Green Bay Packers':
			return 'GB';
		case 'Carolina Panthers':
			return 'CAR';
		case 'Tampa Bay Buccaneers':
			return 'TB';
		case 'Seattle Seahawks':
			return 'SEA';
		case 'Arizona Cardinals':
			return 'ARI';
		case 'Pittsburgh Steelers':
			return 'PIT';
		case 'Denver Broncos':
			return 'DEN';
		case 'Cincinnati Bengals':
			return 'CIN';
		case 'Baltimore Ravens':
			return 'BAL';
		case 'San Diego Chargers':
			return 'SD';
		case 'Oakland Raiders':
			return 'OAK';
	}
}

/**
 * import point spreads for a season
 *
 * @param int $season season
 */
function import_point_spreads($season = null)
{
	global $db;

	if (empty($season))
		$season = get_current_season();

	if ($season < 2011) {
		// feeds aren't available before this
		return;
	}

	$url = 'http://www.repole.com/sun4cast/stats/nfl' . $season . 'lines.xml';

	echo '<p><strong>Updating ' . $season . ' point spreads from ' . $url . ' ...</strong></p>';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$raw = curl_exec($ch);
	curl_close($ch);

	if (empty($raw))
		return;

	$dom = new DOMDocument();
	@$dom->loadXML($raw);

	$scoresnode = $dom->childNodes->item(0);
	if (!$scoresnode || ($scoresnode->nodeName != 'scores')) {
		echo 'Error reading point spread xml';
		return;
	}

	$oldtz = date_default_timezone_get();

	$gamestmt = $db->prepare('SELECT games.id, games.favorite_id, favorites.abbreviation AS favorite_abbr, games.point_spread FROM ' . TOTE_TABLE_GAMES . ' AS games LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON games.season_id=seasons.id LEFT JOIN ' . TOTE_TABLE_TEAMS . ' AS favorites ON games.favorite_id=favorites.id WHERE seasons.year=:year AND games.home_team_id=(SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=:home_team_abbr) AND games.away_team_id=(SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=:away_team_abbr) AND games.start>=:start_min AND games.start<:start_max');
	$gamestmt->bindParam(':year', $season, PDO::PARAM_INT);

	$spreadstmt = $db->prepare('UPDATE ' . TOTE_TABLE_GAMES . ' SET favorite_id=(SELECT id FROM ' . TOTE_TABLE_TEAMS . ' WHERE abbreviation=:favorite_abbr), point_spread=:spread WHERE id=:game_id');

	for ($i = 0; $i < $scoresnode->childNodes->length; $i++) {
		$datenode = $scoresnode->childNodes->item($i);
		if ($datenode->nodeName != 'date')
			continue;

		$date = $datenode->attributes->getNamedItem('day');
		if (!$date)
			continue;
		$date = $date->nodeValue;
		if (empty($date))
			continue;

		for ($j = 0; $j < $datenode->childNodes->length; $j++) {
			$gamenode = $datenode->childNodes->item($j);
			if ($gamenode->nodeName != 'game')
				continue;

			$home = null;
			$visitor = null;
			$favorite = null;
			$spread = null;
			for ($k = 0; $k < $gamenode->childNodes->length; $k++) {
				$teamnode = $gamenode->childNodes->item($k);
				if ($teamnode->nodeName != 'team')
					continue;

				$team = null;
				for ($l = 0; $l < $teamnode->childNodes->length; $l++) {
					$teamdatanode = $teamnode->childNodes->item($l);
					if ($teamdatanode->nodeName == 'name') {
						$team = $teamdatanode->nodeValue;
						break;
					}
				}
				if (empty($team))
					continue;

				for ($l = 0; $l < $teamnode->childNodes->length; $l++) {
					$teamdatanode = $teamnode->childNodes->item($l);
					if ($teamdatanode->nodeName == 'site') {
						if ($teamdatanode->nodeValue == 'H')
							$home = $team;
						else if ($teamdatanode->nodeValue == 'V')
							$visitor = $team;
					} else if ($teamdatanode->nodeName == 'line') {
						$line = $teamdatanode->nodeValue;
						if (empty($line))
							break;
						if ((float)$line > 0)
							break;
						$spread = abs((float)$line);
						$favorite = $team;
					}
				}
				
			}

			if ($home && $visitor && $favorite && ($spread !== null)) {
				echo 'Updating ' . $visitor . ' @ ' . $home . ' on ' . $date . ' with ' . $favorite . ' favored by ' . $spread . '... ';

				$homeabbr = point_spread_team_to_abbr($home);
				if (empty($homeabbr)) {
					echo "invalid home team<br />\n";
					continue;
				}

				$visitorabbr = point_spread_team_to_abbr($visitor);
				if (empty($visitorabbr)) {
					echo "invalid away team<br />\n";
					continue;
				}

				$favoriteabbr = point_spread_team_to_abbr($favorite);

				date_default_timezone_set('America/New_York');
				$datestamp = strtotime($date . ' 00:00:00');
				$nextdatestamp = $datestamp + 86400;

				date_default_timezone_set('UTC');
				$datestr = date('Y-m-d H:i:s', $datestamp);
				$nextdatestr = date('Y-m-d H:i:s', $nextdatestamp);

				$gamestmt->bindParam(':home_team_abbr', $homeabbr);
				$gamestmt->bindParam(':away_team_abbr', $visitorabbr);
				$gamestmt->bindParam(':start_min', $datestr);
				$gamestmt->bindParam(':start_max', $nextdatestr);
				$gamestmt->execute();
				$game = $gamestmt->fetch(PDO::FETCH_ASSOC);

				if (!$game) {
					echo "game not found<br />\n";
					continue;
				}

				if (!empty($game['favorite_abbr']) && isset($game['point_spread']) && ($game['favorite_abbr'] == $favoriteabbr) && ($game['point_spread'] == $spread)) {
					echo "no update necessary<br />\n";
					continue;
				} else {
					$spreadstmt->bindParam(':favorite_abbr', $favoriteabbr);
					$spreadstmt->bindParam(':spread', $spread);
					$spreadstmt->bindParam(':game_id', $game['id'], PDO::PARAM_INT);
					$spreadstmt->execute();
					echo "updated<br />\n";
				}
			}
		}
	}

	$spreadstmt = null;
	$gamestmt = null;

	date_default_timezone_set($oldtz);
}
