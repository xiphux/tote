<?php

/**
 * Wrapper around nfl scorestrip feed so it can be called
 * by ajax (since ajax prevents cross domain queries)
 */

define('TOTE_INCLUDEDIR', dirname(__FILE__) . '/include/');
define('TOTE_CACHEDIR', dirname(__FILE__) . '/cache/');
define('TOTE_CONFIG', dirname(__FILE__) . '/config/tote.conf.php');

define('TOTE_SCORETICKER_URL', 'https://feeds.nfl.com/feeds-rs/scores.json');

if (is_readable(TOTE_CONFIG)) {
	@include(TOTE_CONFIG);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, TOTE_SCORETICKER_URL);
curl_setopt($ch, CURLOPT_FILETIME, 1);
curl_setopt($ch, CURLOPT_NOBODY, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);
$mtime = curl_getinfo($ch, CURLINFO_FILETIME);

$raw = null;

if ($mtime > 0 && isset($tote_conf['cache']) && ($tote_conf['cache'] == true)) {
	if (is_readable(TOTE_CACHEDIR . 'scoreticker.dat')) {
		$cached = file_get_contents(TOTE_CACHEDIR . 'scoreticker.dat');
		if ($cached) {
			$cachedtime = strtok($cached, "\n");
			if ((int)$cachedtime >= $mtime) {
				$raw = substr($cached, strlen($cachedtime) + 1);
			}
		}
	}
}
if (empty($raw)) {
	curl_setopt($ch, CURLOPT_HTTPGET, 1);

	$raw = curl_exec($ch);

	if (!empty($raw) && isset($tote_conf['cache']) && ($tote_conf['cache'] == true)) {
		$data = $mtime . "\n" . $raw;
		file_put_contents(TOTE_CACHEDIR . 'scoreticker.dat', $data, LOCK_EX);
	}
}

curl_close($ch);

if (!empty($raw)) {
	header('Content-type: application/json');
	if ($mtime > 0) {
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $mtime) . ' GMT');
		header('Expires: ' . gmdate("D, d M Y H:i:s", $mtime+13) . ' GMT');
	}
	echo $raw;
};
