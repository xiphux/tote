<?php

define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGDIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');

require_once(TOTE_CONFIGDIR . 'tote.conf.php');

if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {

	require_once($tote_conf['smarty'] . 'Smarty.class.php');

	date_default_timezone_set('UTC');

	$connection = null;
	if (!empty($tote_conf['connectionString']))
		$connection = new Mongo($tote_conf['connectionString'], array('persist' => 'tote'));
	else
		$connection = new Mongo('mongodb://localhost:27017', array('persist' => 'tote'));
	$db = $connection->selectDB($tote_conf['database']);

	require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

	$tpl = new Smarty;

	$usercol = 'users';
	$gamecol = 'games';
	if (!empty($tote_conf['namespace'])) {
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;
	}

	$users = $db->selectCollection($usercol);
	$games = $db->selectCollection($gamecol);

	$reminderusers = $users->find(array('email' => array('$exists' => true), 'reminder' => true, 'remindertime' => array('$exists' => true)), array('email', 'username', 'first_name', 'last_name', 'reminder', 'remindertime', 'lastreminder', 'timezone'));

	// find upcoming game
	$year = (int)date('Y');
	if ((int)date('n') < 2) {
		// January is part of the previous year's season
		$year--;
	}

	$lastgame = $games->find(array('season' => (int)$year), array('week'))->sort(array('week' => -1))->getNext();
	$weeks = $lastgame['week'];

	$currentdate = new MongoDate(time());
	$firstgame = null;
	$weekgames = null;
	for ($i = 1; $i <= $weeks; $i++) {
		// if any game for the week has started, we're past the first game this week
		// so go to the next one
		$closedgame = $games->findOne(array('season' => (int)$year, 'week' => $i, 'start' => array('$lt' => $currentdate)), array('start'));
		if ($closedgame)
			continue;

		// this week is open - get the first game's start time
		$weekgames = $games->find(array('season' => (int)$year, 'week' => $i))->sort(array('start' => 1));
		$firstgame = $weekgames->getNext();
		break;
	}

	if ($firstgame) {
		$weekgamedata = array();
		foreach ($weekgames as $gm) {
			$gm['home_team'] = get_team($gm['home_team']);
			$gm['away_team'] = get_team($gm['away_team']);
			$gm['localstart'] = new DateTime('@' . $gm['start']->sec);
			$weekgamedata[] = $gm;
		}

		$tpl->assign('sitename', $tote_conf['sitename']);
		$tpl->assign('week', $firstgame['week']);
		$tpl->assign('year', $year);
		$subject = 'Reminder from ' . $tote_conf['sitename'] . ': Week ' . $firstgame['week'] . ' is starting';
		$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
			'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		foreach ($reminderusers as $user) {
			if ((time() + (int)$user['remindertime']) < $firstgame['start']->sec) {
				// too early
				continue;
			}

			if (!empty($user['lastreminder']) && (($user['lastreminder']->sec + $user['remindertime']) > $firstgame['start']->sec)) {
				// already reminded user for this week
				continue;
			}
			
			$tz = 'America/New_York';
			if (!empty($user['timezone']))
				$tz = $user['timezone'];

			for ($i = 0; $i < count($weekgamedata); $i++) {
				$weekgamedata[$i]['localstart']->setTimezone(new DateTimeZone($tz));
			}

			$tpl->clear_assign('games');
			$tpl->assign('games', $weekgamedata);
			$tpl->clear_assign('user');
			$tpl->assign('user', $user);
			
			$message = $tpl->fetch('reminderemail.tpl');
			mail($user['email'], $subject, $message, $headers);

			$users->update(array('_id' => $user['_id']), array('$set' => array('lastreminder' => new MongoDate(time()))));
		}
	}

}
