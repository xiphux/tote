<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');

/**
 * editusers
 *
 * central page for editing all users
 */
function display_editusers()
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
	$allusers = $users->find(array(), array('username', 'first_name', 'last_name', 'email', 'admin', 'created', 'lastlogin', 'lastpasswordchange'));
	$userarray = array();
	foreach ($allusers as $u) {
		if (isset($u['created'])) {
			$u['createdlocal'] = get_local_datetime($u['created']);;
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

	// sort alphabetically
	usort($userarray, 'sort_users');

	// set data and display
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('allusers', $userarray);
	$tpl->display('editusers.tpl');
}
