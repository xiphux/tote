<?php

/**
 * rules controller
 *
 * display the rules page
 *
 * @param string $output output mode
 */
function display_rules($output = 'html')
{
	global $tpl, $tote_conf;

	if ($output == 'js')
		$tpl->assign('js', true);

	if (!empty($tote_conf['fromemail'])) {
		$tpl->assign('email', $tote_conf['fromemail']);
	}

	$tpl->display('rules.tpl');
}
