<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_game_by_team.inc.php');

function display_addbet($poolID, $week, $team)
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);
	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'first_name', 'last_name'));
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

	$username = $user['username'];
	if (!empty($user['first_name'])) {
		$username = $user['first_name'];
		if (!empty($user['last_name']))
			$username .= ' ' . $user['last_name'];
	}

	$pools->update(
		array('_id' => $pool['_id']),
		array('$push' => array(
			'entries.' . (string)$userentryindex . '.bets' => array(
				'week' => (int)$week,
				'team' => $betteam['_id'],
				'placed' => new MongoDate(time())
			),
			'actions' => array(
				'action' => 'bet',
				'user' => $user['_id'],
				'user_name' => $username,
				'week' => (int)$week,
				'team' => $betteam['_id'],
				'time' => new MongoDate(time())
			)
		))
	);
	header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?p=' . $pool['_id']);
}

