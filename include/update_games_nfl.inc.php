<?php

require_once(TOTE_INCLUDEDIR . 'update_finished_game.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');

define('NFL_SCORETICKER_URL', 'http://www.nfl.com/liveupdate/scorestrip/ss.xml');

function nfl_team_to_abbr($team)
{
       switch ($team) {
               case 'JAX':
                       return 'JAC';
       }
       return $team;
}

function update_games_nfl($season = null)
{
	if (empty($season))
		$season = get_current_season();

	$raw = load_page(NFL_SCORETICKER_URL);

	$dom = new DOMDocument();
	@$dom->loadHTML($raw);

	echo '<p><strong>Checking ' . NFL_SCORETICKER_URL . " for more up to date scores...</strong></p>\n";

	$gms = $dom->getElementsByTagName('gms');

	if ($gms->item(0)->attributes->getNamedItem('t')->value == 'P') {
		echo "<p>Preseason, skipping...</p>\n";
		return false;
	}

	// times are reported on nfl in Eastern
	$oldtz = date_default_timezone_get();
	date_default_timezone_set('America/New_York');

	$modified = false;

	for ($i = 0; $i < $gms->length; $i++) {
		// gms is a container for the week containing games inside it
		$gmnode = $gms->item($i);

		$localseason = $gmnode->attributes->getNamedItem('y')->value;
		if ($localseason != $season)
			continue;

		$week = $gmnode->attributes->getNamedItem('w')->value;

		echo "<p><strong>Updating " . $localseason . " week " . $week . " ...</strong></p>\n";

		echo "<p>";
		for ($j = 0; $j < $gmnode->childNodes->length; $j++) {
			// child g nodes, one for each game
			$g = $gmnode->childNodes->item($j);

			$home = nfl_team_to_abbr($g->attributes->getNamedItem('h')->value);
			$visitor = nfl_team_to_abbr($g->attributes->getNamedItem('v')->value);
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
				update_finished_game($localseason, $week, $home, $homescore, $visitor, $visitorscore, true);
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

	date_default_timezone_set($oldtz);

	return $modified;
}
