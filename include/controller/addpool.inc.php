<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');  // TODO remove this

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
	global $tpl;

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

	$pools = get_collection(TOTE_COLLECTION_POOLS);

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
			$existingpool = $pools->findOne(
				array('name' => $name, 'season' => (int)$season),
				array('name', 'season')
			);
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
		$tpl->assign('errors', $errors);
		if (!empty($name))
			$tpl->assign('name', $name);
		if (!empty($season))
			$tpl->assign('season', $season);
		if (!empty($fee))
			$tpl->assign('fee', $fee);
		$tpl->assign('csrftoken', $_SESSION['csrftoken']);
		$tpl->display('newpool.tpl');
	} else {
		$data = array(
			'name' => $name,
			'season' => (int)$season
		);
		if (!empty($fee)) {
			$fee = round((float)$fee, 2);
			if ($fee > 0) {
				$data['fee'] = $fee;
			}
		}

		// TODO: make payouts user entered rather than hardcoding
		$data['payout'] = array(
			array(
				'max' => 29,
				'percents' => array(
					0.75,
					0.15,
					0.10
				)
			),
			array(
				'min' => 30,
				'max' => 39,
				'percents' => array(
					0.75,
					0.15,
					0.10,
					0
				)
			),
			array(
				'min' => 40,
				'percents' => array(
					0.73,
					0.13,
					0.08,
					0.06,
					0
				)
			)
		);
		// end TODO

		$pools->insert($data);

		// go to the new pool
		redirect(array('p' => (string)$data['_id']));
	}
}
