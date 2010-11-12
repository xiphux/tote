<?php

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

switch($_GET['a']) {
	case 'pool':
		require_once('include/controller/pool.inc.php');
		break;
	case 'bet':
		require_once('include/controller/bet.inc.php');
		break;
	case 'addbet':
		require_once('include/controller/addbet.inc.php');
		break;
	case 'update':
		require_once('include/controller/update.inc.php');
		break;
	default:
		require_once('include/controller/pool.inc.php');
		break;
}
