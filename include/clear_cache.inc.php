<?php

/**
 * clear_cache
 *
 * clears cache of a certain type
 *
 * @param string $key cache key toclear
 */
function clear_cache($type)
{
	global $tote_conf;

	if (empty($type))
		return;

	if (empty($tote_conf['cache']) || ($tote_conf['cache'] !== true))
		return;

	$cachetpl = new Smarty;
	$cachetpl->caching = 2;
	$cachetpl->clear_cache(null, $type);
}
