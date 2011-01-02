<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('EDITBETS_HEADER', "Edit A User's Bets");

/**
 * editbets controller
 *
 * edit all of a user's bets
 *
 * @param string $poolID pool id
 * @param string $entrant entrant id
 */
function display_editbets($poolID, $entrant)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to edit bets
		return redirect();
	}

	if (empty($poolID)) {
		// need to know the pool
		display_message("Pool is required", EDITBETS_HEADER);
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$games = get_collection(TOTE_COLLECTION_GAMES);
	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	$pool = $pools->findOne(
		array(
			'_id' => new MongoId($poolID)
		),
		array('season', 'name', 'entries')
	);
	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", EDITBETS_HEADER);
		return;
	}

	$entrantobj = get_user($entrant);
	if (!$entrantobj) {
		// entrant being edited needs to exist
		display_message("Entrant not found", EDITBETS_HEADER);
		return;
	}

	$poolentry = null;
	foreach ($pool['entries'] as $entry) {
		if ($entry['user'] == $entrantobj['_id']) {
			$poolentry = $entry;
		}
	}

	if (!$poolentry) {
		// entrant being edited needs to be in the pool
		display_message("Entrant not in pool", EDITBETS_HEADER);
		return;
	}

	// make a list of the user's bets
	$userbets = array();
	if (isset($poolentry['bets'])) {
		foreach ($poolentry['bets'] as $bet) {
			$userbets[(int)$bet['week']] = $bet['team'];
		}
	}

	// find the number of weeks in the season
	$lastgame = $games->find(array('season' => (int)$pool['season']), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	// for any weeks user hasn't bet on, set a placeholder
	// so we can provide the option to add a bet there
	for ($i = 1; $i <= $weeks; $i++) {
		if (!isset($userbets[$i])) {
			$userbets[$i] = '';
		}
	}

	// sort in week order
	ksort($userbets);

	// make a list of all teams available
	$allteams = $teams->find(array())->sort(array('home' => 1, 'team' => 1));

	// provide data and display
	$tpl->assign('pool', $pool);
	$tpl->assign('entrant', $entrantobj);
	$tpl->assign('teams', $allteams);
	$tpl->assign('bets', $userbets);
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);

	$tpl->display('editbets.tpl');
}
