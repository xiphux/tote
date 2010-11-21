<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');

function display_saveuser($userid, $firstname, $lastname, $email, $admin)
{
	global $tpl;

	if (!isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

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

	if (empty($userid)) {
		echo "User required";
		return;
	}

	$edituser = $users->findOne(array('_id' => new MongoId($userid)), array('username', 'admin', 'first_name', 'last_name', 'email'));
	if (!$edituser) {
		echo "User not found";
		return;
	}

	$errors = array();

	if (empty($email)) {
		$errors[] = "Email is required";
	} else {
		$existinguser = $users->findOne(array('email' => $email, '_id' => array('$ne' => $edituser['_id'])), array('username', 'email'));
		if ($existinguser)
			$errors[] = "A user with that email address already exists";
		if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $email)) {
			$errors[] = "Email must be valid";
		}
	}

	if (count($errors) > 0) {
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
		$tpl->display('edituser.tpl');
	} else {
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
			if (empty($edituser['admin']))
				$setdata['admin'] = true;
		} else {
			if (!empty($edituser['admin']) && ($edituser['admin'] == true))
				$unsetdata['admin'] = 1;
		}
		if (count($setdata) > 0)
			$data['$set'] = $setdata;
		if (count($unsetdata) > 0)
			$data['$unset'] = $unsetdata;
		if (count($data) > 0) {
			$users->update(array('_id' => $edituser['_id']), $data);
		}
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?a=editusers');
	}

}
