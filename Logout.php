<?php
	include dirname(__FILE__) . '/header.php';

	$_SESSION = array();
	session_destroy();

	redirect('index.php');

?>