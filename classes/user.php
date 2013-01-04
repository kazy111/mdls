<?php

include_once(dirname(__FILE__) . '/service/service_type.php');

class user {
	
	public $id;
	public $user_screen_name;
	public $user_name;
	public $email;
	public $type;
	public $last_login;

	private $services;
	private $lists;

	public function get_service($type)
	{
		return (isset($this->services[$type]) ? $this->services[$type] : FALSE);
	}
	public function set_service($type, $service)
	{
		$this->services[$type] = $service;
	}
	public function get_list($index)
	{
		return (isset($this->lists[$index]) ? $this->lists[$index] : FALSE);
	}
	public function get_lists()
	{
		return $this->lists;
	}
	public function set_list($index, $list)
	{
		$this->lists[$index] = $list;
	}
	public function add_list($list)
	{
		$this->lists[] = $list;
	}

	public function __construct($id, $user_screen_name, $user_name, $type, $email, $last_login)
	{
		$this->id = $id;
		$this->user_screen_name = $user_screen_name;
		$this->user_name = $user_name;
		$this->type = $type;
		$this->email = $email;
		$this->last_login = $last_login;

		$this->services = array();
		$this->lists = array();
	}

	public function update($manager)
	{
		foreach ($this->services as $service) {
			$service->update_likes($manager);
		}
	}

	/**
	 * Notice to user (via twitter)
	 */
	public function notice($message)
	{
		//if(isset($service) && isset($service[service_type::twitter))

	}
}

?>