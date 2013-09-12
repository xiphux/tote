<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_seasons.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('ADDPOOL_HEADER', 'Add A New Pool');

/**
 * addpool controller
 *
 * add a pool to the database
 *
 * @param string $name pool name
 * @param string $season season year (start year)
 * @param string $fee pool fee
 * @param string $csrftoken CSRF request token
 */
function display_addpool($name, $season, $fee, $csrftoken)
{
	global $tpl, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// must be logged in to add a user
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to add a user
		return redirect();
	}
	
	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", ADDPOOL_HEADER);
		return;
	}

	$errors = array();
	if (empty($name)) {
		// need a pool name
		$errors[] = "Pool name is required";
	}
	if (empty($season)) {
		// need a season year
		$errors[] = "Season is required";
	} else {
		if (!is_numeric($season)) {
			$errors[] = "Season must be a year";
		} else {
			$existingstmt = $mysqldb->prepare('SELECT pools.id FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=pools.season_id WHERE pools.name=? AND seasons.year=?');
			$intseason = (int)$season;
			$existingstmt->bind_param('si', $name, $intseason);
			$existingid = null;
			$existingstmt->bind_result($existingid);
			$existingstmt->execute();
			$existingpool = $existingstmt->fetch();
			$existingstmt->close();
			if ($existingpool) {
				// don't allow duplicate pool names - not for technical reasons,
				// just because it makes no sense since you can't differentiate
				$errors[] = "A pool with that name already exists";
			}
		}
	}
	if (!empty($fee)) {
		if (!is_numeric($fee)) {
			$errors[] = "Fee must be a dollar amount";
		} else if ((float)$fee < 0) {
			$errors[] = "Fee cannot be negative";
		}
	}

	if (count($errors) > 0) {
		http_headers();
		$tpl->assign('errors', $errors);
		if (!empty($name))
			$tpl->assign('name', $name);
		if (!empty($season))
			$tpl->assign('season', $season);
		if (!empty($fee))
			$tpl->assign('fee', $fee);
		$seasons = get_seasons();
		rsort($seasons);
		$tpl->assign('seasons', $seasons);
		$tpl->assign('csrftoken', $_SESSION['csrftoken']);
		$tpl->display('newpool.tpl');
	} else {
		if (!empty($fee)) {
			$fee = round((float)$fee, 2);
			if ($fee < 0) {
				$fee = null;
			}
		} else
			$fee = null;

		$season = (int)$season;

		$newpoolstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOLS . ' (season_id, fee, name) VALUES ((SELECT id FROM seasons WHERE year=?),?,?)');
		$newpoolstmt->bind_param('ids', $season, $fee, $name);
		$newpoolstmt->execute();

		$poolid = $mysqldb->insert_id;

		$newpoolstmt->close();

		// TODO: make payouts user entered rather than hardcoding
		$newpayoutstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUTS . ' (pool_id, minimum, maximum) VALUES (?, ?, ?)');

		// 0-29: 75%, 15%, 10%
		$min = null;
		$max = 29;
		$newpayoutstmt->bind_param('iii', $poolid, $min, $max);
		$newpayoutstmt->execute();
		$payoutid = $mysqldb->insert_id;

		$newpercentstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)');
		$place1 = 1;
		$percent1 = 0.75;
		$place2 = 2;
		$percent2 = 0.15;
		$place3 = 3;
		$percent3 = 0.10;
		$newpercentstmt->bind_param('iidiidiid', $payoutid, $place1, $percent1, $payoutid, $place2, $percent2, $payoutid, $place3, $percent3);
		$newpercentstmt->execute();
		$newpercentstmt->close();

		// 30-39: 75%, 15%, 10%, entry fee
		$min = 30;
		$max = 39;
		$newpayoutstmt->bind_param('iii', $poolid, $min, $max);
		$newpayoutstmt->execute();
		$payoutid = $mysqldb->insert_id;

		$newpercentstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?), (?, ?, ?)');
		$place1 = 1;
		$percent1 = 0.75;
		$place2 = 2;
		$percent2 = 0.15;
		$place3 = 3;
		$percent3 = 0.10;
		$place4 = 4;
		$percent4 = 0;
		$newpercentstmt->bind_param('iidiidiidiid', $payoutid, $place1, $percent1, $payoutid, $place2, $percent2, $payoutid, $place3, $percent3, $payoutid, $place4, $percent4);
		$newpercentstmt->execute();
		$newpercentstmt->close();

		// 40+: 73%, 13%, 8%, 6%, entry fee
		$min = 40;
		$max = null;
		$newpayoutstmt->bind_param('iii', $poolid, $min, $max);
		$newpayoutstmt->execute();
		$payoutid = $mysqldb->insert_id;

		$newpercentstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?), (?, ?, ?), (?, ?, ?)');
		$place1 = 1;
		$percent1 = 0.73;
		$place2 = 2;
		$percent2 = 0.13;
		$place3 = 3;
		$percent3 = 0.08;
		$place4 = 4;
		$percent4 = 0.06;
		$place5 = 5;
		$percent5 = 0;
		$newpercentstmt->bind_param('iidiidiidiidiid', $payoutid, $place1, $percent1, $payoutid, $place2, $percent2, $payoutid, $place3, $percent3, $payoutid, $place4, $percent4, $payoutid, $place5, $percent5);
		$newpercentstmt->execute();
		$newpercentstmt->close();

		$newpayoutstmt->close();

		// end TODO

		// go to the new pool
		redirect(array('p' => $poolid));
	}
}
