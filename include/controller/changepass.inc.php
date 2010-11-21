<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');

function display_changepass()
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		return redirect();
	}

	$tpl->display('changepass.tpl');

}
