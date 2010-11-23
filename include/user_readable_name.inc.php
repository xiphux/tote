<?php

/**
 * Gets a displayable name for a user
 *
 * @param object $user user object
 * @return string human readable name
 */
function user_readable_name($user)
{
	// default to username
	$name = $user['username'];

	if (!empty($user['first_name'])) {
		// use first name
		$name = $user['first_name'];
		if (!empty($user['last_name'])) {
			// add last name if we have it
			$name .= ' ' . $user['last_name'];
		}
		$name = trim($name);
	}

	return $name;
}
