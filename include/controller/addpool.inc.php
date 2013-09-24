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
	global $tpl, $db;

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
			$existingstmt = $db->prepare('SELECT pools.id FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=pools.season_id WHERE pools.name=:pool_name AND seasons.year=:year');
			$existingstmt->bindParam(':pool_name', $name);
			$existingstmt->bindParam(':year', $season, PDO::PARAM_INT);
			$existingid = null;
			$existingstmt->bindColumn(1, $existingid);
			$existingstmt->execute();
			$existingpool = $existingstmt->fetch(PDO::FETCH_ASSOC);
			$existingstmt = null;
			if ($existingpool || $existingid) {
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

		$db->beginTransaction();

		$newpoolstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOLS . ' (season_id, fee, name) VALUES ((SELECT id FROM seasons WHERE year=:year), :fee, :name)');
		$newpoolstmt->bindParam(':year', $season, PDO::PARAM_INT);
		$newpoolstmt->bindParam(':fee', $fee);
		$newpoolstmt->bindParam(':name', $name);
		$newpoolstmt->execute();

		$poolid = $db->lastInsertId();

		$newpoolstmt = null;

		// TODO: make payouts user entered rather than hardcoding
		$newpayoutstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUTS . ' (pool_id, minimum, maximum) VALUES (:pool_id, :minimum, :maximum)');
		$newpayoutstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);

		// 0-29: 75%, 15%, 10%
		$min = null;
		$max = 29;
		$newpayoutstmt->bindParam(':minimum', $min, PDO::PARAM_INT);
		$newpayoutstmt->bindParam(':maximum', $max, PDO::PARAM_INT);
		$newpayoutstmt->execute();
		$payoutid = $db->lastInsertId();

		$newpercentstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (:payout_id1, :place1, :percent1), (:payout_id2, :place2, :percent2), (:payout_id3, :place3, :percent3)');
		$place1 = 1;
		$percent1 = 0.75;
		$place2 = 2;
		$percent2 = 0.15;
		$place3 = 3;
		$percent3 = 0.10;
		$newpercentstmt->bindParam(':payout_id1', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place1', $place1, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent1', $percent1);
		$newpercentstmt->bindParam(':payout_id2', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place2', $place2, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent2', $percent2);
		$newpercentstmt->bindParam(':payout_id3', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place3', $place3, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent3', $percent3);
		$newpercentstmt->execute();
		$newpercentstmt = null;

		// 30-39: 75%, 15%, 10%, entry fee
		$min = 30;
		$max = 39;
		$newpayoutstmt->bindParam(':minimum', $min, PDO::PARAM_INT);
		$newpayoutstmt->bindParam(':maximum', $max, PDO::PARAM_INT);
		$newpayoutstmt->execute();
		$payoutid = $db->lastInsertId();

		$newpercentstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (:payout_id1, :place1, :percent1), (:payout_id2, :place2, :percent2), (:payout_id3, :place3, :percent3), (:payout_id4, :place4, :percent4)');
		$place1 = 1;
		$percent1 = 0.75;
		$place2 = 2;
		$percent2 = 0.15;
		$place3 = 3;
		$percent3 = 0.10;
		$place4 = 4;
		$percent4 = 0;
		$newpercentstmt->bindParam(':payout_id1', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place1', $place1, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent1', $percent1);
		$newpercentstmt->bindParam(':payout_id2', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place2', $place2, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent2', $percent2);
		$newpercentstmt->bindParam(':payout_id3', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place3', $place3, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent3', $percent3);
		$newpercentstmt->bindParam(':payout_id4', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place4', $place4, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent4', $percent4);
		$newpercentstmt->execute();
		$newpercentstmt = null;

		// 40+: 73%, 13%, 8%, 6%, entry fee
		$min = 40;
		$max = null;
		$newpayoutstmt->bindParam(':minimum', $min, PDO::PARAM_INT);
		$newpayoutstmt->bindParam(':maximum', $max, PDO::PARAM_INT);
		$newpayoutstmt->execute();
		$payoutid = $db->lastInsertId();

		$newpercentstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_PAYOUT_PERCENTS . ' (payout_id, place, percent) VALUES (:payout_id1, :place1, :percent1), (:payout_id2, :place2, :percent2), (:payout_id3, :place3, :percent3), (:payout_id4, :place4, :percent4), (:payout_id5, :place5, :percent5)');
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
		$newpercentstmt->bindParam(':payout_id1', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place1', $place1, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent1', $percent1);
		$newpercentstmt->bindParam(':payout_id2', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place2', $place2, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent2', $percent2);
		$newpercentstmt->bindParam(':payout_id3', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place3', $place3, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent3', $percent3);
		$newpercentstmt->bindParam(':payout_id4', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place4', $place4, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent4', $percent4);
		$newpercentstmt->bindParam(':payout_id5', $payoutid, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':place5', $place5, PDO::PARAM_INT);
		$newpercentstmt->bindParam(':percent5', $percent5);
		$newpercentstmt->execute();
		$newpercentstmt = null;

		$newpayoutstmt = null;

		// end TODO

		$db->commit();

		// go to the new pool
		redirect(array('p' => $poolid));
	}
}
