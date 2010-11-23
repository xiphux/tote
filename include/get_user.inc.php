<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

$usercache = array();

function get_user($id)
{
	global $usercache;

	$stringid = '';
	$objid = null;

	if (is_string($id)) {
		$stringid = $id;
	} else if ($id instanceof MongoId) {
		$objid = $id;
		$stringid = (string)$id;
	} else {
		return;
	}

	if (empty($usercache[$stringid])) {
		$users = get_collection(TOTE_COLLECTION_USERS);
		if (!$objid)
			$objid = new MongoId($stringid);
		$usercache[$stringid] = $users->findOne(array('_id' => $objid), array('username', 'admin', 'first_name', 'last_name', 'email'));
	}

	return $usercache[$stringid];
}
