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
	global $tpl, $tote_conf, $db;

	http_headers();

	if (!empty($poolid)) {
		$poolstmt = $db->prepare('SELECT pools.name, seasons.year AS season FROM ' . TOTE_TABLE_POOLS . ' AS pools LEFT JOIN ' . TOTE_TABLE_SEASONS . ' AS seasons ON pools.season_id=seasons.id WHERE pools.id=:pool_id');
		$poolstmt->bindParam(':pool_id', $poolid, PDO::PARAM_INT);
		$poolstmt->execute();
		$pool = $poolstmt->fetch(PDO::FETCH_ASSOC);

		if ($pool) {
			$tpl->assign('pool', $pool);
		}

		$poolstmt = null;
	}

	$payoutpercents = get_pool_payout_percents($poolid);
	if (count($payoutpercents) > 0)
		$tpl->assign('payoutpercents', $payoutpercents);

	$admins = get_pool_administrators($poolid);
	if (count($admins) > 0)
		$tpl->assign('admins', $admins);

	if ($output == 'js')
		$tpl->assign('js', true);

	$fromemail = getenv('TOTE_EMAIL_FROM');
	if (empty($fromemail) && !empty($tote_conf['fromemail'])) {
		$fromemail = $tote_conf['fromemail'];
	}

	if (!empty($fromemail)) {
		$tpl->assign('email', $fromemail);
	}

	$tpl->display('rules.tpl');
}
