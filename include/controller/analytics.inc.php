<?php

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

	$tpl->display('analytics.tpl');
}
