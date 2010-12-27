<?php

/**
 * Wrapper around nfl scorestrip feed so it can be called
 * by ajax (since ajax prevents cross domain queries)
 */

define('TOTE_INCLUDEDIR', dirname(__FILE__) . '/include/');

require_once(TOTE_INCLUDEDIR . 'load_page.inc.php');

$url = 'http://www.nfl.com/liveupdate/scorestrip/ss.xml';

$raw = load_page($url);

if (!empty($raw)) {
	header('Content-type: text/xml');
	echo $raw;
};
