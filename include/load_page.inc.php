<?php

/**
 * load the HTML content of a url
 *
 * @param string $url url to load
 * @return string HTML content
 */
function load_page($url)
{
	return file_get_contents($url);
}

