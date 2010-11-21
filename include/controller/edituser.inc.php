<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_edituser($userid)
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

	if (empty($userid)) {
		echo "User required";
		return;
	}

	$edituser = $users->findOne(array('_id' => new MongoId($userid)), array('username', 'admin', 'first_name', 'last_name', 'email'));
	if (!$edituser) {
		echo "User not found";
		return;
	}

	$tpl->assign('username', $edituser['username']);
	if (!empty($edituser['first_name']))
		$tpl->assign('firstname', $edituser['first_name']);
	if (!empty($edituser['last_name']))
		$tpl->assign('lastname', $edituser['last_name']);
	if (!empty($edituser['email']))
		$tpl->assign('email', $edituser['email']);
	if (!empty($edituser['admin']) && ($edituser['admin'] == true))
		$tpl->assign('admin', true);
	$tpl->assign('userid', $userid);
	$tpl->display('edituser.tpl');

}
