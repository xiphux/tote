<?php

require_once(TOTE_INCLUDEDIR . 'script_url.inc.php');

/**
 * Redirects user to the home page, optionally with parameters
 *
 * @param array $params key/value map of get variables
 */
function redirect($params = null)
{
	header('Location: ' . script_url($params));
}
