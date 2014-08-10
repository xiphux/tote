<?php

require_once(TOTE_INCLUDEDIR . 'generate_salt.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');
require_once(TOTE_INCLUDEDIR . 'send_email.inc.php');

/**
 * finishrecoverpass controller
 *
 * after user requests password reset, send recovery email
 *
 * @param string $email email
 */
function display_finishrecoverpass($email)
{
	global $tpl, $tote_conf, $db;

	$key = '';
	$username = '';
	$userid = null;
	$errors = array();

	if (empty($email)) {
		// need the email
		$errors[] = 'Email is required';
	} else {

		$userstmt = $db->prepare('SELECT id, username FROM ' . TOTE_TABLE_USERS . ' WHERE email=:email');
		$userstmt->bindParam(':email', $email);
		$userstmt->execute();
		$userstmt->bindColumn(1, $userid);
		$userstmt->bindColumn(2, $username);
		$found = $userstmt->fetch(PDO::FETCH_BOUND);
		$userstmt = null;

		if ($found && ($userid != null)) {

			// generate a unique recovery key and store it
			// for the user
			$key = generate_salt();
			
			$setkeystmt = $db->prepare('UPDATE ' . TOTE_TABLE_USERS . ' SET recovery_key=:recovery_key, recovery_key_expiration=DATE_ADD(UTC_TIMESTAMP(), INTERVAL 24 HOUR) WHERE id=:user_id');
			$setkeystmt->bindParam(':recovery_key', $key);
			$setkeystmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
			$setkeystmt->execute();
			$setkeystmt = null;

		} else {
			// can't find that email in the database
			$errors[] = 'That email was not found in the system';
		}
	}

	http_headers();
	if (count($errors) > 0) {
		// if there were errors send back to the recovery form
		// with the errors displayed
		$tpl->assign('errors', $errors);
		$tpl->display('recoverpass.tpl');
	} else {
		// generate and send email
		$tpl->assign('username', $username);
		$tpl->assign('sitename', $tote_conf['sitename']);
		$tpl->assign('url', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php?a=resetpass&k=' . $key);
		$message = $tpl->fetch('recoverpassemail.tpl');
		$subject = 'Password recovery for ' . $tote_conf['sitename'];
		send_email($email, $subject, $message);

		// email sent, tell user
		$tpl->assign('email', $email);
		$tpl->display('finishrecoverpass.tpl');
	}

}
