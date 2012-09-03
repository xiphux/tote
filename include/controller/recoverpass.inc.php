<?php

require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * recoverpass controller
 *
 * display password recovery form
 */
function display_recoverpass()
{
	global $tpl;

	http_headers();

	$tpl->display('recoverpass.tpl');

}
