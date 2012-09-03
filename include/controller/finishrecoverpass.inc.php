<?php

require_once(TOTE_INCLUDEDIR . 'get_collection.inc.php');
require_once(TOTE_INCLUDEDIR . 'generate_salt.inc.php');
require_once(TOTE_INCLUDEDIR . 'http_headers.inc.php');

/**
 * finishrecoverpass controller
 *
 * after user requests password reset, send recovery email
 *
 * @param string $email email
 */
function display_finishrecoverpass($email)
{
	global $tpl, $tote_conf;

	$key = '';
	$username = '';
	$errors = array();

	if (empty($email)) {
		// need the email
		$errors[] = 'Email is required';
	} else {
		$users = get_collection(TOTE_COLLECTION_USERS);

		$userobj = $users->findOne(array('email' => $email));
		if ($userobj) {
			// generate a unique recovery key and store it
			// for the user
			$key = generate_salt();
			$users->update(
				array('_id' => $userobj['_id']),
				array('$set' => array('recoverykey' => $key))
			);
			$username = $userobj['username'];
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
		$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
			'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		mail($email, $subject, $message, $headers);

		// email sent, tell user
		$tpl->assign('email', $email);
		$tpl->display('finishrecoverpass.tpl');
	}

}
