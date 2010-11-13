<?php

session_start();

define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGFIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

require_once('config/tote.conf.php');

$connection = null;
if (!empty($tote_conf['connectionString']))
	$connection = new Mongo($tote_conf['connectionString']);
else
	$connection = new Mongo();
$db = $connection->selectDB($tote_conf['database']);

require_once($tote_conf['smarty'] . 'Smarty.class.php');
$tpl = new Smarty();

date_default_timezone_set('America/New_York');

switch((empty($_GET['a']) ? '' : $_GET['a'])) {
	case 'bet':
		require_once(TOTE_CONTROLLERDIR . 'bet.inc.php');
		display_bet((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['w']) ? null : $_GET['w']));
		break;
	case 'addbet':
		require_once(TOTE_CONTROLLERDIR . 'addbet.inc.php');
		display_addbet((empty($_GET['p']) ? null : $_GET['p']), (empty($_GET['w']) ? null : $_GET['w']), (empty($_GET['t']) ? null : $_GET['t']));
		break;
	case 'update':
		require_once(TOTE_CONTROLLERDIR . 'update.inc.php');
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
