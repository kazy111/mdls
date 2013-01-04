<?php

	include dirname(__FILE__) . '/header.php';

	$type = (int)$_GET['type'];
	
	$service = service_factory::auth($type);

	if($service){
		$manager->upsert_service($service);
		$_SESSION['user']->set_service($type, $service);
		redirect('index.php');
	}
	print_r($service);

?>