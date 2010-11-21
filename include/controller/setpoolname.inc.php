<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_setpoolname($poolID, $poolname)
{
	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (empty($user['admin'])) {
		return redirect();
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('season', 'name', 'entries'));
	if (!$pool) {
		echo "Unknown pool";
		return;
	}

	if (empty($poolname)) {
		echo "Pool must have a name";
		return;
	}

	$duplicate = $pools->findOne(array('name' => $poolname, 'season' => $pool['season'], '_id' => array('$ne' => $pool['_id'])), array('name', 'season'));
	if (!empty($duplicate)) {
		echo "There is already a pool with the name \"" . $poolname . "\" for this season";
		return;
	}

	$pools->update(array('_id' => $pool['_id']), array('$set' => array('name' => $poolname)));

	return redirect(array('a' => 'editpool', 'p' => $poolID));
}
