<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_setpoolname($poolID, $poolname)
{
	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);
	$users = get_collection(TOTE_COLLECTION_USERS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($user['admin'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	if (empty($poolID)) {
		echo "Pool is required";
		return;
	}

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

	header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?a=editpool&p=' . $poolID);
}
