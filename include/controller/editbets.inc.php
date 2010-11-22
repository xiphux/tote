<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_editbets($poolID, $entrant)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (empty($user['admin'])) {
		return redirect();
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);
	$games = get_collection(TOTE_COLLECTION_GAMES);
	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$entrantobj = get_user($entrant);
	if (!$entrantobj) {
		echo "Entrant not found";
		return;
	}

	$poolentry = null;
	foreach ($pool['entries'] as $entry) {
		if ($entry['user'] == $entrantobj['_id']) {
			$poolentry = $entry;
		}
	}

	if (!$poolentry) {
		echo "Entrant not in pool";
		return;
	}

	$userbets = array();
	if (isset($poolentry['bets'])) {
		foreach ($poolentry['bets'] as $bet) {
			$userbets[(int)$bet['week']] = $bet['team'];
		}
	}

	$lastgame = $games->find(array('season' => (int)$pool['season']), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	for ($i = 1; $i <= $weeks; $i++) {
		if (!isset($userbets[$i])) {
			$userbets[$i] = '';
		}
	}

	ksort($userbets);

	$allteams = $teams->find(array())->sort(array('home' => 1, 'team' => 1));

	$tpl->assign('pool', $pool);
	$tpl->assign('entrant', $entrantobj);
	$tpl->assign('teams', $allteams);
	$tpl->assign('bets', $userbets);

	$tpl->display('editbets.tpl');
}
