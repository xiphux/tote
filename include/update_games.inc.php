<?php

require_once(TOTE_INCLUDEDIR . 'update_games_espn.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_games_nfl.inc.php');

function update_games()
{
	update_games_espn();
	update_games_nfl();
}
