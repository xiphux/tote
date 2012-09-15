<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

/**
 * Gets pick distribution across pools
 *
 * @return array pick distribution data
 */
function pick_distribution()
{
	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$poolobjects = $pools->find(
		array(),
		array('entries', 'name', 'season')
	)->sort(array('season' => -1, 'name' => 1));

	$distdata = array();

	foreach ($poolobjects as $pool) {
		if (empty($pool['entries']) || (count($pool['entries']) < 1))
			continue;

		$poolid = (string)$pool['_id'];

		foreach ($pool['entries'] as $entrant) {

			if (empty($entrant['bets']) || (count($entrant['bets']) < 1))
				continue;

			if (!isset($distdata[$poolid])) {
				$distdata[$poolid] = array(
					'name' => $pool['name'],
					'season' => $pool['season'],
					'picks' => array()
				);
			}

			foreach ($entrant['bets'] as $bet) {
				if (empty($bet['team']))
					continue;

				$team = get_team($bet['team']);
				if (!$team)
					continue;

				$abbr = $team['abbreviation'];
				if (!$abbr)
					continue;

				if (empty($distdata[$poolid]['picks'][$abbr]))
					$distdata[$poolid]['picks'][$abbr] = 1;
				else
					$distdata[$poolid]['picks'][$abbr] += 1;
			
			}

		}

	}

	return $distdata;
}
