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
define('TOTE_CONFIGDIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

require_once(TOTE_CONFIGDIR . 'tote.conf.php');

// only allow from command line without login
if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {

	// create Smarty
	require_once('lib/smarty/libs/Smarty.class.php');
	$tpl = new Smarty();
	$tpl->plugins_dir[] = TOTE_INCLUDEDIR . 'smartyplugins';
	
	require_once(TOTE_INCLUDEDIR . 'db.inc.php');
	
	date_default_timezone_set('UTC');

	// use update controller
	require_once(TOTE_CONTROLLERDIR . 'update.inc.php');

}
