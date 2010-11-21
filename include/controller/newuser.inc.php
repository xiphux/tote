<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_newuser()
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		return redirect();
	}

	$users = get_collection(TOTE_COLLECTION_USERS);

	$user = $users->findOne(array('username' => $_SESSION['user']), array('username', 'admin'));
	if (!$user) {
		return redirect();
	}

	if (empty($user['admin'])) {
		return redirect();
	}

	$tpl->display('newuser.tpl');

}
