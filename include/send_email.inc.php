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
		
	$headers = 'From: ' . $tote_conf['fromemail'] . "\r\n" .
		'Reply-To: ' . $tote_conf['fromemail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	if ($bcc && !empty($tote_conf['bccemail']))
		$headers .= "\r\nBcc: " . $tote_conf['bccemail'];
		
	return mail($email, $subject, $content, $headers);
}