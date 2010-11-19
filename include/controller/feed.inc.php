<?php

require_once(TOTE_INCLUDEDIR . 'get_team.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_user.inc.php');

function display_feed($format, $poolID)
{
	global $db, $tote_conf, $tpl;

	$usercol = 'users';
	$poolcol = 'pools';
	if (!empty($tote_conf['namespace'])) {
		$poolcol = $tote_conf['namespace'] . '.' . $poolcol;
		$usercol = $tote_conf['namespace'] . '.' . $usercol;
	}

	$pools = $db->selectCollection($poolcol);
	$users = $db->selectCollection($usercol);

	if (($format == 'html') && !isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$userobj = null;
	if ($format == 'html') {
		$userobj = $users->findOne(array('username' => $_SESSION['user']), array('timezone'));
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
			}
			if (!empty($action['admin'])) {
				$action['admin'] = get_user($action['admin']);
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
				if ($userobj && !empty($userobj['timezone'])) {
					$action['time']->setTimezone(new DateTimeZone($userobj['timezone']));
				} else {
					$action['time']->setTimezone(new DateTimeZone('America/New_York'));
				}
			}
			if ((!$updated) || ($updated->getTimestamp() < $sec))
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
