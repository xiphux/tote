<?php
/**
 * Tote
 *
 * Background score update script
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package Tote
 */

// define include directories
define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGFIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

require_once('config/tote.conf.php');

// only allow from command line without login
if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {

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

	// use update controller
	require_once(TOTE_CONTROLLERDIR . 'update.inc.php');

}
