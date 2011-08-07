<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * setpooladmin controller
 *
 * toggles users' admin state
 *
 * @param string $poolID pool ID
 * @param string $userID user ID
 * @param string $type admin type
 * @param string $csrftoken CSRF request token
 */
function display_setpooladmin($poolID, $userID, $type, $csrftoken)
{
	$user = user_logged_in();
	if (!$user) {
		// user must be logged in
		echo "User not logged in";
		return;
	}

	if (!user_is_admin($user)) {
		// need to be an admin to edit the pool
		echo "User is not an admin";
		return;
	}

	if (!validate_csrftoken($csrftoken)) {
		echo "Invalid request token";
		return;
	}

	if (empty($poolID)) {
		// must have a pool to edit
		echo "Pool is required";
		return;
	}

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$pool = $pools->findOne(array('_id' => new MongoId($poolID)), array('name', 'administrators'));
	if (!$pool) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (empty($userID)) {
		// must have a user
		echo "User is required";
		return;
	}

	$newadminuser = get_user($userID);

	if (!$newadminuser) {
		echo "Unknown user";
		return;
	}

	if (($type > 2) || ($type < 0)) {
		echo "Unknown admin type";
		return;
	}

	$currentadmintype = 0;

	if (isset($pool['administrators'])) {
		foreach ($pool['administrators'] as $admin) {
			if ((string)$admin['user'] == $userID) {
				if (isset($admin['secondary']) && ($admin['secondary'] == true)) {
					$currentadmintype = 2;
				} else {
					$currentadmintype = 1;
				}
				break;
			}
		}
	}

	if ($type != $currentadmintype) {
		$pools->update(
			array('_id' => $pool['_id']),
			array('$pull' => array(
				'administrators' => array(
					'user' => $newadminuser['_id']
				)
			))
		);

		$newadminusername = user_readable_name($newadminuser);

		if ($type > 0) {

			$admindata = array();
			$admindata['user'] = $newadminuser['_id'];
			$admindata['name'] = $newadminusername;
			if ($type == 2) {
				$admindata['secondary'] = true;
			}

			$pools->update(
				array('_id' => $pool['_id']),
				array('$push' => array(
					'administrators' => $admindata
				))
			);

		}

		// audit
		$username = user_readable_name($user);
		$action = array(
			'action' => 'pooladminchange',
			'user' => $newadminuser['_id'],
			'user_name' => $newadminusername,
			'admin' => $user['_id'],
			'admin_name' => $username,
			'time' => new MongoDate(time()),
			'oldpooladmin' => (int)$currentadmintype,
			'newpooladmin' => (int)$type
		);

		$pools->update(
			array('_id' => $pool['_id']),
			array('$push' => array(
				'actions' => $action
			))
		);

	}

}
