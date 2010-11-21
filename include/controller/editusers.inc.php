<?php

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

function display_editusers()
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$allusers = $users->find(array(), array('username', 'first_name', 'last_name', 'email', 'admin'));
	$userarray = array();
	foreach ($allusers as $u) {
		$userarray[] = $u;
	}
	usort($userarray, 'sort_users');

	$tpl->assign('allusers', $userarray);
	$tpl->display('editusers.tpl');
}
