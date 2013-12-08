<?php

require_once(TOTE_INCLUDEDIR . 'load_page.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_scheduled_game.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_finished_game.inc.php');

/**
 * given a full team name from ESPN,
 * find the abbreviation used in the database
 *
 * @param string $team team name from website
 * @return string abbreviation
 */
function espn_team_to_abbr($team)
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


function update_games_espn()
{
	// times are reported on espn in Eastern
	$oldtz = date_default_timezone_get();
	date_default_timezone_set('America/New_York');

	$baseurl = 'http://espn.go.com/nfl/schedule';

	echo '<p><strong>Scraping scores and schedule from ' . $baseurl . "...</strong></p>\n";

	$season = null;

	$modified = false;

	// TODO find a way to not hardcode 17 weeks
	for ($week = 1; $week <= 17; ++$week) {

		$url = $baseurl . '/_/week/' . $week;

		$raw = load_page($url);

		$dom = new DOMDocument();
		@$dom->loadHTML($raw);

		$xpath = new DOMXPath($dom);

		if (empty($season)) {
			// find the season year in the header
			$headers = $xpath->evaluate('/html/body//h1');
			for ($i = 0; $i < $headers->length; $i++)
			{
				$head = $headers->item($i);

				if (preg_match('/NFL\s+Schedule - ([0-9]{4})/', $head->textContent, $regs))
				{
					$season = (int)$regs[1];
					echo "<p><strong>Updating " . $season . " season...</strong></p>\n";
					break;
				}
			}
		}

		if (empty($season)) {
			// don't update if we don't have a season
			echo "<p>Error: couldn't determine season</p>\n";
			return false;
		}

		echo '<strong>Updating week ' . $week . "...</strong><br />\n";

		// find all schedule tables
		$tables = $xpath->evaluate('/html/body//table[@class="tablehead"]');
		for ($i = 0; $i < $tables->length; $i++) {
			$table = $tables->item($i);

			$date = '';

			echo '<p>';
			for ($j = 0; $j < $table->childNodes->length; $j++) {

				$row = $table->childNodes->item($j);

				if (preg_match('/[A-Z]{3}, ([A-Z]{3} [0-9]+)/', $row->firstChild->textContent, $regs)) {

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
						update_finished_game($season, $week, espn_team_to_abbr($regs[1]), $regs[2], espn_team_to_abbr($regs[3]), $regs[4]);

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
							if (update_scheduled_game($season, $week, espn_team_to_abbr($regs[1]), espn_team_to_abbr($regs[2]), (int)$date->format("U")))
								$modified = true;
						}


					}


				}
			}
			echo '</p>';
		}

	}

	date_default_timezone_set($oldtz);

	return $modified;
}
