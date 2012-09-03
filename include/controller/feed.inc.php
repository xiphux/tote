<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_readable_name.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_local_datetime.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * feed controller
 *
 * displays pool audit history in a variety of formats
 *
 * @param string $format format to display
 * @param string $poolID pool to display
 */
function display_feed($format, $poolID)
{
	global $tpl;

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	// if we don't have a pool, try to find the most recent pool
	$poolobj = null;
	if (empty($poolID))
		$poolobj = $pools->find()->sort(array('season' => -1))->getNext();
	else
		$poolobj = $pools->findOne(array('_id' => new MongoId($poolID)));

	if (!$poolobj) {
		// we need some pool
		echo "Pool not found";
		return;
	}

	$actions = array();

	$updated = null;

	if (isset($poolobj['actions'])) {
		foreach ($poolobj['actions'] as $action) {
		
			// load user data
			if (!empty($action['user'])) {
				$action['user'] = get_user($action['user']);
				if (!empty($action['user'])) {
					// use the readable name from the user record
					// if the record still exists
					$action['user_name'] = user_readable_name($action['user']);
				}
			}

			// load admin data
			if (!empty($action['admin'])) {
				$action['admin'] = get_user($action['admin']);
				if (!empty($action['admin'])) {
					// use the readable name from the user record
					// if the record still exists
					$action['admin_name'] = user_readable_name($action['admin']);
				}
			}

			// load team data
			if (!empty($action['team'])) {
				$action['team'] = get_team($action['team']);
			}

			// load from team data (for edits)
			if (!empty($action['from_team'])) {
				$action['from_team'] = get_team($action['from_team']);
			}

			// load to team data (for edits)
			if (!empty($action['to_team'])) {
				$action['to_team'] = get_team($action['to_team']);
			}

			// format times with timezones
			$sec = $action['time']->sec;
			if (($format == 'html') || ($format == 'js')) {
				$action['time'] = get_local_datetime($action['time']);
			} else {
				$action['time'] = new DateTime('@' . $action['time']->sec);
			}

			// keep the most recent updated time
			if ((!$updated) || ((int)$updated->format('U') < $sec))
				$updated = $action['time'];

			// index by time
			$actions[$sec][] = $action;

		}
	}

	// sort by time descending
	krsort($actions);

	// set data
	$tpl->assign('pool', $poolobj);
	if ($updated)
		$tpl->assign('updated', $updated);
	if (count($actions) > 0)
		$tpl->assign('actions', $actions);
	$tpl->assign('domain', trim($_SERVER['HTTP_HOST'], '/'));
	$tpl->assign('self', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
	
	if ($format == 'atom') {
		// display atom feed
		header('Content-type: application/atom+xml; charset=UTF-8');
		$tpl->display('atom.tpl');
	} else if ($format == 'rss') {
		// display rss feed
		header('Content-type: text/xml; charset=UTF-8');
		$tpl->display('rss.tpl');
	} else if ($format == 'js') {
		http_headers();
		$tpl->assign('js', true);
		$tpl->display('history.tpl');
	} else if ($format == 'html') {
		// display history html page
		http_headers();
		$tpl->display('history.tpl');
	}

}
