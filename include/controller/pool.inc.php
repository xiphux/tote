<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_in_pool.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_open_weeks.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_record.inc.php');

/**
 * sort_pools
 *
 * sort pools
 *
 * @param array $a first sort pool
 * @param array $b second sort pool
 */
function sort_pool($a, $b)
{
	// first sort by year descending
	if ($a['season'] != $b['season'])
		return ($a['season'] > $b['season'] ? -1 : 1);

	// then fall back on alphabetical name
	return strcasecmp($a['name'], $b['name']);
}

/**
 * pool controller
 *
 * display a pool record
 *
 * @param string $poolID pool id
 */
function display_pool($poolID = null)
{
	global $tpl;

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$games = get_collection(TOTE_COLLECTION_GAMES);;

	$user = user_logged_in();

	$poolobj = null;
	if (empty($poolID)) {
		// no pool specified - try to find the most sensible pool
		$allpools = $pools->find(
			array(),
			array('name', 'season')
		)->sort(
			array('season' => -1, 'name' => 1)
		);

		if ($user) {
			$newestseason = null;
			foreach ($allpools as $eachpool) {

				if ($newestseason == null) {
					// because of the sort order the very first
					// pool has the most recent available season
					$newestseason = $eachpool['season'];
				}
				if ($newestseason != $eachpool['season']) {
					// this is a pool older than the most recent
					// season - stop searching
					break;
				}
				if (user_in_pool($user['_id'], $eachpool['_id'])) {
					// the user is in this pool
					// default to showing this one
					$poolobj = $eachpool;
					break;
				}
			}
		}

		if ($poolobj == null) {
			// didn't find anything - default to first pool in sort order
			$allpools->reset();
			$poolobj = $allpools->getNext();
		}
	} else {
		// we specified a pool
		$poolobj = $pools->findOne(
			array('_id' => new MongoId($poolID)),
			array('name', 'season')
		);
	}

	if (!$poolobj) {
		// we need some pool
		echo "Pool not found";
		return;
	}

	// Find weeks that are open for betting
	$openweeks = get_open_weeks($poolobj['season']);
	$currentweek = array_search(true, $openweeks, true);
	$poolopen = ($currentweek !== false);

	$poolrecord = get_pool_record($poolobj['_id']);

	// check if logged in user is entered in this pool
	$entered = false;
	if ($user && user_in_pool($user['_id'], $poolobj['_id']))
		$entered = true;

	// get list of all pools
	$allpoolcollect = $pools->find(array(), array('season', 'name'));
	$allpools = array();
	foreach ($allpoolcollect as $p) {
		$allpools[] = $p;
	}
	// and sort them
	usort($allpools, 'sort_pool');

	// set data and display
	if (count($allpools) > 1)
		$tpl->assign('allpools', $allpools);
	if ($currentweek != false)
		$tpl->assign('currentweek', $currentweek);
	$tpl->assign('weeks', $openweeks);
	$tpl->assign('record', $poolrecord);
	$tpl->assign('pool', $poolobj);

	if ($user) {
		$tpl->assign('user', $user);
	}
	$tpl->assign('entered', $entered);
	$tpl->assign('poolopen', $poolopen);

	$tpl->display('pool.tpl');
}

