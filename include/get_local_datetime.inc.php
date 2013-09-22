<?php

define('TOTE_DEFAULT_TIMEZONE', 'America/New_York');

/**
 * Gets the local time for a database timestamp
 *
 * @timestamp int utc timestamp
 */
function get_local_datetime($timestamp, $timezone = null)
{
	if ($timestamp < 1) {
		return null;
	}

	$local = new DateTime('@' . $timestamp);
	$local->setTimezone(new DateTimeZone(TOTE_DEFAULT_TIMEZONE));
	if (!empty($timezone)) {
		try {
			$local->setTimezone(new DateTimeZone($timezone));
		} catch (Exception $e) {
		}
	}
	return $local;
}
