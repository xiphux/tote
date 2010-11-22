<?php

require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

function sort_users($a, $b)
{
	return strcasecmp(user_readable_name($a), user_readable_name($b));
}
