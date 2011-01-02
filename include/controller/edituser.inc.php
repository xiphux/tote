<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('EDITUSER_HEADER', 'Edit A User');

/**
 * edituser
 *
 * form to edit a single user
 *
 * @param string $userid user id
 */
function display_edituser($userid)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to edit users
		return redirect();
	}

	if (empty($userid)) {
		// need to know the user to edit
		display_message("User required", EDITUSER_HEADER);
		return;
	}

	$edituser = get_user($userid);
	if (!$edituser) {
		// needs to be a valid user
		display_message("User not found", EDITUSER_HEADER);
		return;
	}

	// set the user's current values into the form
	// and display
	$tpl->assign('username', $edituser['username']);
	if (!empty($edituser['first_name']))
		$tpl->assign('firstname', $edituser['first_name']);
	if (!empty($edituser['last_name']))
		$tpl->assign('lastname', $edituser['last_name']);
	if (!empty($edituser['email']))
		$tpl->assign('email', $edituser['email']);
	if (user_is_admin($edituser))
		$tpl->assign('admin', true);
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('userid', $userid);
	$tpl->display('edituser.tpl');

}
