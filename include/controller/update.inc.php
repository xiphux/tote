<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

// times are reported on websites in Eastern
date_default_timezone_set('America/New_York');

/**
 * given a full team name from ESPN,
 * find the abbreviation used in the database
 *
 * @param string $team team name from website
 * @return string abbreviation
 */
function team_to_abbr($team)
{
	switch ($team) {
		case 'New Orleans':
			return 'NO';
		case 'Minnesota':
			return 'MIN';
		case 'NY Giants':
			return 'NYG';
		case 'Carolina':
			return 'CAR';
		case 'Pittsburgh':
			return 'PIT';
		case 'Atlanta':
			return 'ATL';
		case 'Tampa Bay':
			return 'TB';
		case 'Cleveland':
			return 'CLE';
		case 'Jacksonville':
			return 'JAC';
		case 'Denver':
			return 'DEN';
		case 'Houston':
			return 'HOU';
		case 'Indianapolis':
			return 'IND';
		case 'Miami':
			return 'MIA';
		case 'Buffalo':
			return 'BUF';
		case 'Chicago':
			return 'CHI';
		case 'Detroit':
			return 'DET';
		case 'Tennessee':
			return 'TEN';
		case 'Oakland':
			return 'OAK';
		case 'New England':
			return 'NE';
		case 'Cincinnati':
			return 'CIN';
		case 'Arizona':
			return 'ARI';
		case 'St. Louis':
			return 'STL';
		case 'Seattle':
			return 'SEA';
		case 'San Francisco':
			return 'SF';
		case 'Green Bay':
			return 'GB';
		case 'Philadelphia':
			return 'PHI';
		case 'Washington':
			return 'WAS';
		case 'Dallas':
			return 'DAL';
		case 'Baltimore':
			return 'BAL';
		case 'NY Jets':
			return 'NYJ';
		case 'Kansas City':
			return 'KC';
		case 'San Diego':
			return 'SD';
	}
}

// cache team abbreviation to id mapping
$teamids = array();

/**
 * Given a team abbrevation, cache the team object ID
 *
 * @param string $team abbreviation
 */
function lookup_team_id($team)
{
	global $teamids;

	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	$teamobj = $teams->findOne(array('abbreviation' => $team));
	if ($teamobj) {
		$teamids[$team] = $teamobj['_id'];
		return;
	}

	$teamids[$team] = '';
}

/**
 * Given a team abbreviation, get the team object id
 *
 * @param string $team team abbreviation
 * @return object team id
 */
function get_team_id($team)
{
	global $teamids;

	if (!isset($teamids[$team]))
		lookup_team_id($team);

	return $teamids[$team];
}

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
	global $tpl, $tote_conf;

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);

	$tpl->assign('week', $week);
	$tpl->assign('hometeam', get_team($hometeam));
	$tpl->assign('awayteam', get_team($awayteam));
	$tpl->assign('homescore', $homescore);
	$tpl->assign('awayscore', $awayscore);

	$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
		'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	if (!empty($tote_conf['bccemail']))
		$headers .= "\r\nBcc: " . $tote_conf['bccemail'];

	$seasonpools = $pools->find(array('season' => $season));

	// go through all pools for this season
	foreach ($seasonpools as $pool) {

		$tpl->assign('pool', $pool);

		// go through all entrants in the pool
		foreach ($pool['entries'] as $entrant) {
		
			// check if the user has notifications turned on
			$entrantuser = $users->findOne(array('_id' => $entrant['user']), array('resultnotification', 'username', 'email', 'first_name', 'last_name'));
			if ($entrantuser && isset($entrantuser['resultnotification']) && ($entrantuser['resultnotification'] === true) && !empty($entrantuser['email'])) {
				foreach ($entrant['bets'] as $bet) {
					if ($bet['week'] != $week) {
						// wrong week
						continue;
					}

					if (($bet['team'] != $hometeam) && ($bet['team'] != $awayteam)) {
						// didn't bet on this game
						continue;
					}

					$win = false;
					$loss = false;
					if (
						(($bet['team'] == $hometeam) && ($homescore > $awayscore)) ||
						(($bet['team'] == $awayteam) && ($awayscore > $homescore))
					) {
						$win = true;
					} else if (
						(($bet['team'] == $hometeam) && ($awayscore > $homescore)) ||
						(($bet['team'] == $awayteam) && ($homescore > $awayscore))
					) {
						$loss = true;
					}
					// (otherwise assume push)

					$subject = '';
					if ($win) {
						$subject = 'Notification from ' . $tote_conf['sitename'] . ': Week ' . $week . ' bet won';
					} else if ($loss) {
						$subject = 'Notification from ' . $tote_conf['sitename'] . ': Week ' . $week . ' bet lost';
					} else {
						$subject = 'Notification from ' . $tote_conf['sitename'] . ': Week ' . $week . ' bet pushed';
					}

					$tpl->assign('user', $entrantuser);
					$tpl->assign('bet', get_team($bet['team']));
					if ($win)
						$tpl->assign('win', true);
					if ($loss)
						$tpl->assign('loss', true);
					$message = $tpl->fetch('notificationemail.tpl');
					mail($entrantuser['email'], $subject, $message, $headers);
				}
			}

		}
	}
}

/**
 * For a game that has finished and has scores,
 * update it in the database if necessary
 *
 * ESPN doesn't tell us which team is home and which is away
 *
 * @param string $season season
 * @param string $week week number
 * @param string $team1 team 1 abbreviation
 * @param string $team1score team 1 score
 * @param string $team2 team 2 abbreviation
 * @param string $team2score team 2 score
 * @param boolean $skipmsg true to skip "Updating..." message
 */
function update_finished_game($season, $week, $team1, $team1score, $team2, $team2score, $skipmsg = false)
{
	if (!$skipmsg) {
		echo 'Updating ' . $team1 . ' ' . $team1score . ', ' . $team2 . ' ' . $team2score . '... ';
	}

	// find the teams we're updating
	$team1id = get_team_id($team1);
	if (empty($team1id)) {
		echo "error: Couldn't locate " . $team1 . "<br />\n";
		return;
	}
	$team2id = get_team_id($team2);
	if (empty($team2id)) {
		echo "error: Couldn't locate " . $team2 . "<br />\n";
		return;
	}

	$games = get_collection(TOTE_COLLECTION_GAMES);

	// find the game where these two teams are playing this week
	$js = "function() {
		return ((this.home_team == '" . $team1id . "') && (this.away_team == '" . $team2id . "')) || ((this.home_team == '" . $team2id . "') && (this.away_team == '" . $team1id . "'));
	}";
	$gameobj = $games->findOne(
		array(
			'season' => (int)$season,
			'week' => (int)$week,
			'$where' => $js
		)
	);
	if (!$gameobj) {
		// these teams aren't playing this week
		echo "error: Couldn't locate " . $team1 . " vs " . $team2 . " for week " . $week . "<br />\n";
		return;
	}

	$hometeam = '';
	$awayteam = '';
	$homeid = '';
	$awayid = '';
	$homescore = '';
	$awayscore = '';

	// figure out which team is home and which is away
	if ($gameobj['home_team'] == $team1id) {
		$hometeam = $team1;
		$homeid = $team1id;
		$homescore = $team1score;
		$awayteam = $team2;
		$awayid = $team2id;
		$awayscore = $team2score;
	} else if ($gameobj['home_team'] == $team2id) {
		$hometeam = $team2;
		$homeid = $team2id;
		$homescore = $team2score;
		$awayteam = $team1;
		$awayid = $team1id;
		$awayscore = $team1score;
	} else {
		// should never happen
		echo "error during update<br />\n";
		return;
	}

	if (!isset($gameobj['home_score']) || !isset($gameobj['away_score']) || ($gameobj['home_score'] != $homescore) || ($gameobj['away_score'] != $awayscore)) {

		// scores don't match what we have in database - update it
		echo 'updating from ' . $awayteam . (isset($gameobj['away_score']) ? ' ' . $gameobj['away_score'] : '') . ' @ ' . $hometeam . (isset($gameobj['home_score']) ? ' ' . $gameobj['home_score'] : '') . ' to ' . $awayteam . ' ' . $awayscore . ' @ ' . $hometeam . ' ' . $homescore . "<br />\n";
		if (!(isset($gameobj['home_score']) || isset($gameobj['away_score']))) {
			// send notification emails if recording scores for the first time
			notify_finished_game((int)$season, (int)$week, $homeid, $homescore, $awayid, $awayscore);
		}
		$games->update(
			array('_id' => $gameobj['_id']),
			array('$set' => array(
				'home_score' => (int)$homescore,
				'away_score' => (int)$awayscore
			))
		);

	} else {
		// we're up to date
		echo "no update necessary, scores up to date<br />\n";
	}
}

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
	$homeid = get_team_id($home);
	if (empty($homeid)) {
		echo "error: Couldn't locate " . $home . "<br />\n";
		return;
	}
	$awayid = get_team_id($away);
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
				'start' => $start
			);
			//$games->insert($data);
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
	} else {
		// we're up to date
		echo "no update necessary, scheduled start up to date<br />\n";
	}
}

/**
 * load the HTML content of a url
 *
 * @param string $url url to load
 * @return string HTML content
 */
function load_page($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	ob_start();
	curl_exec($ch);
	curl_close($ch);
	$raw = ob_get_contents();
	ob_end_clean();

	return $raw;
}

// first check ESPN since it's easy to scrape the entire schedule

$url = 'http://espn.go.com/nfl/schedule';

$raw = load_page($url);

$dom = new DOMDocument();
@$dom->loadHTML($raw);

$xpath = new DOMXPath($dom);

echo '<p><strong>Scraping scores and schedule from ' . $url . "...</strong></p>\n";

// find the season year in the header
$headers = $xpath->evaluate('/html/body//h1');
$season = '';
for ($i = 0; $i < $headers->length; $i++)
{
	$head = $headers->item($i);

	if (preg_match('/NFL\s+Schedule - ([0-9]{4})/', $head->textContent, $regs))
	{
		$season = (int)$regs[1];
		break;
	}
}

if (!$season) {
	// don't update if we don't have a season
	echo "<p>Error: couldn't determine season</p>\n";
} else {
	echo "<p><strong>Updating " . $season . " season...</strong></p>\n";

	// find all schedule tables
	$tables = $xpath->evaluate('/html/body//table[@class="tablehead"]');
	for ($i = 0; $i < $tables->length; $i++) {
		$table = $tables->item($i);

		$date = '';
		$week = '';

		echo '<p>';
		for ($j = 0; $j < $table->childNodes->length; $j++) {

			$row = $table->childNodes->item($j);

			if (empty($week)) {
				
				// the first table row is the header of the table defining the week
				if (preg_match('/Week ([0-9]+)/', $row->textContent, $regs)) {
					$week = $regs[1];
					echo '<strong>Updating week ' . $week . "...</strong><br />\n";
				} else {
					break;
				}


			} else if (preg_match('/[A-Z]{3}, ([A-Z]{3} [0-9]+)/', $row->firstChild->textContent, $regs)) {

					// header with the date of the games below (eg THU, SEP 21)
					$tmp = new DateTime(strtoupper($regs[1]) . ' ' . $season, new DateTimeZone('America/New_York'));
					if ($tmp !== false) {
						if ((int)($tmp->format('n')) < 8) {
							// count anything before august as part of the next year (since season goes after new year)
							$tmp->modify("+1 year");
						}
						$date = $tmp;
					}

			} else {

				// is a game listing?
				$text = strip_tags($row->firstChild->textContent);
				if (preg_match('/^([A-Za-z. ]+) ([0-9]+), ([A-Za-z. ]+) ([0-9]+)/', $text, $regs)) {

					// game that's already finished eg "New Orleans 21, Carolina 14" - update it
					update_finished_game($season, $week, team_to_abbr($regs[1]), $regs[2], team_to_abbr($regs[3]), $regs[4]);

				} else if (preg_match('/^([A-Za-z. ]+) at ([A-Za-z. ]+)$/', $text, $regs) && !preg_match('/Bye:/', $text)) {

					// game that's scheduled eg Baltimore at Pittsburgh

					// the scheduled game time is in the table cell next to the teams
					$time = strip_tags($row->childNodes->item(2)->textContent);
					if (preg_match('/^([0-9]+):([0-9]+) ([AP]M)$/', $time, $timeregs)) {
						// eg 1:00 PM
						if (($timeregs[3] == 'PM') && ((int)$timeregs[1] < 12)) {
							// convert to 24 hour time
							$timeregs[1] = (int)$timeregs[1] + 12;
						}

						// combine date from date header and this time to form full game start datetime
						$date->setTime((int)$timeregs[1], (int)$timeregs[2]);
						// update it
						update_scheduled_game($season, $week, team_to_abbr($regs[1]), team_to_abbr($regs[2]), (int)$date->format("U"));
					}


				}


			}
		}
		echo '</p>';
	}
}

// now check the NFL scorestrip - feed only contains current week's scores, but
// is much more up to date

$url = 'http://www.nfl.com/liveupdate/scorestrip/ss.xml';

$raw = load_page($url);

$dom = new DOMDocument();
@$dom->loadHTML($raw);

echo '<p><strong>Checking ' . $url . " for more up to date scores...</strong></p>\n";

$gms = $dom->getElementsByTagName('gms');

for ($i = 0; $i < $gms->length; $i++) {
	// gms is a container for the week containing games inside it
	$gmnode = $gms->item($i);

	$season = $gmnode->attributes->getNamedItem('y')->value;
	$week = $gmnode->attributes->getNamedItem('w')->value;

	echo "<p><strong>Updating " . $season . " week " . $week . " ...</strong></p>\n";

	echo "<p>";
	for ($j = 0; $j < $gmnode->childNodes->length; $j++) {
		// child g nodes, one for each game
		$g = $gmnode->childNodes->item($j);

		$home = $g->attributes->getNamedItem('h')->value;
		$visitor = $g->attributes->getNamedItem('v')->value;
		$quarter = $g->attributes->getNamedItem('q')->value;
		$homescore = $g->attributes->getNamedItem('hs')->value;
		$visitorscore = $g->attributes->getNamedItem('vs')->value;

		if (($quarter == 'F') || ($quarter == 'FO')) {
			// final or final overtime - do the update
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . " ";
			if ($quarter == 'F')
				echo "final";
			else if ($quarter == 'FO')
				echo "final overtime";
			echo "... ";
			update_finished_game($season, $week, $home, $homescore, $visitor, $visitorscore, true);
		} else if ($quarter == 'P') {
			// pending (not started)
			echo "Updating " . $visitor . " @ " . $home  . "... not started<br />\n";
		} else if ($quarter == 'H') {
			// halftime
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . "... currently halftime<br />\n";
		} else {
			// some quarter
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . "... currently quarter " . $quarter;
			if ((int)$quarter > 4) {
				// obviously quarter 5 and above is overtime
				echo " (overtime)";
			}
			echo "<br />\n";
		}
	}
	echo "</p>";
}
