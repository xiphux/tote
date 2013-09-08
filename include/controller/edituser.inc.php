<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
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
	global $tpl, $mysqldb;

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

	$userstmt = $mysqldb->prepare('SELECT username, first_name, last_name, email, role FROM ' . TOTE_TABLE_USERS . ' WHERE id=?');
	$userstmt->bind_param('i', $userid);
	$userstmt->execute();
	$userresult = $userstmt->get_result();
	$edituser = $userresult->fetch_assoc();
	$userresult->close();
	$userstmt->close();

	if (!$edituser) {
		// needs to be a valid user
		display_message("User not found", EDITUSER_HEADER);
		return;
	}

	// set the user's current values into the form
	// and display
	http_headers();
	$tpl->assign('username', $edituser['username']);
	if (!empty($edituser['first_name']))
		$tpl->assign('firstname', $edituser['first_name']);
	if (!empty($edituser['last_name']))
		$tpl->assign('lastname', $edituser['last_name']);
	if (!empty($edituser['email']))
		$tpl->assign('email', $edituser['email']);
	if (!empty($edituser['role']))
		$tpl->assign('role', $edituser['role']);
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->assign('userid', $userid);
	$tpl->display('edituser.tpl');

}
