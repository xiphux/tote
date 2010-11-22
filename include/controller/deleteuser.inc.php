<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');

function display_deleteuser($userid)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		return redirect();
	}

	if (!user_is_admin($user)) {
		return redirect();
	}

	if (empty($userid)) {
		echo "User to delete is required";
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);
	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$deleteuser = get_user($userid)
	if (!$deleteuser) {
		echo "Could not find user to delete";
		return;
	}

	$adminname = user_readable_name($user);

	$username = user_readable_name($deleteuser);

	$action = array(
		'action' => 'removeentrant',
		'user' => $deleteuser['_id'],
		'user_name' => $username,
		'admin' => $user['_id'],
		'admin_name' => $username,
	);
		
	$enteredpools = $pools->find(array('entries.user' => $deleteuser['_id']), array('name', 'season'));
	foreach ($enteredpools as $p) {
		$action['time'] = new MongoDate(time());
		$pools->update(array('_id' => $p['_id']), array('$pull' => array('entries' => array('user' => $deleteuser['_id'])), '$push' => array('actions' => $action)));
	}

	$users->remove(array('_id' => $deleteuser['_id']));

	redirect(array('a' => 'editusers'));
}
