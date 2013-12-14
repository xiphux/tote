<?php

require_once(TOTE_INCLUDEDIR . 'update_games.inc.php');
require_once(TOTE_INCLUDEDIR . 'import_point_spreads.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

http_headers();

update_games();

// import point spreads
import_point_spreads();
