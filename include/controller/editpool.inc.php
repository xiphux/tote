<?php

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

	return strcmp($aname, $bname);
}

function display_editpool($poolID)
{
	global $db, $tote_conf, $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$poolcol = 'pools';
	$usercol = 'users';
	$gamecol = 'games';
	$teamcol = 'teams';
	if (!empty($tote_conf['namespace'])) {
		$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
		$gamecol = $tote_conf['namespace'] . '.' . $gamecol;
		$teamcol = $tote_conf['namespace'] . '.' . $teamcol;
	}

	$pools = $db->selectCollection($poolcol);
	$users = $db->selectCollection($usercol);
	$games = $db->selectCollection($gamecol);
	$teams = $db->selectCollection($teamcol);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
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
