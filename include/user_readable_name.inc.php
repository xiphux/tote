<?php

function user_readable_name($user)
{
	$name = $user['username'];

	if (!empty($user['first_name'])) {
		$name = $user['first_name'];
		if (!empty($user['last_name'])) {
			$name .= ' ' . $user['last_name'];
		}
		$name = trim($name);
	}

	return $name;
}
