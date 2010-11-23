<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');

function display_editpool($poolID)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (!user_is_admin($user)) {
		return redirect();
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	$allusers = $users->find(array(), array('username', 'first_name', 'last_name', 'email'));
	$availableusers = array();
	foreach ($allusers as $eachuser) {
		$availableusers[(string)$eachuser['_id']] = $eachuser;
	}

	$poolusers = array();
	foreach ($pool['entries'] as $entrant) {
		$poolusers[(string)$entrant['user']] = $availableusers[(string)$entrant['user']];
		if (!empty($entrant['bets']) && (count($entrant['bets']) > 0)) {
			$poolusers[(string)$entrant['user']]['hasbets'] = true;
		}
		unset($availableusers[(string)$entrant['user']]);
	}
	
	if (count($poolusers) > 0) {
		uasort($poolusers, 'sort_users');
		$tpl->assign('poolusers', $poolusers);
	}
	if (count($availableusers) > 0) {
		uasort($availableusers, 'sort_users');
		$tpl->assign('availableusers', $availableusers);
	}
	$tpl->assign('pool', $pool);
	$tpl->display('editpool.tpl');

}
