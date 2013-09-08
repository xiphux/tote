<?php

require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_in_pool.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_record.inc.php');
require_once(TOTE_INCLUDEDIR . 'mobile_browser.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_pot.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_payout_amounts.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

/**
 * pool controller
 *
 * display a pool record
 *
 * @param string $poolid pool id
 */
function display_pool($poolid = null)
{
	global $tpl, $tote_conf, $mysqldb;

	$user = user_logged_in();

	// get list of all pools
	$poolsresult = $mysqldb->query('SELECT pools.id, pools.name, pools.fee, seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id ORDER BY seasons.year DESC, name');
	$pools = $poolsresult->fetch_all(MYSQLI_ASSOC);

	$poolobj = null;
	if (empty($poolid)) {
		// most recent pool
		$poolobj = $pools[0];
	} else {
		// specified pool
		foreach ($pools as $pool) {
			if ($pool['id'] == $poolid) {
				$poolobj = $pool;
				break;
			}
		}
	}

	if (!$poolobj) {
		// we need some pool
		display_message("Pool not found");
		return;
	}

	// Find weeks that are open for betting
	$openweeks = get_open_weeks($poolobj['season']);
	$currentweek = array_search(true, $openweeks, true);
	$poolopen = ($currentweek !== false);

	$poolrecord = get_pool_record($poolobj['id']);
	$showties = false;
	foreach ($poolrecord as $record) {
		if (isset($record['ties']) && ($record['ties'] > 0)) {
			$showties = true;
			break;
		}
	}

	// check if logged in user is entered in this pool
	$entered = false;
	if ($user && user_in_pool($user['_id'], $poolobj['id']))
		$entered = true;

	$pot = get_pool_pot($poolobj['id']);
	$payoutamounts = get_pool_payout_amounts($poolobj['id']);

	// set data and display
	$mobile = mobile_browser();
	if ($mobile) {
		$tpl->assign('mobile', true);

		$mobileweeks = array();
		$totalweeks = count($openweeks);
		if (($currentweek === false) || ($currentweek == $totalweeks)) {
			$mobileweeks[] = $totalweeks - 2;
			$mobileweeks[] = $totalweeks - 1;
			$mobileweeks[] = $totalweeks;
		} else if ($currentweek === 1) {
			$mobileweeks[] = 1;
			$mobileweeks[] = 2;
			$mobileweeks[] = 3;
		} else {
			$mobileweeks[] = $currentweek - 1;
			$mobileweeks[] = $currentweek;
			$mobileweeks[] = $currentweek + 1;
		}

		$tpl->assign('mobileweeks', $mobileweeks);

		if (isset($_GET['full'])) {
			if ($_GET['full'] == '1') {
				$tpl->assign('forcefull', true);
			}
		} else if (isset($_COOKIE[TOTE_FULL_VERSION_COOKIE]) && ($_COOKIE[TOTE_FULL_VERSION_COOKIE] == 1)) {
			$tpl->assign('forcefull', true);
		}
	} else {
		if (!empty($user['timezone']) && ($user['timezone'] != 'America/New_York')) {
			$localtz = new DateTimeZone('America/New_York');
			$usertz = new DateTimeZone($user['timezone']);
			$localtime = new DateTime("now", $localtz);
			$usertime = new DateTime("now", $usertz);
			$offset = $usertz->getOffset($usertime) - $localtz->getOffset($localtime);
			$tpl->assign('timezoneoffset', $offset);
		}
	}

	http_headers();

	if (count($pools) > 1)
		$tpl->assign('allpools', $pools);
	if ($currentweek != false)
		$tpl->assign('currentweek', $currentweek);
	$tpl->assign('weeks', $openweeks);
	$tpl->assign('record', $poolrecord);
	$tpl->assign('showties', $showties);
	$tpl->assign('pool', $poolobj);
	if ($pot)
		$tpl->assign('pot', $pot);
	if (count($payoutamounts) > 0)
		$tpl->assign('payoutamounts', $payoutamounts);

	if ($user) {
		$tpl->assign('user', $user);
	}
	$tpl->assign('entered', $entered);
	$tpl->assign('poolopen', $poolopen);

	if (isset($tote_conf['links']) && is_array($tote_conf['links']) && (count($tote_conf['links']) > 0)) {
		$tpl->assign('links', $tote_conf['links']);
	}

	$tpl->display('pool.tpl');
}

