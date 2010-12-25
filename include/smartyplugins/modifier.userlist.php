<?php

require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

/**
 * smarty_modifier_userlist
 *
 * turns an array of users into a readable list
 *
 * @param array $users user array
 * @return string readable string
 */
function smarty_modifier_userlist($users)
{
	$usercount = count($users);

	if ($usercount <= 0)
		return '';

	$str = '';
	for ($i = 0; $i < $usercount; $i++) {
		$name = '';
		if (is_array($users[$i]))
			$name = user_readable_name($users[$i]);
		else
			$name = $users[$i];

		if (($usercount > 1) && ($i == ($usercount - 1))) {
			$str .= ' and ';
		}

		$str .= $name;

		if (($usercount > 2) && ($i < ($usercount - 1))) {
			$str .= ', ';
		}
	}

	return $str;
}
