<?php

$db = null;
try {
	$db = new PDO(sprintf('mysql:host=%s;dbname=%s', $tote_conf['hostname'], $tote_conf['sql_database']), $tote_conf['username'], $tote_conf['password']);
} catch (PDOException $e) {
	require_once(TOTE_CONTROLLERDIR . 'message.inc.php');
	display_message('Error connecting to database: ' . $e->getMessage());
	exit;
}

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
define('TOTE_TABLE_POOL_RECORDS', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_records');
define('TOTE_TABLE_POOL_RECORDS_VIEW', (!empty($tote_conf['prefix']) ? $tote_conf['prefix'] : '') . 'pool_records_view');