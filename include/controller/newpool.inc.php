<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * newpool controller
 *
 * show new pool entry form
 */
function display_newpool()
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

	http_headers();

	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->display('newpool.tpl');

}
