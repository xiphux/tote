<?php

require_once(TOTE_INCLUDEDIR . 'pick_distribution.inc.php');

/**
 * graphdata controller
 *
 * load graph data
 *
 * @param string $graphtype graph type
 */
function display_graphdata($graphtype)
{
	global $tpl;

	if (empty($graphtype)) {
		return;
	}

	$data = null;

	switch ($graphtype) {
		case 'pickdist':
			$data = pick_distribution();
			break;
		default:
			return;
	}

	if ($data) {
		header('Content-Type: application/json');
		$tpl->assign('data', json_encode($data));
		$tpl->display('data.tpl');
	}
}
