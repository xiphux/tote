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

exit;

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
$db = new PDO(sprintf('mysql:host=%s;dbname=%s', $tote_conf['hostname'], $tote_conf['sql_database']), $tote_conf['username'], $tote_conf['password']);
if (!isset($tote_conf['prefix']))
	$tote_conf['prefix'] = '';

define('TOTE_TABLE_CONFERENCES', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'conferences');
define('TOTE_TABLE_DIVISIONS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'divisions');
define('TOTE_TABLE_TEAMS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'teams');
define('TOTE_TABLE_SEASONS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'seasons');
define('TOTE_TABLE_GAMES', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'games');
define('TOTE_TABLE_USERS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'users');
define('TOTE_TABLE_POOLS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pools');
define('TOTE_TABLE_POOL_ENTRIES', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_entries');
define('TOTE_TABLE_POOL_ENTRY_PICKS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_entry_picks');
define('TOTE_TABLE_POOL_ACTIONS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_actions');
define('TOTE_TABLE_POOL_PAYOUTS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_payouts');
define('TOTE_TABLE_POOL_PAYOUT_PERCENTS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_payout_percents');
define('TOTE_TABLE_POOL_ADMINISTRATORS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_administrators');
define('TOTE_TABLE_POOL_RECORDS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_records');
define('TOTE_TABLE_POOL_RECORDS_VIEW', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_records_view');

// disable data checks to speed up import
$db->exec('SET foreign_key_checks=0');
$db->exec('SET unique_checks=0');

// clear data
echo "\nClearing data...\n";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_CONFERENCES);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_DIVISIONS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_TEAMS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_SEASONS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_GAMES);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_USERS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOLS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_ENTRIES);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_ENTRY_PICKS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_ACTIONS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_PAYOUTS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_ADMINISTRATORS);
echo ".";
$db->exec('TRUNCATE TABLE ' . TOTE_TABLE_POOL_RECORDS);
echo ".";

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


$newconferencestmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_CONFERENCES . ' (conference, abbreviation) VALUES (:conference, :abbreviation)');
if (!$newconferencestmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newdivisionstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_DIVISIONS . ' (division, conference_id) VALUES (:division, :conference_id)');
if (!$newdivisionstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newteamstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_TEAMS . ' (team, home, abbreviation, division_id) VALUES (:team, :home, :abbreviation, :division_id)');
if (!$newteamstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
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
	
			$newconferencestmt->bindParam(':conference', $conference);
			$newconferencestmt->bindParam(':abbreviation', $team['conference']);
			$newconferencestmt->execute();
			
			$conferenceidmap[$team['conference']] = $db->lastInsertId();
		}
		$conferenceid = $conferenceidmap[$team['conference']];

		$newdivisionstmt->bindParam(':division', $team['division']);
		$newdivisionstmt->bindParam(':conference_id', $conferenceid, PDO::PARAM_INT);
		$newdivisionstmt->execute();

		$divisionidmap[$divisionkey] = $db->lastInsertId();
	}
	$divisionid = $divisionidmap[$divisionkey];

	$newteamstmt->bindParam(':team', $team['team']);
	$newteamstmt->bindParam(':home', $team['home']);
	$newteamstmt->bindParam(':abbreviation', $team['abbreviation']);
	$newteamstmt->bindParam(':division_id', $divisionid, PDO::PARAM_INT);
	$newteamstmt->execute();

	$teamidmap[(string)$team['_id']] = $db->lastInsertId();

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

$newseasonstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_SEASONS . ' (year) VALUES (:year)');
if (!$newseasonstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newgamestmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_GAMES . ' (season_id, week, home_team_id, away_team_id, start, home_score, away_score, favorite_id, point_spread) VALUES (:season_id, :week, :home_team_id, :away_team_id, :start, :home_score, :away_score, :favorite_id, :point_spread)');
if (!$newgamestmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}

foreach ($games as $game) {
	if (empty($seasonidmap[$game['season']])) {

		$newseasonstmt->bindParam(':year', $game['season'], PDO::PARAM_INT);
		$newseasonstmt->execute();

		$seasonidmap[$game['season']] = $db->lastInsertId();
	}
	$seasonid = $seasonidmap[$game['season']];

	$homescore = isset($game['home_score']) ? $game['home_score'] : null;
	$awayscore = isset($game['away_score']) ? $game['away_score'] : null;
	$favorite = isset($game['favorite']) ? $teamidmap[(string)$game['favorite']] : null;
	$pointspread = isset($game['point_spread']) ? $game['point_spread'] : null;
	$start = date('Y-m-d H:i:s', $game['start']->sec);

	$newgamestmt->bindParam(':season_id', $seasonid, PDO::PARAM_INT);
	$newgamestmt->bindParam(':week', $game['week'], PDO::PARAM_INT);
	$newgamestmt->bindParam(':home_team_id', $teamidmap[(string)$game['home_team']], PDO::PARAM_INT);
	$newgamestmt->bindParam(':away_team_id', $teamidmap[(string)$game['away_team']], PDO::PARAM_INT);
	$newgamestmt->bindParam(':start', $start);
	$newgamestmt->bindParam(':home_score', $homescore, PDO::PARAM_INT);
	$newgamestmt->bindParam(':away_score', $awayscore, PDO::PARAM_INT);
	$newgamestmt->bindParam(':favorite_id', $favorite, PDO::PARAM_INT);
	$newgamestmt->bindParam(':point_spread', $pointspread);
	$newgamestmt->execute();

	$gameidmap[(string)$game['_id']] = $db->lastInsertId();
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

$newuserstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_USERS . ' (username, salt, password, recovery_key, email, first_name, last_name, role, created, last_login, last_password_change, reminder, reminder_time, last_reminder, result_notification, timezone, style) VALUES (:username, :salt, :password, :recovery_key, :email, :first_name, :last_name, :role, :created, :last_login, :last_password_change, :reminder, :reminder_time, :last_reminder, :result_notification, :timezone, :style)');
if (!$newuserstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}

foreach ($users as $user) {
	
	$username = $user['username'];
	$salt = $user['salt'];
	$password = $user['password'];
	$recoverykey = !empty($user['recoverykey']) ? $user['recoverykey'] : null;
	$email = !empty($user['email']) ? $user['email'] : null;
	if ($username != 'xiphux')
		$email = null;
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

	$newuserstmt->bindParam(':username', $username);
	$newuserstmt->bindParam(':salt', $salt);
	$newuserstmt->bindParam(':password', $password);
	$newuserstmt->bindParam(':recovery_key', $recoverykey);
	$newuserstmt->bindParam(':email', $email);
	$newuserstmt->bindParam(':first_name', $firstname);
	$newuserstmt->bindParam(':last_name', $lastname);
	$newuserstmt->bindParam(':role', $role, PDO::PARAM_INT);
	$newuserstmt->bindParam(':created', $created);
	$newuserstmt->bindParam(':last_login', $lastlogin);
	$newuserstmt->bindParam(':last_password_change', $lastpasswordchange);
	$newuserstmt->bindParam(':reminder', $reminder, PDO::PARAM_INT);
	$newuserstmt->bindParam(':reminder_time', $remindertime, PDO::PARAM_INT);
	$newuserstmt->bindParam(':last_reminder', $lastreminder);
	$newuserstmt->bindParam(':result_notification', $resultnotification, PDO::PARAM_INT);
	$newuserstmt->bindParam(':timezone', $timezone);
	$newuserstmt->bindParam(':style', $style);
	$newuserstmt->execute();

	$useridmap[(string)$user['_id']] = $db->lastInsertId();
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

$newpoolstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOLS . ' (season_id, fee, name) VALUES (:season_id, :fee, :name)');
if (!$newpoolstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newentrystmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRIES . ' (pool_id, user_id) VALUES (:pool_id, :user_id)');
if (!$newentrystmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newpickstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' (pool_entry_id, week, team_id, placed, edited) VALUES (:pool_entry_id, :week, :team_id, :placed, :edited)');
if (!$newpickstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newactionstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ACTIONS . ' (pool_id, action, time, user_id, username, admin_id, admin_username, week, team_id, old_team_id, admin_type, old_admin_type, comment) VALUES (:pool_id, :action, :time, :user_id, :username, :admin_id, :admin_username, :week, :team_id, :old_team_id, :admin_type, :old_admin_type, :comment)');
if (!$newactionstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newpayoutstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUTS . ' (pool_id, minimum, maximum) VALUES (:pool_id, :minimum, :maximum)');
if (!$newpayoutstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newpercentstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (:payout_id, :place, :percent)');
if (!$newpercentstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}
$newadminstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ADMINISTRATORS . ' (pool_id, user_id, name, admin_type) VALUES (:pool_id, :user_id, :name, :admin_type)');
if (!$newadminstmt) {
	$error = $db->errorInfo();
	echo $error[2] . "\n";
	$db->exec('SET unique_checks=1');
	$db->exec('SET foreign_key_checks=1');
	exit;
}

foreach ($pools as $pool) {
	
	$seasonid = $seasonidmap[$pool['season']];
	$fee = $pool['fee'];
	$name = $pool['name'];

	$newpoolstmt->bindParam(':season_id', $seasonid, PDO::PARAM_INT);
	$newpoolstmt->bindParam(':fee', $fee);
	$newpoolstmt->bindParam(':name', $name);
	$newpoolstmt->execute();

	$poolid = $db->lastInsertId();
	$poolidmap[(string)$pool['_id']] = $poolid;

	if (isset($pool['entries'])) {
		foreach ($pool['entries'] as $entry) {

			$newentrystmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
			$newentrystmt->bindParam(':user_id', $useridmap[(string)$entry['user']], PDO::PARAM_INT);
			$newentrystmt->execute();
			$entryid = $db->lastInsertId();
			echo ".";

			if (isset($entry['bets'])) {
				
				foreach ($entry['bets'] as $pick) {

					$week = $pick['week'];
					$team = $teamidmap[(string)$pick['team']];
					$placed = isset($pick['placed']) ? date('Y-m-d H:i:s', $pick['placed']->sec) : null;
					$edited = isset($pick['edited']) ? date('Y-m-d H:i:s', $pick['edited']->sec) : null;

					$newpickstmt->bindParam(':pool_entry_id', $entryid, PDO::PARAM_INT);
					$newpickstmt->bindParam(':week', $week, PDO::PARAM_INT);
					$newpickstmt->bindParam(':team_id', $team, PDO::PARAM_INT);
					$newpickstmt->bindParam(':placed', $placed);
					$newpickstmt->bindParam(':edited', $edited);
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

			$newactionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
			$newactionstmt->bindParam(':action', $actionnum, PDO::PARAM_INT);
			$newactionstmt->bindParam(':time', $time);
			$newactionstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
			$newactionstmt->bindParam(':username', $username);
			$newactionstmt->bindParam(':admin_id', $adminid, PDO::PARAM_INT);
			$newactionstmt->bindParam(':admin_username', $adminusername);
			$newactionstmt->bindParam(':week', $week, PDO::PARAM_INT);
			$newactionstmt->bindParam(':team_id', $teamid, PDO::PARAM_INT);
			$newactionstmt->bindParam(':old_team_id', $oldteamid, PDO::PARAM_INT);
			$newactionstmt->bindParam(':admin_type', $admintype, PDO::PARAM_INT);
			$newactionstmt->bindParam(':old_admin_type', $oldadmintype, PDO::PARAM_INT);
			$newactionstmt->bindParam(':comment', $comment);
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

			$newpayoutstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
			$newpayoutstmt->bindParam(':minimum', $min, PDO::PARAM_INT);
			$newpayoutstmt->bindParam(':maximum', $max, PDO::PARAM_INT);
			$newpayoutstmt->execute();

			$payoutid = $db->lastInsertId();

			foreach ($payout['percents'] as $index => $percent) {

				$place = $index+1;
			
				$newpercentstmt->bindParam(':payout_id', $payoutid, PDO::PARAM_INT);
				$newpercentstmt->bindParam(':place', $place, PDO::PARAM_INT);
				$newpercentstmt->bindParam(':percent', $percent);
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

			$newadminstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
			$newadminstmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
			$newadminstmt->bindParam(':name', $name);
			$newadminstmt->bindParam(':admin_type', $admintype, PDO::PARAM_INT);
			$newadminstmt->execute();

			echo ".";
		}
	}
	echo ".";
}

echo "\n";

// re-enable data checks
$db->exec('SET unique_checks=1');
$db->exec('SET foreign_key_checks=1');

$db = null;
