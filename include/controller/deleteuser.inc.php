<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * deleteuser controller
 *
 * deletes a user from the database
 *
 * @param string $userid user id to delete
 * @param string $csrftoken CSRF request token
 */
function display_deleteuser($userid, $csrftoken)
{
	global $tpl;

	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		return redirect();
	}

	if (!user_is_admin($user)) {
		// need to be an admin to delete a user
		return redirect();
	}

	if (!validate_csrftoken($csrftoken)) {
		echo "Invalid request token";
		return;
	}

	if (empty($userid)) {
		// need to know which user to delete
		echo "User to delete is required";
		return;
	}

	$users = get_collection(TOTE_COLLECTION_USERS);
	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$deleteuser = get_user($userid);
	if (!$deleteuser) {
		// must be a valid user to delete
		echo "Could not find user to delete";
		return;
	}

	// create audit log entry
	$adminname = user_readable_name($user);
	$username = user_readable_name($deleteuser);
	$action = array(
		'action' => 'removeentrant',
		'user' => $deleteuser['_id'],
		'user_name' => $username,
		'admin' => $user['_id'],
		'admin_name' => $username,
	);
		
	// remove user from any pools they're in
	$enteredpools = $pools->find(
		array(
			'entries.user' => $deleteuser['_id']
		),
		array('name', 'season')
	);
	foreach ($enteredpools as $p) {
		$action['time'] = new MongoDate(time());
		$pools->update(
			array(
				'_id' => $p['_id']
			),
			array(
				'$pull' => array(
					'entries' => array(
						'user' => $deleteuser['_id']
					)
				),
				'$push' => array(
					'actions' => $action
				)
			)
		);
		clear_cache('pool|' . (string)$p['_id'])
	}

	// remove user from system
	$users->remove(
		array(
			'_id' => $deleteuser['_id']
		)
	);

	redirect(array('a' => 'editusers'));
}
