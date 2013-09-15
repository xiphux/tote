<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_manager.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

function sort_displayname($a, $b)
{
	return strcasecmp($a['display_name'], $b['display_name']);
}

function sort_username($a, $b)
{
	return strcasecmp($a['username'], $b['username']);
}

function sort_email($a, $b)
{
	if (empty($a['email']) && empty($b['email']))
		return 0;

	if (empty($a['email']))
		return 1;

	if (empty($b['email']))
		return -1;

	return strcasecmp($a['email'], $b['email']);
}

function sort_role($a, $b)
{
	if (!isset($a['role']) && !isset($b['role']))
		return 0;

	if (!isset($a['role']))
		return 1;

	if (!isset($b['role']))
		return -1;

	return $b['role'] < $a['role'] ? 1 : -1;
}

function sort_created($a, $b)
{
	if (!isset($a['created']) && !isset($b['created']))
		return 0;

	if (!isset($a['created']))
		return 1;

	if (!isset($b['created']))
		return -1;

	$atime = strtotime($a['created']);
	$btime = strtotime($b['created']);

	if ($atime == $btime)
		return 0;

	return $atime < $btime ? 1 : -1;
}

function sort_login($a, $b)
{
	if (!isset($a['last_login']) && !isset($b['last_login']))
		return 0;

	if (!isset($a['last_login']))
		return 1;

	if (!isset($b['last_login']))
		return -1;

	$atime = strtotime($a['last_login']);
	$btime = strtotime($b['last_login']);

	if ($atime == $btime)
		return 0;

	return $atime < $btime ? 1 : -1;
}

function sort_passwordchange($a, $b)
{
	if (!isset($a['last_password_change']) && !isset($b['last_password_change']))
		return 0;

	if (!isset($a['last_password_change']))
		return 1;

	if (!isset($b['last_password_change']))
		return -1;

	$atime = strtotime($a['last_password_change']);
	$btime = strtotime($b['last_password_change']);

	if ($atime == $btime)
		return 0;

	return $atime < $btime ? 1 : -1;
}

/**
 * editusers
 *
 * central page for editing all users
 */
function display_editusers($order = 'name')
{
	global $tpl, $mysqldb;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!(user_is_admin($user) || user_is_manager($user))) {
		// need to be an admin or manager to edit users
		return redirect();
	}

	// get all users
	$usersresult = $mysqldb->query("SELECT id, username, (CASE WHEN (first_name IS NOT NULL AND last_name IS NOT NULL) THEN CONCAT(CONCAT(first_name,' '),last_name) WHEN first_name IS NOT NULL THEN first_name ELSE username END) AS display_name, email, role, created, last_login, last_password_change FROM " . TOTE_TABLE_USERS);
	$userarray = array();
	$tz = date_default_timezone_get();
	date_default_timezone_set('UTC');
	while ($u = $usersresult->fetch_assoc()) {
		if (!empty($u['created'])) {
			$u['created_local'] = get_local_datetime(strtotime($u['created']));
		}
		if (!empty($u['last_login'])) {
			$u['last_login_local'] = get_local_datetime(strtotime($u['last_login']));
		}
		if (!empty($u['last_password_change'])) {
			$u['last_password_change_local'] = get_local_datetime(strtotime($u['last_password_change']));
		}
		$userarray[] = $u;
	}
	date_default_timezone_set($tz);

	$usersresult->close();

	// sort
	switch ($order) {
		case 'name':
			usort($userarray, 'sort_displayname');
			break;

		case 'username':
			usort($userarray, 'sort_username');
			break;

		case 'email':
			usort($userarray, 'sort_email');
			break;

		case 'role':
			usort($userarray, 'sort_role');
			break;

		case 'created':
			usort($userarray, 'sort_created');
			break;

		case 'login':
			usort($userarray, 'sort_login');
			break;

		case 'passwordchange':
			usort($userarray, 'sort_passwordchange');
			break;

		default:
			usort($userarray, 'sort_displayname');
			$order = 'name';
			break;
	}

	// set data and display
	http_headers();
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('allusers', $userarray);
	$tpl->assign('order', $order);
	$tpl->assign('user', $user);
	$tpl->display('editusers.tpl');
}
