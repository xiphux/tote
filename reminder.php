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

	// create MySQL connection
	$db = new PDO(sprintf('mysql:host=%s;dbname=%s', $tote_conf['hostname'], $tote_conf['sql_database']), $tote_conf['username'], $tote_conf['password']);

	// define MySQL tables
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


	$tpl = new Smarty;

	send_reminders();

}
