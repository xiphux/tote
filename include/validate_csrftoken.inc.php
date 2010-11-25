<?php

/**
 * Validates the given CSRF token
 *
 * @param string $token token to validate
 * @return true if token is valid
 */
function validate_csrftoken($token)
{
	if (!empty($_SESSION['csrftoken']) && ($_SESSION['csrftoken'] == $token)) {
		return true;
	}

	return false;
}
