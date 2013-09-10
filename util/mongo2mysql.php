<?php
/**
 * Tote
 *
 * Mongo to MySQL conversion
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2013 Christopher Han
 * @package Tote
 * @subpackage Util
 */

require_once(dirname(__FILE__) . '/../config/tote.conf.php');

date_default_timezone_set('UTC');

// create MongoDB connection
$mongoconnection = null;
if (!empty($tote_conf['connectionString']))
	$mongoconnection = new Mongo($tote_conf['connectionString']);
else
	$mongoconnection = new Mongo('mongodb://localhost:27017');
$mongodb = $mongoconnection->selectDB($tote_conf['database']);

// create MySQL connection
$mysqldb = new mysqli($tote_conf['hostname'], $tote_conf['username'], $tote_conf['password'], $tote_conf['sql_database']);
if (!isset($tote_conf['prefix']))
	$tote_conf['prefix'] = '';

// clear data
echo "\nClearing data...\n";
$mysqldb->query('SET FOREIGN_KEY_CHECKS=0');
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'conferences');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'divisions');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'teams');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'seasons');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'games');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'users');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pools');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pool_entries');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pool_entry_picks');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pool_actions');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pool_payouts');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pool_payout_percents');
echo ".";
$mysqldb->query('TRUNCATE TABLE ' . $tote_conf['prefix'] . 'pool_administrators');
echo ".";
$mysqldb->query('SET FOREIGN_KEY_CHECKS=1');

// id mapping arrays
$conferenceidmap = array();
$divisionidmap = array();
$teamidmap = array();
$seasonidmap = array();
$gameidmap = array();
$useridmap = array();
$poolidmap = array();

// import teams
echo "\nImporting teams...\n";
$teamcollection = null;
if (!empty($tote_conf['namespace']))
	$teamcollection = $mongodb->selectCollection($tote_conf['namespace'] . '.' . 'teams');
else
	$teamcollection = $mongodb->selectCollection('teams');
$teams = $teamcollection->find(array());


$newconferencestmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'conferences (conference, abbreviation) VALUES (?, ?)');
if (!$newconferencestmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newdivisionstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'divisions (division, conference_id) VALUES (?, ?)');
if (!$newdivisionstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newteamstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'teams (team, home, abbreviation, division_id) VALUES (?, ?, ?, ?)');
if (!$newteamstmt) {
	echo $mysqldb->error . "\n";
	exit;
}

foreach ($teams as $team) {

	$divisionkey = $team['conference'] . ' ' . $team['division'];
	$divisionid = null;
	if (empty($divisionidmap[$divisionkey])) {
		// division doesn't exist, add it

		if (empty($conferenceidmap[$team['conference']])) {
			// conference doesn't exist, add it

			$conference = null;
			if ($team['conference'] == 'AFC') {
				$conference = 'American Football Conference';
			} else if ($team['conference'] == 'NFC') {
				$conference = 'National Football Conference';
			}

			$newconferencestmt->bind_param('ss', $conference, $team['conference']);
			$newconferencestmt->execute();
			
			$conferenceidmap[$team['conference']] = $mysqldb->insert_id;
		}
		$conferenceid = $conferenceidmap[$team['conference']];

		$newdivisionstmt->bind_param('si', $team['division'], $conferenceid);
		$newdivisionstmt->execute();

		$divisionidmap[$divisionkey] = $mysqldb->insert_id;
	}
	$divisionid = $divisionidmap[$divisionkey];

	$newteamstmt->bind_param('sssi', $team['team'], $team['home'], $team['abbreviation'], $divisionid);
	$newteamstmt->execute();

	$teamidmap[(string)$team['_id']] = $mysqldb->insert_id;

	echo ".";
}

// import games
echo "\nImporting games...\n";
$gamecollection = null;
if (!empty($tote_conf['namespace']))
	$gamecollection = $mongodb->selectCollection($tote_conf['namespace'] . '.' . 'games');
else
	$gamecollection = $mongodb->selectCollection('games');
$games = $gamecollection->find(array());

$newseasonstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'seasons (year) VALUES (?)');
if (!$newseasonstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newgamestmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'games (season_id, week, home_team_id, away_team_id, start, home_score, away_score, favorite_id, point_spread) VALUES (?, ?, ?, ?, ?, ?, ? , ?, ?)');
if (!$newgamestmt) {
	echo $mysqldb->error . "\n";
	exit;
}

foreach ($games as $game) {
	if (empty($seasonidmap[$game['season']])) {

		$newseasonstmt->bind_param('i', $game['season']);
		$newseasonstmt->execute();

		$seasonidmap[$game['season']] = $mysqldb->insert_id;
	}
	$seasonid = $seasonidmap[$game['season']];

	$homescore = isset($game['home_score']) ? $game['home_score'] : null;
	$awayscore = isset($game['away_score']) ? $game['away_score'] : null;
	$favorite = isset($game['favorite']) ? $teamidmap[(string)$game['favorite']] : null;
	$pointspread = isset($game['point_spread']) ? $game['point_spread'] : null;
	$start = date('Y-m-d H:i:s', $game['start']->sec);

	$newgamestmt->bind_param('iiiisiiid', $seasonid, $game['week'], $teamidmap[(string)$game['home_team']], $teamidmap[(string)$game['away_team']], $start, $homescore, $awayscore, $favorite, $pointspread);
	$newgamestmt->execute();

	$gameidmap[(string)$game['_id']] = $mysqldb->insert_id;
	echo ".";
}

// import users
echo "\nImporting users...\n";
$usercollection = null;
if (!empty($tote_conf['namespace']))
	$usercollection = $mongodb->selectCollection($tote_conf['namespace'] . '.' . 'users');
else	
	$usercollection = $mongodb->selectCollection('users');
$users = $usercollection->find(array());

$newuserstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'users (username, salt, password, recovery_key, email, first_name, last_name, role, created, last_login, last_password_change, reminder, reminder_time, last_reminder, result_notification, timezone, style) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$newuserstmt) {
	echo $mysqldb->error . "\n";
	exit;
}

foreach ($users as $user) {
	
	$username = $user['username'];
	$salt = $user['salt'];
	$password = $user['password'];
	$recoverykey = !empty($user['recoverykey']) ? $user['recoverykey'] : null;
	$email = !empty($user['email']) ? $user['email'] : null;
	$firstname = !empty($user['first_name']) ? $user['first_name'] : null;
	$lastname = !empty($user['last_name']) ? $user['last_name'] : null;
	$role = (isset($user['role']) && ($user['role'] > 0)) ? $user['role'] : 0;
	$created = isset($user['created']) ? date('Y-m-d H:i:s', $user['created']->sec) : null;
	$lastlogin = isset($user['lastlogin']) ? date('Y-m-d H:i:s', $user['lastlogin']->sec) : null;
	$lastpasswordchange = isset($user['lastpasswordchange']) ? date('Y-m-d H:i:s', $user['lastpasswordchange']->sec) : null;
	$reminder = (isset($user['reminder']) && $user['reminder']) ? 1 : 0;
	$remindertime = (isset($user['remindertime']) && ($user['remindertime'] > 0)) ? $user['remindertime'] : null;
	$lastreminder = isset($user['lastreminder']) ? date('Y-m-d H:i:s', $user['lastreminder']->sec) : null;
	$resultnotification = (isset($user['resultnotification']) && $user['resultnotification']) ? 1 : 0;
	$timezone = !empty($user['timezone']) ? $user['timezone'] : null;
	$style = !empty($user['style']) ? $user['style'] : null;

	$newuserstmt->bind_param('sssssssisssiisiss', $username, $salt, $password, $recoverykey, $email, $firstname, $lastname, $role, $created, $lastlogin, $lastpasswordchange, $reminder, $remindertime, $lastreminder, $resultnotification, $timezone, $style);
	$newuserstmt->execute();

	$useridmap[(string)$user['_id']] = $mysqldb->insert_id;
	echo ".";
}

// import pools
echo "\nImporting pools...\n";
$poolcollection = null;
if (!empty($tote_conf['namespace']))
	$poolcollection = $mongodb->selectCollection($tote_conf['namespace'] . '.' . 'pools');
else
	$poolcollection = $mongodb->selectCollection('pools');
$pools = $poolcollection->find(array());

$newpoolstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pools (season_id, fee, name) VALUES (?, ?, ?)');
if (!$newpoolstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newentrystmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pool_entries (pool_id, user_id) VALUES (?, ?)');
if (!$newentrystmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newpickstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pool_entry_picks (pool_entry_id, week, team_id, placed, edited) VALUES (?, ?, ?, ?, ?)');
if (!$newpickstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newactionstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pool_actions (pool_id, action, time, user_id, username, admin_id, admin_username, week, team_id, old_team_id, admin_type, old_admin_type, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$newactionstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newpayoutstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pool_payouts (pool_id, minimum, maximum) VALUES (?, ?, ?)');
if (!$newpayoutstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newpercentstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pool_payout_percents (payout_id, place, percent) VALUES (?, ?, ?)');
if (!$newpercentstmt) {
	echo $mysqldb->error . "\n";
	exit;
}
$newadminstmt = $mysqldb->prepare('INSERT INTO ' . $tote_conf['prefix'] . 'pool_administrators (pool_id, user_id, name, admin_type) VALUES (?, ?, ?, ?)');
if (!$newadminstmt) {
	echo $mysqldb->error . "\n";
	exit;
}

foreach ($pools as $pool) {
	
	$seasonid = $seasonidmap[$pool['season']];
	$fee = $pool['fee'];
	$name = $pool['name'];

	$newpoolstmt->bind_param('ids', $seasonid, $fee, $name);
	$newpoolstmt->execute();

	$poolid = $mysqldb->insert_id;
	$poolidmap[(string)$pool['_id']] = $poolid;

	if (isset($pool['entries'])) {
		foreach ($pool['entries'] as $entry) {

			$newentrystmt->bind_param('ii', $poolid, $useridmap[(string)$entry['user']]);
			$newentrystmt->execute();
			$entryid = $mysqldb->insert_id;
			echo ".";

			if (isset($entry['bets'])) {
				
				foreach ($entry['bets'] as $pick) {

					$week = $pick['week'];
					$team = $teamidmap[(string)$pick['team']];
					$placed = isset($pick['placed']) ? date('Y-m-d H:i:s', $pick['placed']->sec) : null;
					$edited = isset($pick['edited']) ? date('Y-m-d H:i:s', $pick['edited']->sec) : null;

					$newpickstmt->bind_param('iiiss', $entryid, $week, $team, $placed, $edited);
					$newpickstmt->execute();
					echo ".";

				}

			}

		}
	}

	if (isset($pool['actions'])) {
		foreach ($pool['actions'] as $action) {
			$actionnum = null;
			$adminid = null;
			$adminusername = null;
			$week = null;
			$teamid = null;
			$oldteamid = null;
			$admintype = null;
			$oldadmintype = null;

			$time = date('Y-m-d H:i:s', $action['time']->sec);
			$userid = $useridmap[(string)$action['user']];
			$username = $action['user_name'];
			$comment = !empty($action['comment']) ? $action['comment'] : null;

			switch ($action['action']) {

				case 'addentrant':
					$actionnum = 1;
					$adminid = $useridmap[(string)$action['admin']];
					$adminusername = $action['admin_name'];
					break;

				case 'removeentrant':
					$actionnum = 2;
					$adminid = $useridmap[(string)$action['admin']];
					$adminusername = $action['admin_name'];
					break;

				case 'pooladminchange':
					$actionnum = 3;
					$adminid = $useridmap[(string)$action['admin']];
					$adminusername = $action['admin_name'];
					$admintype = $action['newpooladmin'];
					$oldadmintype = $action['oldpooladmin'];
					break;

				case 'bet':
					$actionnum = 4;
					$week = $action['week'];
					$teamid = $teamidmap[(string)$action['team']];
					break;

				case 'edit':
					$actionnum = 5;
					$adminid = $useridmap[(string)$action['admin']];
					$adminusername = $action['admin_name'];
					$week = $action['week'];
					if (isset($action['from_team']))
						$oldteamid = $teamidmap[(string)$action['from_team']];
					if (isset($action['to_team']))
						$teamid = $teamidmap[(string)$action['to_team']];
					break;

				default:
					continue;
			}

			$newactionstmt->bind_param('iisisisiiiiis', $poolid, $actionnum, $time, $userid, $username, $adminid, $adminusername, $week, $teamid, $oldteamid, $admintype, $oldadmintype, $comment);
			$newactionstmt->execute();
			echo ".";
		}
	}

	if (isset($pool['payout'])) {
		foreach ($pool['payout'] as $payout) {

			if (!isset($payout['percents']))
				continue;
		
			$min = isset($payout['min']) ? $payout['min'] : null;
			$max = isset($payout['max']) ? $payout['max'] : null;

			$newpayoutstmt->bind_param('iii', $poolid, $min, $max);
			$newpayoutstmt->execute();

			$payoutid = $mysqldb->insert_id;

			foreach ($payout['percents'] as $index => $percent) {

				$place = $index+1;
				
				$newpercentstmt->bind_param('iid', $payoutid, $place, $percent);
				$newpercentstmt->execute();

				echo ".";
			}

		}
	}

	if (isset($pool['administrators'])) {
		foreach ($pool['administrators'] as $admin) {

			$userid = $useridmap[(string)$admin['user']];
			$name = $admin['name'];
			$admintype = (isset($admin['secondary']) && $admin['secondary']) ? 2 : 1;

			$newadminstmt->bind_param('iisi', $poolid, $userid, $name, $admintype);
			$newadminstmt->execute();

			echo ".";
		}
	}
	echo ".";
}

echo "\n";


$mysqldb->close();
