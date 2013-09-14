<?php

require_once(TOTE_INCLUDEDIR . 'update_games.inc.php');
require_once(TOTE_INCLUDEDIR . 'import_point_spreads.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

http_headers();

update_games();

// import point spreads
$season = (int)date('Y');
if ((int)date('n') < 3)
	$season--;
import_point_spreads($season);
