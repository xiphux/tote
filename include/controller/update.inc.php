<?php

date_default_timezone_set('America/New_York');

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

$teamids = array();

function lookup_team_id($team)
{
	global $db, $tote_conf, $teamids;

	$teamcol = 'teams';
	if (!empty($tote_conf['namespace']))
		$teamcol = $tote_conf['namespace'] . '.' . $teamcol;

	$teams = $db->selectCollection($teamcol);

	$teamobj = $teams->findOne(array('abbreviation' => $team));
	if ($teamobj) {
		$teamids[$team] = $teamobj['_id'];
		return;
	}

	$teamids[$team] = '';
}

function get_team_id($team)
{
	global $teamids;

	if (!isset($teamids[$team]))
		lookup_team_id($team);

	return $teamids[$team];
}

function update_finished_game($season, $week, $team1, $team1score, $team2, $team2score, $skipmsg = false)
{
	global $db, $tote_conf, $teamids;

	if (!$skipmsg) {
		echo 'Updating ' . $team1 . ' ' . $team1score . ', ' . $team2 . ' ' . $team2score . '... ';
	}

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

	$gamecol = 'games';
	if (!empty($tote_conf['namespace']))
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;

	$games = $db->selectCollection($gamecol);

	$js = "function() {
		return ((this.home_team == '" . $team1id . "') && (this.away_team == '" . $team2id . "')) || ((this.home_team == '" . $team2id . "') && (this.away_team == '" . $team1id . "'));
	}";

	$gameobj = $games->findOne(array('season' => (int)$season, 'week' => (int)$week, '$where' => $js));
	if (!$gameobj) {
		echo "error: Couldn't locate " . $team1 . " vs " . $team2 . " for week " . $week . "<br />\n";
		return;
	}

	$hometeam = '';
	$awayteam = '';
	$homeid = '';
	$awayid = '';
	$homescore = '';
	$awayscore = '';
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
		echo "error during update<br />\n";
		return;
	}

	if (!isset($gameobj['home_score']) || !isset($gameobj['away_score']) || ($gameobj['home_score'] != $homescore) || ($gameobj['away_score'] != $awayscore)) {
		echo 'updating from ' . $awayteam . (isset($gameobj['away_score']) ? ' ' . $gameobj['away_score'] : '') . ' @ ' . $hometeam . (isset($gameobj['home_score']) ? ' ' . $gameobj['home_score'] : '') . ' to ' . $awayteam . ' ' . $awayscore . ' @ ' . $hometeam . ' ' . $homescore . "<br />\n";
		$games->update(
			array('_id' => $gameobj['_id']),
			array('$set' => array(
				'home_score' => (int)$homescore,
				'away_score' => (int)$awayscore
			))
		);
	} else {
		echo "no update necessary, scores up to date<br />\n";
	}
}

function update_scheduled_game($season, $week, $away, $home, $start)
{
	global $db, $tote_conf;

	$newstart = new DateTime('@' . $start);
	$newstart->setTimezone(new DateTimeZone('America/New_York'));

	echo 'Updating ' . $away . ' @ ' . $home . ' at ' . $newstart->format('D M j, Y g:i a T') . '... ';

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

	$gamecol = 'games';
	if (!empty($tote_conf['namespace']))
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;

	$games = $db->selectCollection($gamecol);

	$gameobj = $games->findOne(array('season' => $season, 'week' => (int)$week, 'home_team' => $homeid, 'away_team' => $awayid));
	if (!$gameobj) {
		if ($season && $week && $homeid && $awayid && $start) {
			// add new game
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
			echo "error: couldn't locate game but don't have enough information to add it to database<br />\n";
		}
		return;
	}

	if ((!isset($gameobj['start'])) || ($gameobj['start']->sec != $start)) {
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
		echo "no update necessary, scheduled start up to date<br />\n";
	}
}

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

$url = 'http://espn.go.com/nfl/schedule';

$raw = load_page($url);

$dom = new DOMDocument();
@$dom->loadHTML($raw);

$xpath = new DOMXPath($dom);

echo '<p><strong>Scraping scores and schedule from ' . $url . "...</strong></p>\n";

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
	echo "<p>Error: couldn't determine season</p>\n";
} else {
	echo "<p><strong>Updating " . $season . " season...</strong></p>\n";
	$tables = $xpath->evaluate('/html/body//table[@class="tablehead"]');

	for ($i = 0; $i < $tables->length; $i++) {
		$table = $tables->item($i);

		$date = '';
		$week = '';

		echo '<p>';
		for ($j = 0; $j < $table->childNodes->length; $j++) {

			$row = $table->childNodes->item($j);

			if (empty($week)) {
				
				// header of the table defining the week
				if (preg_match('/Week ([0-9]+)/', $row->textContent, $regs)) {
					$week = $regs[1];
					echo '<strong>Updating week ' . $week . "...</strong><br />\n";
				} else {
					break;
				}


			} else if (preg_match('/[A-Z]{3}, ([A-Z]{3} [0-9]+)/', $row->firstChild->textContent, $regs)) {

					// header indicating the date of games
					$tmp = new DateTime(strtoupper($regs[1]) . ' ' . $season, new DateTimeZone('America/New_York'));
					if ($tmp !== false) {
						if ((int)($tmp->format('n')) < 8) {
							$tmp->modify("+1 year");
						}
						$date = $tmp;
					}

			} else {


				$text = strip_tags($row->firstChild->textContent);
				if (preg_match('/^([A-Za-z. ]+) ([0-9]+), ([A-Za-z. ]+) ([0-9]+)/', $text, $regs)) {

					// game that's already completed
					update_finished_game($season, $week, team_to_abbr($regs[1]), $regs[2], team_to_abbr($regs[3]), $regs[4]);

				} else if (preg_match('/^([A-Za-z. ]+) at ([A-Za-z. ]+)$/', $text, $regs) && !preg_match('/Bye:/', $text)) {

					// game that's scheduled
					$time = strip_tags($row->childNodes->item(2)->textContent);
					if (preg_match('/^([0-9]+):([0-9]+) ([AP]M)$/', $time, $timeregs)) {
						if (($timeregs[3] == 'PM') && ((int)$timeregs[1] < 12)) {
							$timeregs[1] = (int)$timeregs[1] + 12;
						}
						$date->setTime((int)$timeregs[1], (int)$timeregs[2]);
						update_scheduled_game($season, $week, team_to_abbr($regs[1]), team_to_abbr($regs[2]), (int)$date->format("U"));
					}


				}


			}
		}
		echo '</p>';
	}
}


$url = 'http://www.nfl.com/liveupdate/scorestrip/ss.xml';

$raw = load_page($url);

$dom = new DOMDocument();
@$dom->loadHTML($raw);

echo '<p><strong>Checking ' . $url . " for more up to date scores...</strong></p>\n";

$gms = $dom->getElementsByTagName('gms');

for ($i = 0; $i < $gms->length; $i++) {
	$gmnode = $gms->item($i);

	$season = $gmnode->attributes->getNamedItem('y')->value;
	$week = $gmnode->attributes->getNamedItem('w')->value;

	echo "<p><strong>Updating " . $season . " week " . $week . " ...</strong></p>\n";

	echo "<p>";
	for ($j = 0; $j < $gmnode->childNodes->length; $j++) {
		$g = $gmnode->childNodes->item($j);

		$home = $g->attributes->getNamedItem('h')->value;
		$visitor = $g->attributes->getNamedItem('v')->value;
		$quarter = $g->attributes->getNamedItem('q')->value;
		$homescore = $g->attributes->getNamedItem('hs')->value;
		$visitorscore = $g->attributes->getNamedItem('vs')->value;

		if (($quarter == 'F') || ($quarter == 'FO')) {
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . " ";
			if ($quarter == 'F')
				echo "final";
			else if ($quarter == 'FO')
				echo "final overtime";
			echo "... ";
			update_finished_game($season, $week, $home, $homescore, $visitor, $visitorscore, true);
		} else if ($quarter == 'P') {
			echo "Updating " . $visitor . " @ " . $home  . "... not started<br />\n";
		} else if ($quarter == 'H') {
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . "... currently halftime<br />\n";
		} else {
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . "... currently quarter " . $quarter;
			if ((int)$quarter > 4)
				echo " (overtime)";
			echo "<br />\n";
		}
	}
	echo "</p>";
}
