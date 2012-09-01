<?php

/**
 * Tests if a user is a manager
 *
 * @param object $user user object
 * @return true if user is manager
 */
function user_is_manager($user)
{
	if (!empty($user['role']) && ($user['role'] == 2)) {
		return true;
	}

	return false;
}
