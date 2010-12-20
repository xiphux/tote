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
	global $tpl;

	if ($output == 'js')
		$tpl->assign('js', true);

	$tpl->display('rules.tpl');
}
