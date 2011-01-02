<?php

/**
 * message controller
 *
 * shows a message to the user
 *
 * @param string $message message string
 * @param string $header header
 */
function display_message($message, $header = '')
{
	global $tpl;

	$tpl->assign('message', $message);

	if (!empty($header)) {
		$tpl->assign('header', $header);
	}

	$tpl->display('message.tpl');
}
