<?php

require_once(TOTE_INCLUDEDIR . 'get_game_by_team.inc.php');

function display_addbet($poolID, $week, $team)
{
	global $db, $tote_conf, $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$poolcol = 'pools';
	$usercol = 'users';
	$teamcol = 'teams';
	if (!empty($tote_conf['namespace'])) {
		$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
		$teamcol = $tote_conf['namespace'] . '.' . $teamcol;
	}

	$pools = $db->selectCollection($poolcol);
	$users = $db->selectCollection($usercol);
	$teams = $db->selectCollection($teamcol);

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username'));
	if (!$user) {
		echo "User not found";
		return;
	}

	if (empty($week)) {
		echo "Week is required";
		return;
	}

	if (empty($team)) {
		echo "A bet is required";
		return;
	}

	$userentry = null;
	$userentryindex = -1;
	for ($i = 0; $i < count($pool['entries']); $i++) {
		if ($pool['entries'][$i]['user'] == $user['_id']) {
			$userentry = $pool['entries'][$i];
			$userentryindex = $i;
			break;
		}
	}

	if (!$userentry) {
		echo "You are not entered in this pool";
		return;
	}

	$betteam = $teams->findOne(array('_id' => new MongoId($team)));
	if (!$betteam) {
		echo "Invalid team";
		return;
	}

	$weekbet = false;
	$teambet = false;
	foreach ($pool['entries'] as $entrant) {
		if ($entrant['user'] == $user['_id']) {
			foreach ($entrant['bets'] as $bet) {
				if ($bet['week'] == (int)$week) {
					$weekbet = $teams->findOne(array('_id' => $bet['team']));
				} else if ($bet['team'] == $betteam['_id'])
					$teambet = (int)$bet['week'];
			}
		}
	}

	if ($weekbet) {
		echo "You've already bet on " . $weekbet['home'] . ' ' . $weekbet['team'] . " for week " . $week;
		return;
	}

	if ($teambet) {
		echo "You've already bet on " . $betteam['home'] . ' ' . $betteam['team'] . ' in week ' . $teambet;
		return;
	}

	// test if game already started
	$betgame = get_game_by_team((int)$pool['season'], (int)$week, $betteam['_id']);
	if (!$betgame) {
		echo $betteam['home'] . ' ' . $betteam['team'] . " aren't playing this week";
		return;
	}

	if ($betgame['start']->sec < time()) {
		echo "This game has already started";
		return;
	}

	$pools->update(
		array('_id' => $pool['_id']),
		array('$push' => array('entries.' . (string)$userentryindex . '.bets' => array('week' => (int)$week, 'team' => $betteam['_id'])))
	);
	header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?p=' . $pool['_id']);
}

