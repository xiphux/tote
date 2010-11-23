<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_login()
{
	global $tpl;

	if (user_logged_in()) {
		return redirect();
	}

	$tpl->display('login.tpl');

}
