<?php

include_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
include_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * For users that have bet on this game and have notifications
 * turned on, let them know
 *
 * @param int $season season
 * @param int $week week
 * @param object $hometeam home team id
 * @param int $homescore home team score
 * @param int $awayteam away team id
 * @param int $awayscore away team score
 */
function notify_finished_game($season, $week, $hometeam, $homescore, $awayteam, $awayscore)
{
	global $tpl, $tote_conf;

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);

	$tpl->assign('week', $week);
	$tpl->assign('hometeam', get_team($hometeam));
	$tpl->assign('awayteam', get_team($awayteam));
	$tpl->assign('homescore', $homescore);
	$tpl->assign('awayscore', $awayscore);

	$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
		'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	if (!empty($tote_conf['bccemail']))
		$headers .= "\r\nBcc: " . $tote_conf['bccemail'];

	$seasonpools = $pools->find(array('season' => $season));

	// go through all pools for this season
	foreach ($seasonpools as $pool) {

		$tpl->assign('pool', $pool);

		// go through all entrants in the pool
		foreach ($pool['entries'] as $entrant) {
		
			// check if the user has notifications turned on
			$entrantuser = $users->findOne(array('_id' => $entrant['user']), array('resultnotification', 'username', 'email', 'first_name', 'last_name'));
			if ($entrantuser && isset($entrantuser['resultnotification']) && ($entrantuser['resultnotification'] === true) && !empty($entrantuser['email'])) {
				foreach ($entrant['bets'] as $bet) {
					if ($bet['week'] != $week) {
						// wrong week
						continue;
					}

					if (($bet['team'] != $hometeam) && ($bet['team'] != $awayteam)) {
						// didn't bet on this game
						continue;
					}

					$win = false;
					$loss = false;
					if (
						(($bet['team'] == $hometeam) && ($homescore > $awayscore)) ||
						(($bet['team'] == $awayteam) && ($awayscore > $homescore))
					) {
						$win = true;
					} else if (
						(($bet['team'] == $hometeam) && ($awayscore > $homescore)) ||
						(($bet['team'] == $awayteam) && ($homescore > $awayscore))
					) {
						$loss = true;
					}
					// (otherwise assume push)

					$subject = '';
					if ($win) {
						$subject = 'Notification from ' . $tote_conf['sitename'] . ': Week ' . $week . ' pick won';
					} else if ($loss) {
						$subject = 'Notification from ' . $tote_conf['sitename'] . ': Week ' . $week . ' pick lost';
					} else {
						$subject = 'Notification from ' . $tote_conf['sitename'] . ': Week ' . $week . ' pick pushed';
					}

					$tpl->assign('user', $entrantuser);
					$tpl->assign('bet', get_team($bet['team']));
					$tpl->assign('win', $win);
					$tpl->assign('loss', $loss);
					$message = $tpl->fetch('notificationemail.tpl');
					mail($entrantuser['email'], $subject, $message, $headers);
				}
			}

		}
	}
}

