<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');

/**
 * logout controller
 *
 * logs the user out
 */
function display_logout()
{
	unset($_SESSION['user']);
	unset($_SESSION['csrftoken']);
	redirect();
}
