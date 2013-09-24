<?php

require_once(TOTE_INCLUDEDIR . 'validate_csrftoken.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');

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
	global $db;

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

	$poolstmt = $db->prepare('SELECT id FROM ' . TOTE_TABLE_POOLS . ' WHERE id=:pool_id');
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();
	$found = $poolstmt->fetch(PDO::FETCH_ASSOC);
	$poolstmt = null;

	if (!$found) {
		// pool must exist
		echo "Unknown pool";
		return;
	}

	if (($modification == 'add') || ($modification == 'remove')) {
		
		$modstmt = null;

		if ($modification == 'add') {
			$modstmt = $db->prepare('INSERT INTO ' . TOTE_TABLE_POOL_ENTRIES . ' (pool_id, user_id) VALUES (:pool_id, :user_id)');
		} else {
			$modstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=:pool_id AND user_id=:user_id');
		}
		$modstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);

		$actionquery = <<<EOQ
INSERT INTO %s
(pool_id, time, action, user_id, username, admin_id, admin_username)
VALUES
(:pool_id, UTC_TIMESTAMP(), :action, :user_id, (
SELECT
(CASE
WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name)
WHEN users.first_name IS NOT NULL THEN users.first_name
ELSE users.username
END)
FROM %s AS users
WHERE id=:user_name_id
),
:admin_id, :admin_username)
EOQ;

		$actionquery = sprintf($actionquery, TOTE_TABLE_POOL_ACTIONS, TOTE_TABLE_USERS);
		$actionstmt = $db->prepare($actionquery);
		$action = ($modification == 'add') ? 1 : 2;
		$actionstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
		$actionstmt->bindParam(':action', $action, PDO::PARAM_INT);
		$actionstmt->bindParam(':admin_id', $user['id'], PDO::PARAM_INT);
		$actionstmt->bindParam(':admin_username', $user['display_name']);

		$modifiedusers = array();

		foreach ($modusers as $muser) {

			if ($modification == 'remove') {
				$rmpickstmt = $db->prepare('DELETE FROM ' . TOTE_TABLE_POOL_ENTRY_PICKS . ' WHERE pool_entry_id=(SELECT id FROM ' . TOTE_TABLE_POOL_ENTRIES . ' WHERE pool_id=:pool_id AND user_id=:user_id)');
				$rmpickstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
				$rmpickstmt->bindParam(':user_id', $muser, PDO::PARAM_INT);
				$rmpickstmt->execute();
				$rmpickstmt = null;
			}

			$modstmt->bindParam(':user_id', $muser, PDO::PARAM_INT);
			$modstmt->execute();
			if ($modstmt->rowCount() > 0) {
				$modifiedusers[] = $db->quote($muser);
			}

			$actionstmt->bindParam(':user_id', $muser, PDO::PARAM_INT);
			$actionstmt->bindParam(':user_name_id', $muser, PDO::PARAM_INT);
			$actionstmt->execute();

		}

		$actionstmt = null;
		$modstmt = null;

		if (count($modifiedusers) > 0) {
			$updaterecordquery = null;
			if ($modification == 'add') {
				$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE, ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' READ');
				$db->exec('SET foreign_key_checks=0');
				$db->exec('SET unique_checks=0');
				$db->exec('INSERT INTO ' . TOTE_TABLE_POOL_RECORDS . ' SELECT * FROM ' . TOTE_TABLE_POOL_RECORDS_VIEW . ' WHERE pool_id=' . $db->quote($poolid) . ' AND user_id IN (' . implode(', ', $modifiedusers) . ')');
				$db->exec('SET foreign_key_checks=1');
				$db->exec('SET unique_checks=1');
				$db->exec('UNLOCK TABLES');
			} else {
				$db->exec('LOCK TABLES ' . TOTE_TABLE_POOL_RECORDS . ' WRITE');
				$db->exec('SET foreign_key_checks=0');
				$db->exec('SET unique_checks=0');
				$db->exec('DELETE FROM ' . TOTE_TABLE_POOL_RECORDS . ' WHERE pool_id=' . $db->quote($poolid) . ' AND user_id IN (' . implode(', ', $modifiedusers) . ')');
				$db->exec('SET foreign_key_checks=1');
				$db->exec('SET unique_checks=1');
				$db->exec('UNLOCK TABLES');
			}
		}

	} else {
		echo "Unknown modification";
	}

}
