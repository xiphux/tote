<?php

require_once(TOTE_INCLUDEDIR . 'redirect.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');
require_once(TOTE_INCLUDEDIR . 'user_logged_in.inc.php');

function display_feed($format, $poolID)
{
	global $tpl;

	$pools = get_collection(TOTE_COLLECTION_POOLS);

	$user = null;
	if ($format == 'html') {
		$user = user_logged_in();
		//if (!$user) {
		//	return redirect();
		//}
	}

	$poolobj = null;

	if (empty($poolID))
		$poolobj = $pools->find()->sort(array('season' => -1))->getNext();
	else
		$poolobj = $pools->findOne(array('_id' => new MongoId($poolID)));

	if (!$poolobj) {
		echo "Pool not found";
		return;
	}

	$actions = array();

	$updated = null;

	if (isset($poolobj['actions'])) {
		foreach ($poolobj['actions'] as $action) {
			
			if (!empty($action['user'])) {
				$action['user'] = get_user($action['user']);
				if (!empty($action['user'])) {
					$action['user_name'] = $action['user']['username'];
					if (!empty($action['user']['first_name'])) {
						$action['user_name'] = $action['user']['first_name'];
						if (!empty($action['user']['last_name']))
							$action['user_name'] .= ' ' . $action['user']['last_name'];
					}
				}
			}
			if (!empty($action['admin'])) {
				$action['admin'] = get_user($action['admin']);
				if (!empty($action['admin'])) {
					$action['admin_name'] = $action['admin']['username'];
					if (!empty($action['admin']['first_name'])) {
						$action['admin_name'] = $action['admin']['first_name'];
						if (!empty($action['admin']['last_name']))
							$action['admin_name'] .= ' ' . $action['admin']['last_name'];
					}
				}
			}

			if (!empty($action['team'])) {
				$action['team'] = get_team($action['team']);
			}
			if (!empty($action['from_team'])) {
				$action['from_team'] = get_team($action['from_team']);
			}
			if (!empty($action['to_team'])) {
				$action['to_team'] = get_team($action['to_team']);
			}

			$sec = $action['time']->sec;
			$action['time'] = new DateTime('@' . $sec);
			if ($format == 'html') {
				if ($user && !empty($user['timezone'])) {
					$action['time']->setTimezone(new DateTimeZone($user['timezone']));
				} else {
					$action['time']->setTimezone(new DateTimeZone('America/New_York'));
				}
			}
			if ((!$updated) || ((int)$updated->format('U') < $sec))
				$updated = $action['time'];

			$actions[$sec][] = $action;

		}
	}

	krsort($actions);

	$tpl->assign('pool', $poolobj);
	if ($updated)
		$tpl->assign('updated', $updated);
	if (count($actions) > 0)
		$tpl->assign('actions', $actions);
	$tpl->assign('domain', trim($_SERVER['HTTP_HOST'], '/'));
	$tpl->assign('self', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
	
	if ($format == 'atom') {
		header('Content-type: application/atom+xml; charset=UTF-8');
		$tpl->display('atom.tpl');
	} else if ($format == 'rss') {
		header('Content-type: text/xml; charset=UTF-8');
		$tpl->display('rss.tpl');
	} else if ($format == 'html') {
		$tpl->display('history.tpl');
	}

}
