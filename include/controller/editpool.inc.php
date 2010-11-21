<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');

function sort_users($a, $b)
{
	$aname = $a['username'];
	$bname = $b['username'];

	if (!empty($a['first_name'])) {
		$aname = $a['first_name'];
		if (!empty($a['last_name'])) {
			$aname .= ' ' . $a['last_name'];
		}
	}

	if (!empty($b['first_name'])) {
		$bname = $b['first_name'];
		if (!empty($b['last_name'])) {
			$bname .= ' ' . $b['last_name'];
		}
	}

	return strcasecmp($aname, $bname);
}

function display_editpool($poolID)
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		return redirect();
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);
	$games = get_collection(TOTE_COLLECTION_GAMES);
	$teams = get_collection(TOTE_COLLECTION_TEAMS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		return redirect();
	}

	if (empty($user['admin'])) {
		return redirect();
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

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
