<?php

define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGFIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

require_once('config/tote.conf.php');

if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {

	$connection = null;
	if (!empty($tote_conf['connectionString']))
		$connection = new Mongo($tote_conf['connectionString'], array('persist' => 'tote'));
	else
		$connection = new Mongo('mongodb://localhost:27017', array('persist' => 'tote'));
	$db = $connection->selectDB($tote_conf['database']);

	require_once(TOTE_CONTROLLERDIR . 'update.inc.php');

}
