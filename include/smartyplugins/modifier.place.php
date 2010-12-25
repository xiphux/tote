<?php

/**
 * smarty_modifier_place
 *
 * turns an integer into a placement
 *
 * @param string $num number
 * @return string placement string
 */
function smarty_modifier_place($num)
{
	$num = (int)$num;

	if ($num < 1)
		return '';

	if (($num >= 4) && ($num <= 20)) {
		// 4th - 20th all end in 'th'
		return $num . 'th';
	}

	$rem = $num % 10;	// last digit

	if ($rem == 1)
		return $num . 'st';
	else if ($rem == 2)
		return $num . 'nd';
	else if ($rem == 3)
		return $num . 'rd';

	return $num . 'th';
}
