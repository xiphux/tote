<?php

require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * analytics controller
 *
 * display graphs
 *
 * @param string $graphtype graph type
 */
function display_analytics($graphtype)
{
	global $tpl;

	if (empty($graphtype)) {
		$graphtype = 'pickrisk';
	}

	$tpl->assign('graphtype', $graphtype);

	http_headers();
	$tpl->display('analytics.tpl');
}
