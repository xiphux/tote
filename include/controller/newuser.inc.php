<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

function display_newuser()
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (!user_is_admin($user)) {
		return redirect();
	}

	$tpl->display('newuser.tpl');

}
