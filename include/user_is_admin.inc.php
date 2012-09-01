<?php

/**
 * Tests if a user is an admin
 *
 * @param object $user user object
 * @return true if user is admin
 */
function user_is_admin($user)
{
	if (!empty($user['role']) && ($user['role'] == 1)) {
		return true;
	}

	return false;
}
