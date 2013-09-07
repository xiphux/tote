<?php

require_once(TOTE_INCLUDEDIR . 'get_pool_payout_percents.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_pool_administrators.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

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
	global $tpl, $tote_conf, $mysqldb;

	http_headers();

	if (!empty($poolid)) {
		$poolstmt = $mysqldb->prepare('SELECT pools.name, seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE pools.id=?');
		$poolstmt->bind_param('i', $poolid);
		$poolstmt->execute();
		$poolresult = $poolstmt->get_result();
		$pool = $poolresult->fetch_assoc();

		if ($pool) {
			$tpl->assign('pool', $pool);
		}

		$poolresult->close();
		$poolstmt->close();
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
