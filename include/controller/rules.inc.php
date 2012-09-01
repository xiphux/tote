<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_payout_percents.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_administrators.inc.php');

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

	if (!empty($poolid)) {
		$pools = get_collection(TOTE_COLLECTION_POOLS);
		$poolobj = $pools->findOne(
			array('_id' => new MongoId($poolid)),
			array('name', 'season')
		);
		if ($poolobj) {
			$tpl->assign('pool', $poolobj);
		}
	}

	$payoutpercents = get_pool_payout_percents($poolid);
	if (count($payoutpercents) > 0)
		$tpl->assign('payoutpercents', $payoutpercents);

	$admins = get_pool_administrators($poolid);
	if (count($admins) > 0)
		$tpl->assign('admins', $admins);

	if ($output == 'js')
		$tpl->assign('js', true);

	if (!empty($tote_conf['fromemail'])) {
		$tpl->assign('email', $tote_conf['fromemail']);
	}

	$tpl->display('rules.tpl');
}
