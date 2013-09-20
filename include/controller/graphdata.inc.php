<?php

/**
 * graphdata controller
 *
 * load graph data
 *
 * @param string $graphtype graph type
 */
function display_graphdata($graphtype)
{
	if (empty($graphtype)) {
		return;
	}

	$data = null;

	switch ($graphtype) {
		case 'pickrisk':
			require_once(TOTE_INCLUDEDIR . 'pick_risk.inc.php');
			$data = pick_risk();
			break;
		case 'pickdist':
			require_once(TOTE_INCLUDEDIR . 'pick_distribution.inc.php');
			$data = pick_distribution();
			break;
		case 'teamrel':
			require_once(TOTE_INCLUDEDIR . 'team_relationships.inc.php');
			$data = team_relationships();
			break;
		default:
			return;
	}

	if ($data) {
		header('Content-Type: application/json');
		echo json_encode($data);
	}
}
