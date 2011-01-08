<?php

/**
 * Tests if browser is a mobile device
 *
 * @return boolean true if mobile browser
 */
function mobile_browser()
{
	// TODO make this more robust rather than just
	// hardcoding a few cases

	$ua = $_SERVER['HTTP_USER_AGENT'];

	if ((stripos($ua, 'iPhone') !== false) && (stripos($ua, 'Mobile') !== false)) {
		// iphone
		return true;
	}

	if ((stripos($ua, 'iPod') !== false) && (stripos($ua, 'Mobile') !== false)) {
		// ipod touch
		return true;
	};

	if ((stripos($ua, 'iPad') !== false) && (stripos($ua, 'Mobile') !== false)) {
		// ipad
		return true;
	}

	if (stripos($ua, 'Android') !== false) {
		// android
		return true;
	}

	if (stripos($ua, 'webOS') !== false) {
		// webos
		return true;
	}

	if (stripos($ua, 'IEMobile') !== false) {
		// windows mobile
		return true;
	}

	if (stripos($ua, 'BlackBerry') !== false) {
		// blackberry
		return true;
	}

	return false;
}
