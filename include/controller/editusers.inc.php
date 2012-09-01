<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');

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

	if ($a['created']->sec == $b['created']->sec)
		return 0;

	return $a['created']->sec < $b['created']->sec ? 1 : -1;
}

function sort_login($a, $b)
{
	if (!isset($a['lastlogin']) && !isset($b['lastlogin']))
		return 0;

	if (!isset($a['lastlogin']))
		return 1;

	if (!isset($b['lastlogin']))
		return -1;

	if ($a['lastlogin']->sec == $b['lastlogin']->sec)
		return 0;

	return $a['lastlogin']->sec < $b['lastlogin']->sec ? 1 : -1;
}

function sort_passwordchange($a, $b)
{
	if (!isset($a['lastpasswordchange']) && !isset($b['lastpasswordchange']))
		return 0;

	if (!isset($a['lastpasswordchange']))
		return 1;

	if (!isset($b['lastpasswordchange']))
		return -1;

	if ($a['lastpasswordchange']->sec == $b['lastpasswordchange']->sec)
		return 0;

	return $a['lastpasswordchange']->sec < $b['lastpasswordchange']->sec ? 1 : -1;
}

/**
 * editusers
 *
 * central page for editing all users
 */
function display_editusers($order = 'name')
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to edit users
		return redirect();
	}

	// get all users
	$users = get_collection(TOTE_COLLECTION_USERS);
	$allusers = $users->find(array(), array('username', 'first_name', 'last_name', 'email', 'role', 'created', 'lastlogin', 'lastpasswordchange'));
	$userarray = array();
	foreach ($allusers as $u) {
		if (isset($u['created'])) {
			$u['createdlocal'] = get_local_datetime($u['created']);
		}
		if (isset($u['lastlogin'])) {
			$u['lastloginlocal'] = get_local_datetime($u['lastlogin']);
		}
		if (isset($u['lastpasswordchange'])) {
			$u['lastpasswordchangelocal'] = get_local_datetime($u['lastpasswordchange']);
		}
		$u['readable_name'] = user_readable_name($u);
		$userarray[] = $u;
	}

	// sort
	switch ($order) {
		case 'name':
			usort($userarray, 'sort_users');
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
			usort($userarray, 'sort_users');
			$order = 'name';
			break;
	}

	// set data and display
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('allusers', $userarray);
	$tpl->assign('order', $order);
	$tpl->display('editusers.tpl');
}
