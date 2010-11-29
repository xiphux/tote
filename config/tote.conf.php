<?php

/**
 * connectionString
 *
 * connection string to use to connect to mongodb
 * empty uses default host/port (localhost:27017)
 */
$tote_conf['connectionString'] = 'mongodb://tote:taxA2t2GeneTrudR@localhost/tote';

/**
 * database
 *
 * name of dataspace to use
 */
$tote_conf['database'] = 'tote';

/**
 * namespace
 *
 * namespace prefix to use in database
 */
$tote_conf['namespace'] = '';

/**
 * smarty
 *
 * path to smarty library
 */
$tote_conf['smarty'] = '/home/xiphux/smarty/';

/**
 * sitename
 *
 * name of site to use in various displays/emails
 */
$tote_conf['sitename'] = 'football.chris-han.net';

/**
 * fromemail
 *
 * email address to use as from/reply-to in website
 * mailings
 */
$tote_conf['fromemail'] = 'xiphux@gmail.com';

/**
 * bccemail
 *
 * email address to BCC all outgoing emails to
 * (for debugging purposes)
 */
//$tote_conf['bccemail'] = 'xiphux@gmail.com';

/**
 * reminders
 *
 * set to true to enable the reminder settings
 * note this also depends on a background cron job
 * running the reminders.php script once an hour
 * or more often
 */
$tote_conf['reminders'] = true;
