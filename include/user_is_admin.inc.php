<?php

/**
 * Tests if a user is an admin
 *
 * @param object $user user object
 * @return true if user is admin
 */
function user_is_admin($user)
{
	if (!empty($user['admin']) && ($user['admin'] === true)) {
		return true;
	}

	return false;
}
