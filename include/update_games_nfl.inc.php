<?php

require_once(TOTE_INCLUDEDIR . 'load_page.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_scheduled_game.inc.php');
require_once(TOTE_INCLUDEDIR . 'update_finished_game.inc.php');
require_once(TOTE_INCLUDEDIR . 'get_current_season.inc.php');

define('NFL_BASEURL', 'http://www.nfl.com/ajax/scorestrip');
define('NFL_WEEKURL', NFL_BASEURL . '?season=%d&seasonType=%s&week=%d');
define('NFL_LIVEURL', 'http://www.nfl.com/liveupdate/scorestrip/ss.xml');
define('NFL_SEASONTYPE_REGULAR', 'REG');

function nfl_team_to_abbr($team)
{
    switch ($team) {
        case 'JAX':
            return 'JAC';
    }
    return $team;
}

function load_nfl_scorestrip($url)
{
    $raw = load_page($url);
    $raw = preg_replace("/>\s+</", "><", $raw);

    $dom = new DOMDocument();
    @$dom->loadXML($raw);

    $ss = $dom->firstChild;
    if ($ss == null || $ss->nodeName != 'ss') {
        echo '<p>Error: invalid scorestrip xml</p>';
        return false;
    }

    $gms = $ss->firstChild;
    if ($gms == null || $gms->nodeName != 'gms') {
        echo '<p>Error: invalid scorestrip xml</p>';
        return false;
    }

    return $gms;
}

function update_games_nfl_gms($gms, $season, $week, $modified)
{
    $y = $gms->attributes->getNamedItem('y');
    if ($y == null || $y->nodeValue != $season) {
        echo '<p>Error: season mismatch</p>';
        return false;
    }

    $w = $gms->attributes->getNamedItem('w');
    if (($w == null) || ($w->nodeValue != $week)) {
        echo '<p>Error: week mismatch</p>';
        return false;
    }

    echo '<p>';

    for ($i = 0; $i < $gms->childNodes->length; ++$i) {

        $g = $gms->childNodes->item($i);
        if ($g == null || $g->nodeName != 'g') {
            continue;
        }

        $h = $g->attributes->getNamedItem('h');
        if ($h == null || empty($h->nodeValue)) {
            continue;
        }
        $home = nfl_team_to_abbr($h->nodeValue);

        $v = $g->attributes->getNamedItem('v');
        if ($v == null || empty($v->nodeValue)) {
            continue;
        }
        $away = nfl_team_to_abbr($v->nodeValue);

        $q = $g->attributes->getNamedItem('q');
        if ($q == null || empty($q->nodeValue)) {
            continue;
        }
        $quarter = $q->nodeValue;

        if ($quarter == 'F' || $quarter == 'FO') {
            // finished game

            $hs = $g->attributes->getNamedItem('hs');
            if ($hs == null) {
                continue;
            }
            $homeScore = $hs->nodeValue;
            
            $vs = $g->attributes->getNamedItem('vs');
            if ($vs == null) {
                continue;
            }
            $awayScore = $vs->nodeValue;

            // echo 'Updating ' . $away . ' ' . $awayScore . ' @ ' . $home . ' ' . $homeScore . "\n";
            if (update_finished_game($season, $week, $away, $awayScore, $home, $homeScore)) {
                $modified = true;
            }
        } else {
            // pending/active game

            $eid = $g->attributes->getNamedItem('eid');
            if ($eid == null || empty($eid->nodeValue)) {
                continue;
            }
            $start = substr($eid->nodeValue, 0, 8);

            $t = $g->attributes->getNamedItem('t');
            if ($t == null || empty($t->nodeValue)) {
                continue;
            }
            $time = $t->nodeValue;

            $timePieces = explode(':', $time);
            if ($timePieces[0] < 12) {
                $morning = false;
                if ($timePieces[0] >= 6 && $i < $gms->childNodes->length - 1) {
                    // morning games are unlikely before 6am
                    $nextg = $gms->childNodes->item($i + 1);
                    if ($nextg != null) {
                        $nexteid = $nextg->attributes->getNamedItem('eid');
                        $nextt = $nextg->attributes->getNamedItem('t');
                        if ($nexteid != null && $nextt != null) {
                            $nextStart = substr($nexteid->nodeValue, 0, 8);
                            $nextTimePieces = explode(':', $nextt->nodeValue);
                            if ($nextStart == $start && $nextTimePieces[0] < $timePieces[0]) {
                                // if there's another game on this day and the hour is less
                                // than this game's hour, this is likely a morning game and
                                // the next game is an afternoon game
                                $morning = true;
                            }
                        }
                    }
                }
                if (!$morning) {
                    $timePieces[0] += 12;
                }
            }

            $tmp = new DateTime($start . 'T' . $timePieces[0] . ':' . $timePieces[1]);

            // echo 'Updating ' . $away . ' @ ' . $home . ' at ' . $tmp->format('D M j, Y g:i a T') . "\n";
            if (update_scheduled_game($season, $week, $away, $home, (int)$tmp->format('U'))) {
                $modified = true;
            }
        }
    }
    
    echo '</p>';

    return true;
}

function update_games_nfl_week($season, $week, $modified)
{
    if (empty($season) || empty($week))
        return false;

    $gms = load_nfl_scorestrip(sprintf(NFL_WEEKURL, $season, NFL_SEASONTYPE_REGULAR, $week));
    if (!$gms) {
        return false;
    }

    echo '<strong>Updating season ' . $season . ' week ' . $week . "...</strong><br />\n";

    return update_games_nfl_gms($gms, $season, $week, $modified);
}

function update_games_nfl_live($season, $modified)
{
    if (empty($season))
        return false;

    $gms = load_nfl_scorestrip(NFL_LIVEURL);
    if (!$gms) {
        return false;
    }

    $w = $gms->attributes->getNamedItem('w');
    if (!$w) {
        echo '<p>Error: unknown week</p>';
        return false;
    }
    $week = $w->nodeValue;
    if (!$week) {
        echo '<p>Error: unknown week</p>';
        return false;
    }

    echo '<strong>Updating season ' . $season . ' week ' . $week . " live...</strong><br />\n";

    return update_games_nfl_gms($gms, $season, $week, $modified);
}

function update_games_nfl($season = null)
{
    if (empty($season))
        $season = get_current_season();

    // times are reported on nfl in Eastern
    $oldtz = date_default_timezone_get();
    date_default_timezone_set('America/New_York');

    echo '<p><strong>Scraping ' . $season . ' scores and schedule from ' . NFL_BASEURL . "...</strong></p>\n";

    $modified = false;

    $weekcount = 17;

    for ($week = 1; $week <= $weekcount; ++$week) {
        if (!update_games_nfl_week($season, $week, $modified))
            break;
    }

    update_games_nfl_live($season, $modified);

    date_default_timezone_set($oldtz);

    return $modified;
}