<?php
include_once 'service.php';

class service_soundcloud implements service {
	private $user_id;
	// OAuth parameters
	private static $client_id = 'cd9fecf57e8187dab00f0460cbd68316';
	private static $client_secret = '3da39ab840f28a0379ca4c1178094956';
	private static $type = service_type::soundcloud;
	private $access_token;
	private $access_token_secret;
	private $access_token_expire;
	private $refresh_token;
	private $enable;
	private $service_user;
	private $public;
	private $last_update;
	private $refreshed = FALSE;

	function __construct($user_id){
		$this->user_id = $user_id;
	}
	public function get_user_id() { return $this->user_id; } 
	public function get_client_id() { return $this->client_id; } 
	public function get_client_secret() { return $this->client_secret; } 
	public function get_access_token() { return $this->access_token; } 
	public function get_access_token_secret() { return $this->access_token_secret; } 
	public function get_access_token_expire() { return $this->access_token_expire; } 
	public function get_refresh_token() { return $this->refresh_token; } 
	public function get_enable() { return $this->enable; } 
	public function get_service_user() { return $this->service_user; } 
	public function get_public() { return $this->public; } 
	public function get_type() { return self::$type; }
	public function get_last_update() { return $this->last_update; }
	public function get_refreshed() { return $this->refreshed; }
	public function set_user_id($x) { $this->user_id = $x; } 
	public function set_client_id($x) { $this->client_id = $x; } 
	public function set_client_secret($x) { $this->client_secret = $x; } 
	public function set_access_token($x) { $this->access_token = $x; } 
	public function set_access_token_secret($x) { $this->access_token_secret = $x; } 
	public function set_access_token_expire($x) { $this->access_token_expire = $x; } 
	public function set_refresh_token($x) { $this->refresh_token = $x; } 
	public function set_enable($x) { $this->enable = $x; } 
	public function set_service_user($x) { $this->service_user = $x; }
	public function set_public($x) { $this->public = $x; } 
	public function set_last_update($x) { $this->last_update = $x; }


	public static function auth()
	{

		$client = new oauth_client_class;
		$client->server = 'SoundCloud';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
			dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/connect_service.php?type='.self::$type;

		$client->client_id = self::$client_id; //$application_line = __LINE__;
		$client->client_secret = self::$client_secret;

		// if(strlen($client->client_id) == 0
		// || strlen($client->client_secret) == 0)
		// 	die('Please go to Google APIs console page '.
		// 		'http://code.google.com/apis/console in the API access tab, '.
		// 		'create a new client ID, and in the line '.$application_line.
		// 		' set the client_id to Client ID and client_secret with Client Secret. '.
		// 		'The callback URL must be '.$client->redirect_uri.' but make sure '.
		// 		'the domain is valid and can be resolved by a public DNS.');

		/* API permissions
		 */
		if(($success = $client->Initialize()))
		{
			//$client->session_started = true;
			//$_SESSION['OAUTH_ACCESS_TOKEN'][$client->access_token_url] = $access_token;

			if(($success = $client->Process()))
			{
				if(strlen($client->authorization_error))
				{
					$client->error = $client->authorization_error;
					$success = false;
				}
				elseif(strlen($client->access_token))
				{
					$success = $client->CallAPI(
						'https://api.soundcloud.com/me.json',
						'GET', array(), array('FailOnAccessError'=>true), $user);
				}
			}
			$success = $client->Finalize($success);
		}
		if($client->exit)
			exit;

		if($success) {
			// create service object and save
			$service = service_factory::create_instance($_SESSION['user']->id, self::$type);
			$service->set_access_token($client->access_token);
			$service->set_access_token_secret($client->access_token_secret);
			$service->set_access_token_expire($client->access_token_expiry);
			$service->set_refresh_token($client->refresh_token);
			$service->set_enable(TRUE);
			$service->set_service_user((string)$user->id);
			$service->set_public(FALSE);

			return $service;
		} else {
			return false;
		}

	}

	public function auth_refresh()		// when token expire, refresh token
	{

	}

	private function request($uri, $method, $parameters, $options)
	{
		if(strlen($this->get_access_token()) === 0){ return FALSE; }

		$access_token = array();
		$access_token['authorized'] = TRUE;
		$access_token['value'] = $this->get_access_token();
		$access_token['secret'] = $this->get_access_token_secret();
		$access_token['expiry'] = $this->get_access_token_expire();
		$access_token['refresh_token'] = $this->get_refresh_token();

		$client = new oauth_client_class;
		$client->server = 'SoundCloud';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
			dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/connect_service.php?type='.self::$type;

		$client->client_id = self::$client_id;
		$client->client_secret = self::$client_secret;

		/* API permissions
		 */
		if(($success = $client->Initialize()))
		{
			$client->session_started = true;
			$_SESSION['OAUTH_ACCESS_TOKEN'][$client->access_token_url] = $access_token;

			if(($success = $client->Process()))
			{
				if(strlen($client->authorization_error))
				{
					$client->error = $client->authorization_error;
					$success = false;
				}
				elseif(strlen($client->access_token))
				{
					if($this->get_access_token() !== $client->access_token){
						$this->set_access_token($client->access_token);
						$this->set_access_token_secret($client->access_token_secret);
						$this->set_access_token_expire($client->access_token_expiry);
						$this->set_refresh_token($client->refresh_token);
						$this->refreshed = TRUE;
					}
					$success = $client->CallAPI(
						$uri, $method, $parameters, $options, $result);
				}
			}
			$success = $client->Finalize($success);
		}
		if($client->exit)
			exit;

		if($success) {
			return $result;
		} else {
			return false;
		}
	}

	public function update_timeline($manager)	// update timeline
	{

	}
	public function update_likes($manager)		// update likes
	{

		$offset = 1;
		$max_results = 20;

		$insert_count = 0;
		$update_date = strtotime('now');
		$this->set_last_update(date('c', $update_date));
		// get recent user events
		while(true) {

			$uri = 'https://api.soundcloud.com/me/favorites?limit='.$max_results.'&offset='.$offset;

			$items = $this->request($uri, 'GET', array(), array('Accept'=>'application/json','FailOnAccessError'=>true));

			//print_r($items);
			if (!$items || count($items) === 0 ) {
				break;
			}
			
			foreach ($items as $e) {
				if(property_exists($e, 'artwork_url') && $e->artwork_url){
					$thumbnail = $e->artwork_url;
				} else if (property_exists($e->user, 'avatar_url')) {
					$thumbnail = $e->user->{'avatar_url'};
				} else {
					$thumbnail = '';
				}
	  			$item = New item(NULL, $e->id, self::$type, $e->permalink_url, $e->title, $e->user->username, $e->description, $e->genre, date('c', strtotime($e->created_at)), $thumbnail, strtr($e->tag_list, ' ', ','));

				//print_r($e);

				$insert_count = $manager->insert_likes($this->get_user_id(), $item, date('c', $update_date));
				//$insert_count = $manager->insert_likes($this->get_user_id(), $item, date('c', strtotime($e->created_at)));
				
				if( $insert_count === 0) {
					break;
				} else {
					$update_date -= 1;
				}
			}

			if( $insert_count === 0) {
				break;
			}

			$offset += $max_results;
		}

		$manager->update_service_last_update($this);
		if($this->get_refreshed()){
			$manager->upsert_service($this);
		}
	}

	// TODO import list

	public function parse_item($item){

	}
}
