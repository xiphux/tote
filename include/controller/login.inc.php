<?php

function display_login()
{
	global $tpl;

	if (isset($_SESSION['user'])) {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/index.php');
		return;
	}

	$tpl->display('login.tpl');

}
