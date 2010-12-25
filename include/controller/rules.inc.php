<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_payout_percents.inc.php');

/**
 * rules controller
 *
 * display the rules page
 *
 * @param string pool id
 * @param string $output output mode
 */
function display_rules($poolid, $output = 'html')
{
	global $tpl, $tote_conf;

	$payout = get_pool_payout_percents($poolid);
	if (count($payout) > 0)
		$tpl->assign('payout', $payout);

	if ($output == 'js')
		$tpl->assign('js', true);

	if (!empty($tote_conf['fromemail'])) {
		$tpl->assign('email', $tote_conf['fromemail']);
	}

	$tpl->display('rules.tpl');
}
