<?php

require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

define('TOTE_DEFAULT_TIMEZONE', 'America/New_York');

/**
 * Gets the local time for a database timestamp
 *
 * @timestamp int utc timestamp
 */
function get_local_datetime($timestamp)
{
	if ($timestamp < 1) {
		return null;
	}

	$local = new DateTime('@' . $timestamp);
	$local->setTimezone(new DateTimeZone(TOTE_DEFAULT_TIMEZONE));
	$user = user_logged_in();
	if ($user && isset($user['timezone'])) {
		try {
			$local->setTimezone(new DateTimeZone($user['timezone']));
		} catch (Exception $e) {
		}
	}
	return $local;
}
