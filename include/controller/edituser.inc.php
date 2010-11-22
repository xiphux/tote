<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_edituser($userid)
{
	global $tpl;

	$user = user_logged_in();
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

	$users = get_collection(TOTE_COLLECTION_USERS);

	$edituser = get_user(new MongoId($userid));
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
