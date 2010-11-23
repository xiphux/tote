<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');

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
	$allusers = $users->find(array(), array('username', 'first_name', 'last_name', 'email', 'admin'));
	$userarray = array();
	foreach ($allusers as $u) {
		$userarray[] = $u;
	}

	// sort alphabetically
	usort($userarray, 'sort_users');

	// set data and display
	$tpl->assign('allusers', $userarray);
	$tpl->display('editusers.tpl');
}
