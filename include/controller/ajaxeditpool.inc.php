<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'clear_cache.inc.php');

/**
 * ajaxeditpool controller
 *
 * perform AJAX asynchronous pool modifications
 *
 * @param string $poolid pool ID
 * @param string $modification type of modification to do
 * @param array $modusers list of users being modified
 * @param string $csrftoken CSRF request token
 */
function display_ajaxeditpool($poolid, $modification, $modusers, $csrftoken)
{
	global $mysqldb;

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

	if (empty($poolid)) {
		// must have a pool to edit
		echo "Pool is required";
		return;
	}

	if (empty($modification)) {
		// need to know what to do
		echo "Modification required";
		return;
	}

	if (empty($modusers) || (count($modusers) < 1)) {
		// need at least 1 user to modify
		echo "No users to modify";
		return;
	}

	$poolstmt = $mysqldb->prepare('SELECT id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=?');
	$poolstmt->bind_param('i', $poolid);
	$poolstmt->execute();
	$found = $poolstmt->fetch();
	$poolstmt->close();

	if (!$found) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	$adminusername = user_readable_name($user);

	if (($modification == 'add') || ($modification == 'remove')) {
		
		$modstmt = null;

		if ($modification == 'add') {
			$modstmt = $mysqldb->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRIES . ' (pool_id, user_id) VALUES (?, ?)');
		} else {
			$modstmt = $mysqldb->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=? AND user_id=?');
		}

		$actionquery = <<<EOQ
INSERT INTO %s
(pool_id, time, action, user_id, username, admin_id, admin_username)
VALUES
(?, UTC_TIMESTAMP(), ?, ?, (
SELECT
(CASE
WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name)
WHEN users.first_name IS NOT NULL THEN users.first_name
ELSE users.username
END)
FROM %s AS users
WHERE id=?
),
?, ?)
EOQ;

		$actionquery = sprintf($actionquery, TOTE_TABLE_POOL_ACTIONS, TOTE_TABLE_USERS);
		$actionstmt = $mysqldb->prepare($actionquery);
		$action = ($modification == 'add') ? 1 : 2;

		foreach ($modusers as $muser) {

			$modstmt->bind_param('ii', $poolid, $muser);
			$modstmt->execute();

			$actionstmt->bind_param('iiiiis', $poolid, $action, $muser, $muser, $user['id'], $adminusername);
			$actionstmt->execute();

		}

		$actionstmt->close();
		$modstmt->close();

		clear_cache('pool|' . $poolid);
	} else {
		echo "Unknown modification";
	}

}
