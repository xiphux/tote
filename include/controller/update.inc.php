<?php

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
		echo "error: Couldn't locate " . $team1 . "<br />";
		return;
	}

	$team2id = get_team_id($team2);
	if (empty($team2id)) {
		echo "error: Couldn't locate " . $team2 . "<br />";
		return;
	}

	$gamecol = 'games';
	if (!empty($tote_conf['namespace']))
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;

	$games = $db->selectCollection($gamecol);

	$js = "function() {
		return ((this.home_team == '" . $team1id . "') && (this.away_team == '" . $team2id . "')) || ((this.home_team == '" . $team2id . "') && (this.away_team == '" . $team1id . "'));
	}";

	$gameobj = $games->findOne(array('season' => $season, 'week' => (int)$week, '$where' => $js));
	if (!$gameobj) {
		echo "error: Couldn't locate " . $team1 . " vs " . $team2 . " for week " . $week . "<br />";
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
		echo 'error during update<br />';
		return;
	}

	if (($gameobj['home_score'] != $homescore) || ($gameobj['away_score'] != $awayscore)) {
		echo 'updating from ' . $awayteam . ' ' . $gameobj['away_score'] . ' @ ' . $hometeam . ' ' . $gameobj['home_score'] . ' to ' . $awayteam . ' ' . $awayscore . ' @ ' . $hometeam . ' ' . $homescore . '<br />';
		/*
		$games->update(
			array('_id' => $gameobj['_id']),
			array('$set' => array(
				'home_score' => (int)$homescore,
				'away_score' => (int)$awayscore
			))
		);
		*/
	} else {
		echo 'no update necessary, scores up to date<br />';
	}
}

function update_scheduled_game($season, $week, $away, $home, $start)
{
	global $db, $tote_conf;

	echo 'Updating ' . $away . ' @ ' . $home . ' at ' . strftime('%c', $start) . '... ';

	$homeid = get_team_id($home);
	if (empty($homeid)) {
		echo "error: Couldn't locate " . $home . "<br />";
		return;
	}

	$awayid = get_team_id($away);
	if (empty($awayid)) {
		echo "error: Couldn't locate " . $away . "<br />";
		return;
	}

	$gamecol = 'games';
	if (!empty($tote_conf['namespace']))
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;

	$games = $db->selectCollection($gamecol);

	$gameobj = $games->findOne(array('season' => $season, 'week' => (int)$week, 'home_team' => $homeid, 'away_team' => $awayid));
	if (!$gameobj) {
		// add new game
		echo 'adding game to database<br />';
		$data = array(
			'season' => $season,
			'week' => (int)$week,
			'home_team' => $homeid,
			'away_team' => $awayid,
			'start' => $start
		);
		//$games->insert($data);
		return;
	}

	if ((!isset($gameobj['start'])) || ($gameobj['start']->sec != $start)) {
		echo 'updating scheduled start';
		if (isset($gameobj['start']))
			echo ' from ' . strftime("%c", $gameobj['start']->sec);
		echo ' to ' . strftime("%c", $start) . '<br />';
		/*
		$games->update(
			array('_id' => $gameobj['_id']),
			array('$set' => array(
				'start' => new MongoDate($start)
			))
		);
		*/
	} else {
		echo 'no update necessary, scheduled start up to date<br />';
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

date_default_timezone_set('America/New_York');

$url = 'http://espn.go.com/nfl/schedule';

$raw = load_page($url);

$dom = new DOMDocument();
@$dom->loadHTML($raw);

$xpath = new DOMXPath($dom);

echo '<p><strong>Scraping scores and schedule from ' . $url . '...</strong></p>';

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
	echo "<p>Error: couldn't determine season</p>";
} else {
	echo "<p><strong>Updating " . $season . " season...</strong></p>";
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
					echo '<strong>Updating week ' . $week . '...</strong><br />';
				} else {
					break;
				}


			} else if (preg_match('/[A-Z]{3}, ([A-Z]{3} [0-9]+)/', $row->firstChild->textContent, $regs)) {

					// header indicating the date of games
					$tmp = strtotime($regs[1]);
					if ($tmp !== false) {
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
					$timestamp = strtotime($time);
					if ($timestamp !== false) {
						$year = date('Y', $date);
						$month = date('n', $date);
						if ($month < 8) {
							// (pre)season starts in august,
							// games before that must be next year
							$year++;
						}
						$fulltimestamp = mktime(
							date('H', $timestamp),
							date('i', $timestamp),
							date('s', $timestamp),
							$month,
							date('j', $date),
							$year
						);
						update_scheduled_game($season, $week, team_to_abbr($regs[1]), team_to_abbr($regs[2]), $fulltimestamp);
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

echo '<p><strong>Checking ' . $url . ' for more up to date scores...</strong></p>';

$gms = $dom->getElementsByTagName('gms');

for ($i = 0; $i < $gms->length; $i++) {
	$gmnode = $gms->item($i);

	$season = $gmnode->attributes->getNamedItem('y')->value;
	$week = $gmnode->attributes->getNamedItem('w')->value;

	echo "<p><strong>Updating " . $season . " week " . $week . " ...</strong></p>";

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
			echo "Updating " . $visitor . " @ " . $home  . "... not started<br />";
		} else if ($quarter == 'H') {
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . "... currently halftime<br />";
		} else {
			echo "Updating " . $visitor . " " . $visitorscore . " @ " . $home . " " . $homescore . "... currently quarter " . $quarter;
			if ((int)$quarter > 4)
				echo " (overtime)";
			echo "<br />";
		}
	}
	echo "</p>";
}
