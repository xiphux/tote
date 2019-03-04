<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_is_admin.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_CONTROLLERDIR . 'message.inc.php');

define('EDITPOOL_HEADER', 'Manage Your Pool');

/**
 * editpool controller
 *
 * page to edit entrants/settings for a pool
 *
 * @param string $poolid pool id
 */
function display_editpool($poolid)
{
	global $tpl, $db;

	$user = user_logged_in();
	if (!$user) {
		// must be logged in to add a user
		return redirect();
	}

	if (!user_is_admin($user)) {
		// must be an admin to add a user
		return redirect();
	}

	if (empty($poolid)) {
		// need to know the pool
		display_message("Pool is required", EDITPOOL_HEADER);
		return;
	}

	$poolstmt = $db->prepare('SELECT pools.id, pools.name, seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON seasons.id=pools.season_id WHERE pools.id=:pool_id');
	$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
	$poolstmt->execute();
	$pool = $poolstmt->fetch(PDO::FETCH_ASSOC);
	$poolstmt = null;

	if (!$pool) {
		// pool must exist
		display_message("Unknown pool", EDITPOOL_HEADER);
		return;
	}

	$userquery = <<<EOQ
SELECT
users.id,
users.email,
(CASE
 WHEN (users.first_name IS NOT NULL AND users.last_name IS NOT NULL) THEN CONCAT(CONCAT(users.first_name,' '),users.last_name)
 WHEN users.first_name IS NOT NULL THEN users.first_name
 ELSE users.username
END) AS display_name,
pool_entries.id AS entry_id,
pool_administrators.admin_type,
COUNT(pool_entry_picks.id) AS pick_count
FROM %s AS users
LEFT JOIN %s AS pool_entries
ON pool_entries.user_id=users.id AND pool_entries.pool_id=:entry_pool_id
LEFT JOIN %s AS pool_administrators
ON pool_administrators.user_id=users.id AND pool_administrators.pool_id=:admin_pool_id
LEFT JOIN %s AS pool_entry_picks
ON pool_entry_picks.pool_entry_id=pool_entries.id
GROUP BY users.id, pool_administrators.admin_type
ORDER BY LOWER(display_name)
EOQ;
	$userquery = sprintf($userquery, TOTE_TABLE_USERS, TOTE_TABLE_POOL_ENTRIES, TOTE_TABLE_POOL_ADMINISTRATORS, TOTE_TABLE_POOL_ENTRY_PICKS);
	$userstmt = $db->prepare($userquery);
	$userstmt->bindParam(':entry_pool_id', $poolid, PDO::PARAM_INT);
	$userstmt->bindParam(':admin_pool_id', $poolid, PDO::PARAM_INT);
	$userstmt->execute();

	$poolusers = array();
	$availableusers = array();

	while ($user = $userstmt->fetch(PDO::FETCH_ASSOC)) {
		if (!empty($user['entry_id'])) {
			$poolusers[(int)$user['id']] = $user;
		} else {
			$availableusers[(int)$user['id']] = $user;
		}
	}

	$userstmt = null;

	// set data and display
	http_headers();
	if (count($poolusers) > 0) {
		$tpl->assign('poolusers', $poolusers);
	}
	if (count($availableusers) > 0) {
		$tpl->assign('availableusers', $availableusers);
	}
	$tpl->assign('pool', $pool);
	$tpl->assign('csrftoken', $_SESSION['csrftoken']);
	$tpl->display('editpool.tpl');

}
