<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_game_by_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('ADDBET_HEADER', 'Place A Bet');

/**
 * addbet controller
 *
 * add a bet to the database
 *
 * @param string $poolID pool ID
 * @param string $week week number
 * @param string $team team ID
 * @param string $csrftoken CSRF request token
 */
function display_addbet($poolID, $week, $team, $csrftoken)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", ADDBET_HEADER);
		return;
	}

	if (empty($poolID)) {
		// need to know the pool
		display_message("Pool is required", ADDBET_HEADER);
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	$pool = $pools->findOne(
		array('_id' => new MongoId($poolID)),
		array('season', 'entries')
	);
	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", ADDBET_HEADER);
		return;
	}

	if (empty($week)) {
		// week is required
		display_message("Week is required", ADDBET_HEADER);
		return;
	}

	if (empty($team)) {
		// bet is required
		display_message("A bet is required", ADDBET_HEADER);
		return;
	}

	// find the user's entry in the pool
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
		// can't bet if you aren't in the pool
		display_message("You are not entered in this pool", ADDBET_HEADER);
		return;
	}

	$betteam = $teams->findOne(array('_id' => new MongoId($team)));
	if (!$betteam) {
		// need to bet on a valid team
		display_message("Invalid team", ADDBET_HEADER);
		return;
	}

	// check and see if user has bet on this team or this week already
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
		// user already bet on this week
		display_message("You've already bet on " . $weekbet['home'] . ' ' . $weekbet['team'] . " for week " . $week, ADDBET_HEADER);
		return;
	}

	if ($teambet) {
		// user already bet on this team
		display_message("You've already bet on " . $betteam['home'] . ' ' . $betteam['team'] . ' in week ' . $teambet, ADDBET_HEADER);
		return;
	}

	// find game user is betting on
	$betgame = get_game_by_team((int)$pool['season'], (int)$week, $betteam['_id']);
	if (!$betgame) {
		// user bet on a bye team
		display_message($betteam['home'] . ' ' . $betteam['team'] . " aren't playing this week", ADDBET_HEADER);
		return;
	}

	if ($betgame['start']->sec < time()) {
		// can't bet after the game has started
		display_message("This game has already started", ADDBET_HEADER);
		return;
	}

	$username = user_readable_name($user);

	$pools->update(
		array('_id' => $pool['_id']),
		array(
			'$push' => array(
				'entries.' . (string)$userentryindex . '.bets' => array(	// add bet
					'week' => (int)$week,
					'team' => $betteam['_id'],
					'placed' => new MongoDate(time())
				),
				'actions' => array(	// audit bet entry
					'action' => 'bet',
					'user' => $user['_id'],
					'user_name' => $username,
					'week' => (int)$week,
					'team' => $betteam['_id'],
					'time' => new MongoDate(time())
				)
			)
		)
	);
	clear_cache('pool|' . (string)$pool['_id']);

	// go back to the pool view
	redirect(array('p' => $pool['_id']));
}

