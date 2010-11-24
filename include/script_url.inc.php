<?php

/**
 * Get the running script url, optionally with parameters
 *
 * @param array $params key/value map of get variables
 * @return string url
 */
function script_url($params = null)
{
	$url = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php';

	if (!empty($params) && (count($params) > 0)) {
		// build get variables into query string
		$first = true;
		foreach ($params as $key => $val) {
			if (!(empty($key) || empty($val))) {
				if ($first) {
					$url .= '?';
					$first = false;
				} else {
					$url .= '&';
				}
				$url .= $key . '=' . $val;
			}
		}
	}

	return $url;
}
