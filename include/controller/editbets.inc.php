<?php

function display_editbets($poolID, $entrant)
{
	global $db, $tote_conf, $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$poolcol = 'pools';
	$usercol = 'users';
	$gamecol = 'games';
	$teamcol = 'teams';
	if (!empty($tote_conf['namespace'])) {
		$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;
		$teamcol = $tote_conf['namespace'] . '.' . $teamcol;
	}

	$pools = $db->selectCollection($poolcol);
	$users = $db->selectCollection($usercol);
	$games = $db->selectCollection($gamecol);
	$teams = $db->selectCollection($teamcol);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$entrantobj = $users->findOne(array('_id' => new MongoId($entrant)), array('username', 'first_name', 'last_name'));
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
