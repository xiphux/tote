<?php

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

$teamcache = array();

function get_team($id)
{
	global $teamcache, $teams;

	if (empty($teamcache[(string)$id])) {
		$teamcache[(string)$id] = $teams->findOne(array('_id' => $id), array('team', 'home', 'abbreviation'));
	}

	return $teamcache[(string)$id];
}

if (!empty($_GET['p'])) {
	$pool = $pools->findOne(array('_id' => new MongoId($_GET['p'])), array('season', 'entries'));
	if ($pool) {
		$user = $users->findOne(array('username' => $_SESSION['user']), array('username'));
		if ($user) {
			if (!empty($_GET['w'])) {
				if (!empty($_GET['team'])) {
					$userentry = null;
					$userentryindex = -1;
					for ($i = 0; $i < count($pool['entries']); $i++) {
						if ($pool['entries'][$i]['user'] == $user['_id']) {
							$userentry = $pool['entries'][$i];
							$userentryindex = $i;
							break;
						}
					}
					if ($userentry) {
						$betteam = $teams->findOne(array('_id' => new MongoId($_GET['team'])));
						if ($betteam) {
							$weekbet = false;
							$teambet = false;
							foreach ($pool['entries'] as $entrant) {
								if ($entrant['user'] == $user['_id']) {
									foreach ($entrant['bets'] as $bet) {
										if ($bet['week'] == (int)$_GET['w']) {
											$weekbet = $teams->findOne(array('_id' => $bet['team']));
										} else if ($bet['team'] == $betteam['_id'])
											$teambet = (int)$bet['week'];
									}
								}
							}
							if (!($weekbet || $teambet)) {
								// test if game already started
								$js = "function() {
									return ((this.home_team == '" . $betteam['_id'] . "') || (this.away_team == '" . $betteam['_id'] . "'));
								}";
								$betgame = $games->findOne(array('season' => (int)$pool['season'], 'week' => (int)$_GET['w'], '$where' => $js), array('start'));
								if ($betgame) {
									if ($betgame['start']->sec > time()) {
										$pools->update(
											array('_id' => $pool['_id']),
											array('$push' => array('entries.' . (string)$userentryindex . '.bets' => array('week' => (int)$_GET['w'], 'team' => $betteam['_id'])))
										);
										header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?p=' . $pool['_id']);
									} else {
										echo "This game has already started";
									}
								} else {
									echo $betteam['home'] . ' ' . $betteam['team'] . " aren't playing this week";
								}
							} else if ($weekbet) {
								echo "You've already bet on " . $weekbet['home'] . ' ' . $weekbet['team'] . " for week " . $_GET['w'];
							} else if ($teambet) {
								echo "You've already bet on " . $betteam['home'] . ' ' . $betteam['team'] . ' in week ' . $teambet;
							}
						} else {
							echo "Invalid team";
						}
					} else {
						echo "You are not entered in this pool";
					}
				} else {
					echo "A bet is required";
				}
			} else {
				echo "Week is required";
			}
		} else {
			echo "User not found";
		}
	} else {
		echo "Unknown pool";
	}
} else {
	echo "Pool is required";
}
