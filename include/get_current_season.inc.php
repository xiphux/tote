<?php

function get_current_season()
{
	$season = (int)date('Y');
	if ((int)date('n') < 3)
		$season--;
	return $season;
}
