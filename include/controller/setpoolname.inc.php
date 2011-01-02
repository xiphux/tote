<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('SETPOOLNAME_HEADER', 'Manage Your Pool');

/**
 * setpoolname controller
 *
 * change the name of a pool
 *
 * @param string $poolID pool id
 * @param string $poolname new name
 * @param string $csrftoken CSRF request token
 */
function display_setpoolname($poolID, $poolname, $csrftoken)
{
	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to change the name
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		display_message("Invalid request token", SETPOOLNAME_HEADER);
		return;
	}

	if (empty($poolID)) {
		// need to know the pool
		display_message("Pool is required", SETPOOLNAME_HEADER);
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", SETPOOLNAME_HEADER);
		return;
	}

	if (empty($poolname)) {
		// we need a name
		display_message("Pool must have a name", SETPOOLNAME_HEADER);
		return;
	}

	$duplicate = $pools->findOne(
		array(
			'name' => $poolname,
			'season' => $pool['season'],
			'_id' => array(
				'$ne' => $pool['_id']
			)
		),
		array('name', 'season')
	);
	if (!empty($duplicate)) {
		// don't allow duplicate pool names - not for technical reasons,
		// just because it makes no sense since you can't differentiate
		display_message("There is already a pool with the name \"" . $poolname . "\" for this season", SETPOOLNAME_HEADER);
		return;
	}

	// do the update
	$pools->update(
		array('_id' => $pool['_id']),
		array('$set' => array('name' => $poolname))
	);

	// go back to edit pool page
	return redirect(array('a' => 'editpool', 'p' => $poolID));
}
