<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

/**
 * newuser controller
 *
 * show new user entry form
 */
function display_newuser()
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// need to be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin
		return redirect();
	}

	$tpl->display('newuser.tpl');

}
