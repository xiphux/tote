<?php

require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

/**
 * Sort users alphabetically by their human readable name
 *
 * @param object $a first comparison item
 * @param object $b second comparison item
 */
function sort_users($a, $b)
{
	// case insensitive comparison by user readable name
	return strcasecmp(user_readable_name($a), user_readable_name($b));
}
