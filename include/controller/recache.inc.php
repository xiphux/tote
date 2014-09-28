<?php

require_once(TOTE_INCLUDEDIR . 'record_mark_dirty.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

/**
 * recache controller
 *
 * triggers a pool to rebuild its cache
 *
 * @param string $poolid pool id
 */
function display_recache($poolid)
{
	if (empty($poolid)) {
		return redirect();
	}
	
	$user = user_logged_in();
	if (!$user) {
		// need to be logged in
		return redirect(array('p' => $poolid));
	}

	if (!user_is_admin($user)) {
		// need to be an admin
		return redirect(array('p' => $poolid));
	}

	record_mark_dirty($poolid);
	
	redirect(array('p' => $poolid));
}