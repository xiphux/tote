<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_password_hash.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('SAVEUSER_HEADER', 'Edit A User');

/**
 * saveuser controller
 *
 * after editing a user, save the changes in the database
 *
 * @param string $userid user id to save
 * @param string $firstname first name
 * @param string $lastname last name
 * @param string $email email address
 * @param string $role user role
 * @param string $csrftoken CSRF request token
 */
function display_saveuser($userid, $firstname, $lastname, $email, $role, $newpassword, $newpassword2, $csrftoken)
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
		display_message("Invalid request token", SAVEUSER_HEADER);
		return;
	}

	if (empty($userid)) {
		// need to know what user to edit
		display_message("User required", SAVEUSER_HEADER);
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);

	$edituser = get_user($userid);
	if (!$edituser) {
		// needs to be a valid user
		display_message("User not found", SAVEUSER_HEADER);
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

	if (!(empty($newpassword) && empty($newpassword2))) {
		if ($newpassword != $newpassword2) {
			$errors[] = 'Passwords don\'t match';
		}
	}

	if (count($errors) > 0) {
		// if we have errors, go back to the edit page and display them
		http_headers();
		$tpl->assign("errors", $errors);
		if (!empty($firstname))
			$tpl->assign('firstname', $firstname);
		if (!empty($lastname))
			$tpl->assign('lastname', $lastname);
		$tpl->assign('username', $edituser['username']);
		if (!empty($email))
			$tpl->assign('email', $email);
		if (!empty($role))
			$tpl->assign('role', $role);
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
		if (!empty($role))
			$setdata['role'] = (int)$role;
		else
			$unsetdata['role'] = 1;
		if (!(empty($newpassword) || empty($newpassword2))) {
			$hashdata = generate_password_hash($edituser['username'], $newpassword);
			$setdata['salt'] = $hashdata['salt'];
			$setdata['password'] = $hashdata['passwordhash'];
			$setdata['lastpasswordchange'] = new MongoDate();
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
