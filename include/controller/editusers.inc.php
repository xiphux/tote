<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'sort_users.inc.php');

function display_editusers()
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (!user_is_admin($user)) {
		return redirect();
	}

	$users = get_collection(TOTE_COLLECTION_USERS);

	$allusers = $users->find(array(), array('username', 'first_name', 'last_name', 'email', 'admin'));
	$userarray = array();
	foreach ($allusers as $u) {
		$userarray[] = $u;
	}
	usort($userarray, 'sort_users');

	$tpl->assign('allusers', $userarray);
	$tpl->display('editusers.tpl');
}
