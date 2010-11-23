<?php

require_once(TOTE_INCLUDEDIR . 'script_url.inc.php');

function redirect($params = null)
{
	header('Location: ' . script_url($params));
	return;
}
