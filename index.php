<?php
/**
 * Tote
 *
 * Index
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package Tote
 */

// session for user logins
session_start();

// define include directories
define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGFIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

// include config file
require_once('config/tote.conf.php');

// create MongoDB connection
$connection = null;
if (!empty($tote_conf['connectionString']))
	$connection = new Mongo($tote_conf['connectionString'], array('persist' => 'tote'));
else
	$connection = new Mongo('mongodb://localhost:27017', array('persist' => 'tote'));
$db = $connection->selectDB($tote_conf['database']);

// create Smarty
require_once($tote_conf['smarty'] . 'Smarty.class.php');
$tpl = new Smarty();
$tpl->plugins_dir[] = TOTE_INCLUDEDIR . 'smartyplugins';

// work with UTC timestamps internally
date_default_timezone_set('UTC');

// a= parameter specifies the action to perform
switch((empty($_GET['a']) ? '' : $_GET['a'])) {

	// bet - page for user to place a bet
	case 'bet':
		require_once(TOTE_CONTROLLERDIR . 'bet.inc.php');
		display_bet((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['w']) ? null : $_GET['w']));
		break;

	// addbet - process the user's submitted bet
	case 'addbet':
		require_once(TOTE_CONTROLLERDIR . 'addbet.inc.php');
		display_addbet(
			(empty($_POST['p']) ? null : $_POST['p']),
			(empty($_POST['w']) ? null : $_POST['w']),
			(empty($_POST['t']) ? null : $_POST['t']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;

	// editbets - edit a user's bets
	case 'editbets':
		require_once(TOTE_CONTROLLERDIR . 'editbets.inc.php');
		display_editbets((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['u']) ? null : $_GET['u']));
		break;

	
	// savebets - save the changes to a user's bet
	case 'savebets':
		require_once(TOTE_CONTROLLERDIR . 'savebets.inc.php');
		display_savebets(
			(empty($_POST['p']) ? null : $_POST['p']),
			(empty($_POST['u']) ? null : $_POST['u']),
			(empty($_POST['week']) ? null : $_POST['week']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;


	// update - update the game schedule and scores
	case 'update':
		require_once(TOTE_CONTROLLERDIR . 'update.inc.php');
		break;

	
	// newpool - form to enter a new pool
	case 'newpool':
		require_once(TOTE_CONTROLLERDIR . 'newpool.inc.php');
		display_newpool();
		break;

	
	// addpool - validates and adds the new pool to the database
	case 'addpool':
		require_once(TOTE_CONTROLLERDIR . 'addpool.inc.php');
		display_addpool(
			(empty($_POST['name']) ? null : $_POST['name']),
			(empty($_POST['season']) ? null : $_POST['season']),
			(empty($_POST['fee']) ? null : $_POST['fee']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;

	
	// editpool - edit settings for pool such as entrants and name
	case 'editpool':
		require_once(TOTE_CONTROLLERDIR . 'editpool.inc.php');
		display_editpool((empty($_GET['p']) ? null : $_GET['p']));
		break;

	// ajaxeditpool - used to asynchronously save changes when editing pool
	case 'ajaxeditpool':
		require_once(TOTE_CONTROLLERDIR . 'ajaxeditpool.inc.php');
		display_ajaxeditpool(
			(empty($_POST['p']) ? null : $_POST['p']),
			(empty($_POST['m']) ? null : $_POST['m']),
			(empty($_POST['u']) ? null : $_POST['u']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;


	// setpoolname - used to save the changes to a pool's name
	case 'setpoolname':
		require_once(TOTE_CONTROLLERDIR . 'setpoolname.inc.php');
		display_setpoolname(
			(empty($_POST['p']) ? null : $_POST['p']),
			(empty($_POST['poolname']) ? null : $_POST['poolname']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;


	// deletepool - deletes the entire pool
	case 'deletepool':
		require_once(TOTE_CONTROLLERDIR . 'deletepool.inc.php');
		display_deletepool(
			(empty($_GET['p']) ? null : $_GET['p']),
			(empty($_GET['csrftoken']) ? null : $_GET['csrftoken'])
		);
		break;


	// atom - get the pool event history in atom feed format
	case 'atom':
		require_once(TOTE_CONTROLLERDIR . 'feed.inc.php');
		display_feed('atom', (empty($_GET['p']) ? null : $_GET['p']));
		break;


	// rss - get the pool event history in rss feed format
	case 'rss':
		require_once(TOTE_CONTROLLERDIR . 'feed.inc.php');
		display_feed('rss', (empty($_GET['p']) ? null : $_GET['p']));
		break;


	// history - get the pool event history page
	case 'history':
		require_once(TOTE_CONTROLLERDIR . 'feed.inc.php');
		display_feed((empty($_GET['o']) ? 'html' : $_GET['o']), (empty($_GET['p']) ? null : $_GET['p']));
		break;


	// editusers - list of users to edit/add/delete
	case 'editusers':
		require_once(TOTE_CONTROLLERDIR . 'editusers.inc.php');
		display_editusers();
		break;

	
	// edituser - edit a single user
	case 'edituser':
		require_once(TOTE_CONTROLLERDIR . 'edituser.inc.php');
		display_edituser(empty($_GET['u']) ? null : $_GET['u']);
		break;

	
	// saveuser - save changes to a single user
	case 'saveuser':
		require_once(TOTE_CONTROLLERDIR . 'saveuser.inc.php');
		display_saveuser(
			(empty($_POST['u']) ? null : $_POST['u']),
			(empty($_POST['firstname']) ? null : $_POST['firstname']),
			(empty($_POST['lastname']) ? null : $_POST['lastname']),
			(empty($_POST['email']) ? null : $_POST['email']),
			(empty($_POST['admin']) ? null : $_POST['admin']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;

	
	// newuser - form to add a new user
	case 'newuser':
		require_once(TOTE_CONTROLLERDIR . 'newuser.inc.php');
		display_newuser();
		break;


	// adduser - saves a new user into database
	case 'adduser':
		require_once(TOTE_CONTROLLERDIR . 'adduser.inc.php');
		display_adduser(
			(empty($_POST['username']) ? null : $_POST['username']),
			(empty($_POST['firstname']) ? null : $_POST['firstname']),
			(empty($_POST['lastname']) ? null : $_POST['lastname']),
			(empty($_POST['email']) ? null : $_POST['email']),
			(empty($_POST['password']) ? null : $_POST['password']),
			(empty($_POST['password2']) ? null : $_POST['password2']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;


	// deleteuser - delete a user
	case 'deleteuser':
		require_once(TOTE_CONTROLLERDIR . 'deleteuser.inc.php');
		display_deleteuser(
			(empty($_GET['u']) ? null : $_GET['u']),
			(empty($_GET['csrftoken']) ? null : $_GET['csrftoken'])
		);
		break;


	// login - show login page
	case 'login':
		require_once(TOTE_CONTROLLERDIR . 'login.inc.php');
		display_login();
		break;


	// finishlogin - validate login credentials
	case 'finishlogin':
		require_once(TOTE_CONTROLLERDIR . 'finishlogin.inc.php');
		display_finishlogin((empty($_POST['username']) ? null : $_POST['username']), (empty($_POST['password']) ? null : $_POST['password']));
		break;


	// logout - log user out of the system
	case 'logout':
		require_once(TOTE_CONTROLLERDIR . 'logout.inc.php');
		display_logout();
		break;


	// editprefs - show user preferences edit page
	case 'editprefs':
		require_once(TOTE_CONTROLLERDIR . 'editprefs.inc.php');
		display_editprefs();
		break;


	// saveprefs - save changes to user preferences page
	case 'saveprefs':
		require_once(TOTE_CONTROLLERDIR . 'saveprefs.inc.php');
		display_saveprefs(
			(empty($_POST['timezone']) ? null : $_POST['timezone']),
			(empty($_POST['reminder']) ? false : $_POST['reminder']),
			(empty($_POST['remindertime']) ? null : $_POST['remindertime']),
			(empty($_POST['resultnotification']) ? null : $_POST['resultnotification']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;


	// changepass - show change password form
	case 'changepass':
		require_once(TOTE_CONTROLLERDIR . 'changepass.inc.php');
		display_changepass();
		break;


	// finishchangepass - save changed password in the database
	case 'finishchangepass':
		require_once(TOTE_CONTROLLERDIR . 'finishchangepass.inc.php');
		display_finishchangepass(
			(empty($_POST['oldpassword']) ? null : $_POST['oldpassword']),
			(empty($_POST['newpassword']) ? null : $_POST['newpassword']),
			(empty($_POST['newpassword2']) ? null : $_POST['newpassword2']),
			(empty($_POST['csrftoken']) ? null : $_POST['csrftoken'])
		);
		break;


	// recoverpass - present password recovery form
	case 'recoverpass':
		require_once(TOTE_CONTROLLERDIR . 'recoverpass.inc.php');
		display_recoverpass();
		break;


	// finishrecoverpass - generate recovery token and email user
	case 'finishrecoverpass':
		require_once(TOTE_CONTROLLERDIR . 'finishrecoverpass.inc.php');
		display_finishrecoverpass((empty($_POST['email']) ? null : $_POST['email']));
		break;


	// resetpass - reset user password with a recovery token
	case 'resetpass':
		require_once(TOTE_CONTROLLERDIR . 'resetpass.inc.php');
		display_resetpass((empty($_GET['k']) ? null : $_GET['k']));
		break;


	// finishresetpass - insert reset password into database
	case 'finishresetpass':
		require_once(TOTE_CONTROLLERDIR . 'finishresetpass.inc.php');
		display_finishresetpass((empty($_POST['key']) ? null : $_POST['key']), (empty($_POST['newpassword']) ? null : $_POST['newpassword']), (empty($_POST['newpassword2']) ? null : $_POST['newpassword2']));
		break;


	// rules - display the pool rules page
	case 'rules':
		require_once(TOTE_CONTROLLERDIR . 'rules.inc.php');
		display_rules((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['o']) ? 'html' : $_GET['o']));
		break;

	
	// schedule - display the game schedule for a week
	case 'schedule':
		require_once(TOTE_CONTROLLERDIR . 'schedule.inc.php');
		display_schedule(
			(empty($_GET['y']) ? null : $_GET['y']),
			(empty($_GET['w']) ? null : $_GET['w']),
			(empty($_GET['o']) ? 'html' : $_GET['o'])
		);
		break;


	// pool (default) - display a pool
	default:
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Pragma: no-cache");
		require_once(TOTE_CONTROLLERDIR . 'pool.inc.php');
		display_pool(empty($_GET['p']) ? null : $_GET['p']);
		break;
}
