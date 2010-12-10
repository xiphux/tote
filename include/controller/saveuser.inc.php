<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * saveuser controller
 *
 * after editing a user, save the changes in the database
 *
 * @param string $userid user id to save
 * @param string $firstname first name
 * @param string $lastname last name
 * @param string $email email address
 * @param string $admin admin flag
 * @param string $csrftoken CSRF request token
 */
function display_saveuser($userid, $firstname, $lastname, $email, $admin, $csrftoken)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to change a user
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		echo "Invalid request token";
		return;
	}

	if (empty($userid)) {
		// need to know what user to edit
		echo "User required";
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);

	$edituser = get_user($userid);
	if (!$edituser) {
		// needs to be a valid user
		echo "User not found";
		return;
	}

	$errors = array();

	if (empty($email)) {
		// need the email address
		$errors[] = "Email is required";
	} else {
		$existinguser = $users->findOne(
			array(
				'email' => $email,
				'_id' => array(
					'$ne' => $edituser['_id']
				)
			),
			array('username', 'email')
		);
		if ($existinguser) {
			// no duplicate emails
			$errors[] = "A user with that email address already exists";
		}
		if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $email)) {
			// must be a valid email address
			$errors[] = "Email must be valid";
		}
	}

	if (count($errors) > 0) {
		// if we have errors, go back to the edit page and display them
		$tpl->assign("errors", $errors);
		if (!empty($firstname))
			$tpl->assign('firstname', $firstname);
		if (!empty($lastname))
			$tpl->assign('lastname', $lastname);
		$tpl->assign('username', $edituser['username']);
		if (!empty($email))
			$tpl->assign('email', $email);
		if (!empty($admin) && (strcasecmp($admin, 'on') == 0))
			$tpl->assign('admin', $admin);
		$tpl->assign('userid', $userid);
		$tpl->assign('csrftoken', $_SESSION['csrftoken']);
		$tpl->display('edituser.tpl');
	} else {
		// set data
		$data = array();
		$setdata = array();
		$unsetdata = array();
		if ($firstname != $edituser['first_name'])
			$setdata['first_name'] = $firstname;
		if ($lastname != $edituser['last_name'])
			$setdata['last_name'] = $lastname;
		if ($email != $edituser['email'])
			$setdata['email'] = $email;
		if (!empty($admin) && (strcasecmp($admin, 'on') == 0)) {
			if (!user_is_admin($edituser))
				$setdata['admin'] = true;
		} else {
			if (user_is_admin($edituser))
				$unsetdata['admin'] = 1;
		}
		if (count($setdata) > 0)
			$data['$set'] = $setdata;
		if (count($unsetdata) > 0)
			$data['$unset'] = $unsetdata;
		if (count($data) > 0) {
			$users->update(array('_id' => $edituser['_id']), $data);
			clear_cache('pool');
		}

		// go back to edit users page
		redirect(array('a' => 'editusers'));
	}

}
