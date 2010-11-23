<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

/**
 * changepass controller
 *
 * displays page for user to change password
 */
function display_changepass()
{
	global $tpl;

	if (!user_logged_in()) {
		// user must be logged in
		return redirect();
	}

	$tpl->display('changepass.tpl');

}
