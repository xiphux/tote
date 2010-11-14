<?php

function sort_bets($a, $b)
{
	return ($a['week'] > $b['week'] ? 1 : -1);
}

function display_savebets($poolID, $entrant)
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

	$userentry = null;
	$userentryindex = -1;
	for ($i = 0; $i < count($pool['entries']); $i++) {
		if ($pool['entries'][$i]['user'] == $entrantobj['_id']) {
			$userentry = $pool['entries'][$i];
			$userentryindex = $i;
			break;
		}
	}

	if (!$userentry) {
		echo "Entrant not in pool";
		return;
	}

	$lastgame = $games->find(array('season' => (int)$pool['season']), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	$actions = array();

	for ($i = 1; $i <= $weeks; $i++) {
		if (empty($_POST['week' . $i])) {
			// no bet for the week
			for ($j = 0; $j < count($userentry['bets']); $j++) {
				if (isset($userentry['bets'][$j]) && ($userentry['bets'][$j]['week'] == $i)) {
					// delete existing bet
					$actions[] = array(
						'action' => 'edit',
						'user' => $entrantobj['_id'],
						'admin' => $user['_id'],
						'week' => $i,
						'from_team' => $userentry['bets'][$j]['team'],
						'time' => new MongoDate(time())
					);
					unset($userentry['bets'][$j]);
					break;
				}
			}
		} else {
			// setting a bet for a week
			$set = false;
			if (isset($userentry['bets'])) {
				for ($j = 0; $j < count($userentry['bets']); $j++) {
					if (isset($userentry['bets'][$j]) && ($userentry['bets'][$j]['week'] == $i)) {
						if ($_POST['week' . $i] != (string)$userentry['bets'][$j]['team']) {
							$actions[] = array(
								'action' => 'edit',
								'user' => $entrantobj['_id'],
								'admin' => $user['_id'],
								'week' => $i,
								'from_team' => $userentry['bets'][$j]['team'],
								'to_team' => new MongoId($_POST['week' . $i]),
								'time' => new MongoDate(time())
							);
							$userentry['bets'][$j]['team'] = new MongoId($_POST['week' . $i]);
							$userentry['bets'][$j]['edited'] = new MongoDate(time());
						}
						$set = true;
						break;
					}
				}
			}

			if (!$set) {
				// new bet, add it
				$actions[] = array(
					'action' => 'edit',
					'user' => $entrantobj['_id'],
					'admin' => $user['_id'],
					'week' => $i,
					'to_team' => new MongoId($_POST['week' . $i]),
					'time' => new MongoDate(time())
				);
				$userentry['bets'][] = array(
					'week' => $i,
					'team' => new MongoId($_POST['week' . $i]),
					'edited' => new MongoDate(time())
				);
			}
		}
	}

	usort($userentry['bets'], 'sort_bets');

	$pools->update(
		array('_id' => $pool['_id']),
		array(
		'$unset' => array('entries.' . (string)$userentryindex . '.bets' => 1),
		)
	);
	$pools->update(
		array('_id' => $pool['_id']),
		array(
		'$set' => array('entries.' . (string)$userentryindex . '.bets' => $userentry['bets'])
		)
	);
	$pools->update(
		array('_id' => $pool['_id']),
		array(
			'$pushAll' => array('actions' => $actions)
		)
	);
	
	header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
}
