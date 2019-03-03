<?php

// define include directories
define('TOTE_BASEDIR', dirname(__FILE__) . '/');
define('TOTE_CONFIGDIR', TOTE_BASEDIR . 'config/');
define('TOTE_INCLUDEDIR', TOTE_BASEDIR . 'include/');
define('TOTE_CONTROLLERDIR', TOTE_INCLUDEDIR . 'controller/');

require_once(TOTE_CONFIGDIR . 'tote.conf.php');

// create Smarty
require_once('lib/smarty/libs/Smarty.class.php');
$tpl = new Smarty();
$tpl->plugins_dir[] = TOTE_INCLUDEDIR . 'smartyplugins';

date_default_timezone_set('UTC');

foreach (glob('templates/*.tpl') as $template) {
    $templatefile = basename($template);
    $data = $tpl->fetch($templatefile);
}
