<?php

session_start();

define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGFIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

require_once('config/tote.conf.php');
require_once($tote_conf['smarty'] . 'Smarty.class.php');

$connection = null;
if (!empty($tote_conf['connectionString']))
	$connection = new Mongo($tote_conf['connectionString'], array('persist' => 'tote'));
else
	$connection = new Mongo('mongodb://localhost:27017', array('persist' => 'tote'));
$db = $connection->selectDB($tote_conf['database']);

require_once($tote_conf['smarty'] . 'Smarty.class.php');
$tpl = new Smarty();

date_default_timezone_set('UTC');

switch((empty($_GET['a']) ? '' : $_GET['a'])) {

	case 'bet':
		require_once(TOTE_CONTROLLERDIR . 'bet.inc.php');
		display_bet((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['w']) ? null : $_GET['w']));
		break;


	case 'addbet':
		require_once(TOTE_CONTROLLERDIR . 'addbet.inc.php');
		display_addbet((empty($_POST['p']) ? null : $_POST['p']), (empty($_POST['w']) ? null : $_POST['w']), (empty($_POST['t']) ? null : $_POST['t']));
		break;


	case 'editbets':
		require_once(TOTE_CONTROLLERDIR . 'editbets.inc.php');
		display_editbets((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['u']) ? null : $_GET['u']));
		break;


	case 'savebets':
		require_once(TOTE_CONTROLLERDIR . 'savebets.inc.php');
		display_savebets((empty($_POST['p']) ? null : $_POST['p']), (empty($_POST['u']) ? null : $_POST['u']), (empty($_POST['week']) ? null : $_POST['week']));
		break;


	case 'update':
		require_once(TOTE_CONTROLLERDIR . 'update.inc.php');
		break;

	
	case 'editpool':
		require_once(TOTE_CONTROLLERDIR . 'editpool.inc.php');
		display_editpool((empty($_GET['p']) ? null : $_GET['p']));
		break;


	case 'ajaxeditpool':
		require_once(TOTE_CONTROLLERDIR . 'ajaxeditpool.inc.php');
		display_ajaxeditpool((empty($_POST['p']) ? null : $_POST['p']), (empty($_POST['m']) ? null : $_POST['m']), (empty($_POST['u']) ? null : $_POST['u']));
		break;


	case 'setpoolname':
		require_once(TOTE_CONTROLLERDIR . 'setpoolname.inc.php');
		display_setpoolname((empty($_POST['p']) ? null : $_POST['p']), (empty($_POST['poolname']) ? null : $_POST['poolname']));
		break;


	case 'atom':
		require_once(TOTE_CONTROLLERDIR . 'feed.inc.php');
		display_feed('atom', (empty($_GET['p']) ? null : $_GET['p']));
		break;


	case 'rss':
		require_once(TOTE_CONTROLLERDIR . 'feed.inc.php');
		display_feed('rss', (empty($_GET['p']) ? null : $_GET['p']));
		break;

	
	case 'history':
		require_once(TOTE_CONTROLLERDIR . 'feed.inc.php');
		display_feed('html', (empty($_GET['p']) ? null : $_GET['p']));
		break;


	case 'login':
		require_once(TOTE_CONTROLLERDIR . 'login.inc.php');
		display_login();
		break;


	case 'finishlogin':
		require_once(TOTE_CONTROLLERDIR . 'finishlogin.inc.php');
		display_finishlogin((empty($_POST['username']) ? null : $_POST['username']), (empty($_POST['password']) ? null : $_POST['password']));
		break;


	case 'logout':
		unset($_SESSION['user']);
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		break;


	case 'editprefs':
		require_once(TOTE_CONTROLLERDIR . 'editprefs.inc.php');
		display_editprefs();
		break;


	case 'saveprefs':
		require_once(TOTE_CONTROLLERDIR . 'saveprefs.inc.php');
		display_saveprefs(
			(empty($_POST['timezone']) ? null : $_POST['timezone']),
			(empty($_POST['reminder']) ? false : $_POST['reminder']),
			(empty($_POST['remindertime']) ? null : $_POST['remindertime'])
		);
		break;


	case 'changepass':
		require_once(TOTE_CONTROLLERDIR . 'changepass.inc.php');
		display_changepass();
		break;


	case 'finishchangepass':
		require_once(TOTE_CONTROLLERDIR . 'finishchangepass.inc.php');
		display_finishchangepass((empty($_POST['oldpassword']) ? null : $_POST['oldpassword']), (empty($_POST['newpassword']) ? null : $_POST['newpassword']), (empty($_POST['newpassword2']) ? null : $_POST['newpassword2']));
		break;


	case 'recoverpass':
		require_once(TOTE_CONTROLLERDIR . 'recoverpass.inc.php');
		display_recoverpass();
		break;


	case 'finishrecoverpass':
		require_once(TOTE_CONTROLLERDIR . 'finishrecoverpass.inc.php');
		display_finishrecoverpass((empty($_POST['email']) ? null : $_POST['email']));
		break;


	case 'resetpass':
		require_once(TOTE_CONTROLLERDIR . 'resetpass.inc.php');
		display_resetpass((empty($_GET['k']) ? null : $_GET['k']));
		break;


	case 'finishresetpass':
		require_once(TOTE_CONTROLLERDIR . 'finishresetpass.inc.php');
		display_finishresetpass((empty($_POST['key']) ? null : $_POST['key']), (empty($_POST['newpassword']) ? null : $_POST['newpassword']), (empty($_POST['newpassword2']) ? null : $_POST['newpassword2']));
		break;


	default:
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Pragma: no-cache");
		require_once(TOTE_CONTROLLERDIR . 'pool.inc.php');
		display_pool(empty($_GET['p']) ? null : $_GET['p']);
		break;
}
