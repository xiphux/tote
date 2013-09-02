<?php
/**
 * Tote
 *
 * Reminder mailer script
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package Tote
 */

// define include directories
define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGDIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');

require_once(TOTE_CONFIGDIR . 'tote.conf.php');

require_once(TOTE_INCLUDEDIR . 'get_season_weeks.inc.php');

// only if reminders are turned on in the config
if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {

	// create Smarty
	require_once($tote_conf['smarty'] . 'Smarty.class.php');

	// work with UTC timestamps internally
	date_default_timezone_set('UTC');

	// create MongoDB connection
	$connection = null;
	if (!empty($tote_conf['connectionString']))
		$connection = new Mongo($tote_conf['connectionString']);
	else
		$connection = new Mongo('mongodb://localhost:27017');
	$db = $connection->selectDB($tote_conf['database']);

	require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
	require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');

	$tpl = new Smarty;

	$users = get_collection(TOTE_COLLECTION_USERS);
	$games = get_collection(TOTE_COLLECTION_GAMES);
	$pools = get_collection(TOTE_COLLECTION_POOLS);

	// find all users...
	$reminderusers = $users->find(
		array(
			'email' => array('$exists' => true),		// that have an email address
			'reminder' => true,				// and have reminders turned on
			'remindertime' => array('$exists' => true)	// and have a reminder time defined
		),
		array('email', 'username', 'first_name', 'last_name', 'reminder', 'remindertime', 'lastreminder', 'timezone')		// load these fields
	);

	// find running season's year
	$year = (int)date('Y');
	if ((int)date('n') < 2) {
		// January is part of the previous year's season
		$year--;
	}

	// find pools for this season
	$seasonpools = $pools->find(
		array(
			'season' => $year
		),
		array('entries')
	);

	// find users active in this season's pools
	$activeentrants = array();
	foreach ($reminderusers as $user) {
		foreach ($seasonpools as $pool) {
			foreach ($pool['entries'] as $entrant) {
				if (isset($entrant['user'])) {
					$activeentrants[(string)$entrant['user']] = true;
				}
			}
		}
	}

	// Find the number of weeks in the season
	$weeks = get_season_weeks((int)$year);

	// find the upcoming unstarted week
	$currentdate = new MongoDate(time());
	$firstgame = null;
	$weekgames = null;
	for ($i = 1; $i <= $weeks; $i++) {
		// if any game for the week has started, we're past the first game this week
		// so go to the next one
		$closedgame = $games->findOne(array('season' => (int)$year, 'week' => $i, 'start' => array('$lt' => $currentdate)), array('start'));
		if ($closedgame)
			continue;

		// this week is open - get the first game
		$weekgames = $games->find(array('season' => (int)$year, 'week' => $i))->sort(array('start' => 1));
		$firstgame = $weekgames->getNext();
		break;
	}

	if ($firstgame) {
		// we have an upcoming week

		// get the data for the games for this week
		$weekgamedata = array();
		foreach ($weekgames as $gm) {
			$gm['home_team'] = get_team($gm['home_team']);
			$gm['away_team'] = get_team($gm['away_team']);
			$gm['localstart'] = new DateTime('@' . $gm['start']->sec);
			$weekgamedata[] = $gm;
		}

		// set up data needed to generate reminder email
		$tpl->assign('sitename', $tote_conf['sitename']);
		$tpl->assign('week', $firstgame['week']);
		$tpl->assign('year', $year);
		$subject = 'Reminder from ' . $tote_conf['sitename'] . ': Week ' . $firstgame['week'] . ' is starting';
		$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
			'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		if (!empty($tote_conf['bccemail']))
			$headers .= "\r\nBcc: " . $tote_conf['bccemail'];


		// now process all users that want reminders
		foreach ($reminderusers as $user) {

			$userid = (string)$user['_id'];
			if (!(isset($activeentrants[$userid]) && ($activeentrants[$userid] == true))) {
				// user not in a pool this season
				continue;
			}

			if ((time() + (int)$user['remindertime']) < $firstgame['start']->sec) {
				// too early - haven't reached user's reminder time yet
				continue;
			}

			if (!empty($user['lastreminder']) && (($user['lastreminder']->sec + $user['remindertime']) > $firstgame['start']->sec)) {
				// we already reminded the user this week, don't do it again
				continue;
			}
		
			// use the user's preferred timezone, otherwise default to Eastern
			$tz = 'America/New_York';
			if (!empty($user['timezone']))
				$tz = $user['timezone'];

			for ($i = 0; $i < count($weekgamedata); $i++) {
				$weekgamedata[$i]['localstart']->setTimezone(new DateTimeZone($tz));
			}

			// set up user-specific data for the mail template
			$tpl->clear_assign('games');
			$tpl->assign('games', $weekgamedata);
			$tpl->clear_assign('user');
			$tpl->assign('user', $user);
		
			// send the mail
			$message = $tpl->fetch('reminderemail.tpl');
			mail($user['email'], $subject, $message, $headers);

			// mark the user as messaged
			$users->update(
				array('_id' => $user['_id']),
				array(
					'$set' => array(
						'lastreminder' => new MongoDate(time())
					)
				)
			);
		}
	}

}
