<?php

require_once(TOTE_INCLUDEDIR . 'load_page.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_scheduled_game.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_finished_game.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');

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
		case 'JAX':
			return 'JAC';
		case 'WSH':
			return 'WAS';
	}
	return $team;
}

define('ESPN_BASEURL', 'http://www.espn.com/nfl/schedule');
define('ESPN_WEEKURL', ESPN_BASEURL . '/_/year/%d/seasontype/%d/week/%d');
define('ESPN_SEASONTYPE_REGULAR', 2);

function update_games_espn_week($season, $week, &$weekcount, &$modified)
{
	if (empty($week) || empty($season))
		return false;

	$url = sprintf(ESPN_WEEKURL, $season, ESPN_SEASONTYPE_REGULAR, $week);

	$raw = load_page($url);

	$dom = new DOMDocument();
	@$dom->loadHTML($raw);

	$xpath = new DOMXPath($dom);

	$localseason = null;

	// find the season year in the header
	$headers = $xpath->evaluate('/html/body//h1');
	for ($i = 0; $i < $headers->length; $i++)
	{
		$head = $headers->item($i);

		if (preg_match('/NFL Schedule - ([0-9]{4})/', $head->textContent, $regs))
		{
			$localseason = (int)$regs[1];
			break;
		}
	}

	if (empty($localseason)) {
		// don't update if we don't have a season
		echo "<p>Error: couldn't determine season</p>\n";
		return false;
	} else if ($localseason != $season) {
		// season mismatch
		echo "<p>Error: season mismatch</p>\n";
		return false;
	}

	if (empty($weekcount)) {
		// use the week selection dropdown to figure out how many weeks are in the season
		$weeklinks = $xpath->evaluate('/html/body//div[contains(@class,"dropdown-type-week")]/select/option');
		for ($i = 0; $i < $weeklinks->length; $i++) {
			$weeklink = $weeklinks->item($i);
			if (preg_match('/^Week ([0-9]+)$/', $weeklink->textContent, $regs)) {
				if ((int)$regs[1] > $weekcount)
					$weekcount = (int)$regs[1];
			}
		}
	}
	if (empty($weekcount)) {
		// don't update if we don't have a number of weeks
		echo "<p>Error: couldn't determine number of weeks in season</p>\n";
		return false;
	}

	echo '<strong>Updating week ' . $week . "...</strong><br />\n";

	// find all schedule tables - one table per game day
	$tables = $xpath->evaluate('/html/body//table[contains(@class,"schedule")]');
	for ($i = 0; $i < $tables->length; $i++) {
		$table = $tables->item($i);

		$date = '';

		echo '<p>';
		
		$dateCaptions = $xpath->evaluate('caption', $table);
		for ($j = 0; $j < $dateCaptions->length; $j++) {
			$dateCaption = $dateCaptions->item($j);
			if (preg_match('/^[A-Za-z]+, ([A-Za-z]+ [0-9]+)$/', $dateCaption->textContent, $regs)) {
				// Table caption with date of games (eg Thursday, September 10)
				$tmp = new DateTime(strtoupper($regs[1]) . ' ' . $season, new DateTimeZone('America/New_York'));
				if ($tmp !== false) {
					if ((int)($tmp->format('n')) < 8) {
						// count anything before august as part of the next year (since season goes after new year)
						$tmp->modify("+1 year");
					}
					$date = $tmp;
				}
			}
		}
		
		// find game rows - one per game
		$gameRows = $xpath->evaluate('tbody/tr', $table);
		for ($j = 0; $j < $gameRows->length; $j++) {

			$row = $gameRows->item($j);

			$awayCell = $row->childNodes->item(0);
			$awayAbbr = $xpath->evaluate('a/abbr', $awayCell);
			$awayTeam = espn_team_to_abbr($awayAbbr->item(0)->textContent);
			
			$homeCell = $row->childNodes->item(1);
			$homeAbbr = $xpath->evaluate('a/abbr', $homeCell);
			$homeTeam = espn_team_to_abbr($homeAbbr->item(0)->textContent);
			
			$resultTime = $row->childNodes->item(2);
			$resultAttr = $resultTime->attributes->getNamedItem('data-date');
			if ($resultAttr) {
				// game that's scheduled
				$tmp = new DateTime(str_replace('Z', '+00:00', $resultAttr->textContent));
				if (update_scheduled_game($season, $week, $awayTeam, $homeTeam, (int)$tmp->format('U'))) {
					$modified = true;
				}
			} else {
				// game that's resolved
				$resultStr = $resultTime->firstChild->textContent;
				if (preg_match('/^([A-Za-z]+) ([0-9]+), ([A-Za-z]+) ([0-9]+)$/', $resultStr, $regs)) {
					$team1 = espn_team_to_abbr($regs[1]);
					$score1 = $regs[2];
					$team2 = espn_team_to_abbr($regs[3]);
					$score2 = $regs[4];
					
					update_finished_game($season, $week, $team1, $score1, $team2, $score2);
				}
			}
			
		}
		echo '</p>';
	}

	return true;
}


function update_games_espn($season = null)
{
	if (empty($season))
		$season = get_current_season();

	// times are reported on espn in Eastern
	$oldtz = date_default_timezone_get();
	date_default_timezone_set('America/New_York');

	echo '<p><strong>Scraping ' . $season . ' scores and schedule from ' . ESPN_BASEURL . "...</strong></p>\n";

	$modified = false;

	$weekcount = null;

	if (!update_games_espn_week($season, 1, $weekcount, $modified)) {
		date_default_timezone_set($oldtz);
		return false;
	}

	for ($week = 2; $week <= $weekcount; ++$week) {
		if (!update_games_espn_week($season, $week, $weekcount, $modified))
			break;
	}

	date_default_timezone_set($oldtz);

	return $modified;
}
