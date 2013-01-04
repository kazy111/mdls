<?php

	include dirname(__FILE__) . '/header.php';

	$type = (int)$_GET['type'];
	
	$service = $_SESSION['user']->get_service($type);
	if($service){
		$manager->delete_service($service);
		$_SESSION['user']->set_service($type, FALSE);
	}

	redirect('index.php');
	
	//print_r($service);

?>