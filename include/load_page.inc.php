<?php

/**
 * load the HTML content of a url
 *
 * @param string $url url to load
 * @return string HTML content
 */
function load_page($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	ob_start();
	curl_exec($ch);
	curl_close($ch);
	$raw = ob_get_contents();
	ob_end_clean();

	return $raw;
}

