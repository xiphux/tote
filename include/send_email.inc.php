<?php

/**
 * Send an email
 *
 * @param string $email address to send to
 * @param string $subject mail subject
 * @param string $content mail content
 * @param boolean $bcc true to bcc to self address (if defined)
 * @return boolean true on success, false otherwise
 */
function send_email($email, $subject, $content, $bcc = false)
{
	global $tote_conf;

	$fromemail = getenv('TOTE_EMAIL_FROM');
	if (empty($fromemail) && !empty($tote_conf['fromemail'])) {
		$fromemail = $tote_conf['fromemail'];
	}

	$bccemail = getenv('TOTE_EMAIL_BCC');
	if (empty($bccemail) && !empty($tote_conf['bccemail'])) {
		$bccemail = $tote_conf['bccemail'];
	}
		
	$headers = 'From: ' . $fromemail . "\r\n" .
		'Reply-To: ' . $fromemail . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	if ($bcc && !empty($bccemail))
		$headers .= "\r\nBcc: " . $bccemail;
		
	return mail($email, $subject, $content, $headers);
}