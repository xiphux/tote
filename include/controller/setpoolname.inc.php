<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

/**
 * setpoolname controller
 *
 * change the name of a pool
 *
 * @param string $poolID pool id
 * @param string $poolname new name
 */
function display_setpoolname($poolID, $poolname)
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

	if (empty($poolID)) {
		// need to know the pool
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (empty($poolname)) {
		// we need a name
		echo "Pool must have a name";
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
		echo "There is already a pool with the name \"" . $poolname . "\" for this season";
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
