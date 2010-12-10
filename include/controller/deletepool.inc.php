<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * deletepool controller
 *
 * deletes a pool from the database
 *
 * @param string $poolid pool id to delete
 * @param string $csrftoken CSRF request token
 */
function display_deletepool($poolid, $csrftoken)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to delete a pool
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		echo "Invalid request token";
		return;
	}

	if (empty($poolid)) {
		// need to know which pool to delete
		echo "Pool to delete is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	// remove pool from system
	$pools->remove(
		array(
			'_id' => new MongoId($poolid)
		)
	);
	clear_cache('pool|' . $poolid);

	redirect();
}
