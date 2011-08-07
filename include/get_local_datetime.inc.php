<?php

require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

define('TOTE_DEFAULT_TIMEZONE', 'America/New_York');

/**
 * Gets the local time for a database timestamp
 *
 * @param object mongo date object
 */
function get_local_datetime($mongodate)
{
	if (!$mongodate) {
		return null;
	}

	$local = new DateTime('@' . $mongodate->sec);
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
