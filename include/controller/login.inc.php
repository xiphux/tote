<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * login controller
 *
 * display login form
 */
function display_login()
{
	global $tpl;

	if (user_logged_in()) {
		// don't login again if user is already logged in
		return redirect();
	}

	http_headers();

	$tpl->display('login.tpl');

}
