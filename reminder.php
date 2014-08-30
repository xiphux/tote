<?php
/**
 * Tote
 *
 * Reminder mailer script
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package Tote
 */

// define include directories
define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGDIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');

require_once(TOTE_CONFIGDIR . 'tote.conf.php');
require_once(TOTE_INCLUDEDIR . 'send_reminders.inc.php');

// only if reminders are turned on in the config
if (!empty($tote_conf['reminders']) && ($tote_conf['reminders'] == true)) {

	date_default_timezone_set('UTC');

	// create Smarty
	require_once($tote_conf['smarty'] . 'Smarty.class.php');
	$tpl = new Smarty();
	$tpl->plugins_dir[] = TOTE_INCLUDEDIR . 'smartyplugins';

	require_once(TOTE_INCLUDEDIR . 'db.inc.php');

	send_reminders();

}
