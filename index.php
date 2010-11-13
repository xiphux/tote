<?php

session_start();

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
		require_once('include/controller/bet.inc.php');
		break;
	case 'addbet':
		require_once('include/controller/addbet.inc.php');
		break;
	case 'update':
		require_once('include/controller/update.inc.php');
		break;
	case 'login':
		if (isset($_SESSION['user']))
			header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		else
			require_once('include/controller/login.inc.php');
		break;
	case 'finishlogin':
		if (isset($_SESSION['user']))
			header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		else
			require_once('include/controller/finishlogin.inc.php');
		break;
	case 'logout':
		unset($_SESSION['user']);
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		break;
	case 'changepass':
		if (!isset($_SESSION['user']))
			header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		else
			require_once('include/controller/changepass.inc.php');
		break;
	case 'finishchangepass':
		if (!isset($_SESSION['user']))
			header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		else
			require_once('include/controller/finishchangepass.inc.php');
		break;
	default:
		require_once('include/controller/pool.inc.php');
		break;
}
