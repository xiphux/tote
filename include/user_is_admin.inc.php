<?php

function user_is_admin($user)
{
	if (!empty($user['admin']) && ($user['admin'] === true)) {
		return true;
	}

	return false;
}
