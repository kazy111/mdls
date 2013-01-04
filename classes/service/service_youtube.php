<?php
include_once 'service.php';

class service_youtube implements service {
	private $user_id;
	// OAuth parameters
	private static $client_id = '882062043049.apps.googleusercontent.com';
	private static $client_secret = '5Cd1NEvtPd1sVh2U-D1iUc_v';
	private static $developer_key = 'AI39si5FadVNSe6AVO1uXf9mCrBTnHcY6zkIq7OwTp7L56UZkdf16WqAgOo9qppn1V0Z_i1dS9liVSDHNGhUCrmrKchGARYDgA';
	private static $type = service_type::youtube;
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
	public function set_last_update($x) {
		if(is_string($x)){
			$x = date('c', strtotime($x));
		}
		$this->last_update = $x;
	}


	public static function auth()
	{

		$client = new oauth_client_class;
		$client->server = 'Google';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
			dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/connect_service.php?type='.self::$type;

		$client->client_id = self::$client_id;
		$client->client_secret = self::$client_secret;

		/* API permissions
		 */
		$client->scope = 'http://gdata.youtube.com';
		if(($success = $client->Initialize()))
		{
			if(($success = $client->Process()))
			{
				if(strlen($client->authorization_error))
				{
					$client->error = $client->authorization_error;
					$success = FALSE;
				}
				elseif(strlen($client->access_token))
				{
					$success = $client->CallAPI(
						'http://gdata.youtube.com/feeds/api/users/default?v=2',
						'GET', array(), array('FailOnAccessError'=>TRUE), $user);
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
			$service->set_service_user((string)$user->title);
			$service->set_public(FALSE);

			return $service;
		} else {
			return FALSE;
		}

	}

	public function auth_refresh()		// when token expire, refresh token
	{

	}

	public function update_timeline($manager)	// update timeline
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
		$client->server = 'Google';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
			dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/connect_service.php?type='.self::$type;

		$client->client_id = self::$client_id;
		$client->client_secret = self::$client_secret;

		/* API permissions
		 */
		$client->scope = 'http://gdata.youtube.com';
		if(($success = $client->Initialize()))
		{
			$client->session_started = TRUE;
			$_SESSION['OAUTH_ACCESS_TOKEN'][$client->access_token_url] = $access_token;

			if(($success = $client->Process()))
			{
				if(strlen($client->authorization_error))
				{
					$client->error = $client->authorization_error;
					$success = FALSE;
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
			return FALSE;
		}
	}

	public function update_likes($manager)		// update likes
	{

		$index = 1;
		$max_results = 20;
		$rated = array();
		$xml = array();
		$updated = '';
		$max_updated = date(0);
		// get recent user events
		while(TRUE) {

			$events = $this->request('http://gdata.youtube.com/feeds/api/users/default/events?v=2&start-index='.$index.'&max-results='.$max_results.'&key='.self::$developer_key, 'GET', array(), array('FailOnAccessError'=>TRUE));
			//print_r($events);
			if (!$events) {
				return FALSE;
			}
			if (!$events->entry ) {
				break;
			}
			foreach ($events->entry as $e) {
				$updated = date('c', strtotime((string)$e->updated));
				if($updated > $max_updated){
					$max_updated = $updated;
				}
				//print($updated . ' < ' . $this->get_last_update() . '<br/>');
				if($updated <= $this->get_last_update()){
					break;
				}

				$yt = $e->children('http://gdata.youtube.com/schemas/2007');
				//print_r($yt);
				if(property_exists($yt, 'rating')){
					//print_r($yt);
					$rated[(string)$yt->videoid] = array('liked'=>$updated);
					$xml[] = '<entry><id>http://gdata.youtube.com/feeds/api/videos/'.(string)$yt->videoid.'</id></entry>';
				}
			}

			if($updated < $this->get_last_update()){
				break;
			}
			$index += $max_results;
		}

		$this->set_last_update($max_updated);

		// get info of each rated videos
  		$xml_chunked = array_chunk($xml, 50);
  		//print_r($xml_chunked);
  		foreach($xml_chunked as $chunk){
			$req = '<feed xmlns=\'http://www.w3.org/2005/Atom\'  xmlns:media=\'http://search.yahoo.com/mrss/\' '
				  .' xmlns:batch=\'http://schemas.google.com/gdata/batch\' '
	 			  .' xmlns:yt=\'http://gdata.youtube.com/schemas/2007\'> '
				  .'<batch:operation type=\'query\'/>'.implode('', $chunk) . '</feed>';
			$stream = stream_context_create(array('http' => array(
				'method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'content'   => $req,
			)));
			//print_r(file_get_contents('http://gdata.youtube.com/feeds/api/videos/batch?v=2', false, $stream));
			$videos = simplexml_load_string(file_get_contents('http://gdata.youtube.com/feeds/api/videos/batch?v=2&key='.self::$developer_key, FALSE, $stream), 'SimpleXMLElement', LIBXML_NOCDATA);
			//print_r($videos);
			foreach ($videos->entry as $v) {
				$media = $v->children('http://search.yahoo.com/mrss/');
				$yt = $media->group->children('http://gdata.youtube.com/schemas/2007');
				if(!property_exists($yt, 'videoid')) { break; } // error
				//print_r($media->group->thumbnail[0]->attributes()->url);
				$rated[(string)$yt->videoid]['title'] = (string)$v->title;
				$rated[(string)$yt->videoid]['published'] = date('c', strtotime((string)$v->published));
				$rated[(string)$yt->videoid]['author'] = (string)$v->author->name;
				$rated[(string)$yt->videoid]['thumbnail'] = (string)$media->group->thumbnail[0]->attributes()->url;
				$rated[(string)$yt->videoid]['category'] = (string)$media->group->category;
				$rated[(string)$yt->videoid]['description'] = (string)$media->group->description;
				$rated[(string)$yt->videoid]['uri'] = 'http://www.youtube.com/watch?v='.(string)$yt->videoid;
			}
  		}
			
  		foreach($rated as $k => $v){
  			$item = New item(NULL, $k, self::$type, $v['uri'], $v['title'], $v['author'], $v['description'], $v['category'], $v['published'], $v['thumbnail'], '');
  			$manager->insert_likes($this->user_id, $item, $v['liked']);
  		}

  		$manager->update_service_last_update($this);
		if($this->get_refreshed()){
			$manager->upsert_service($this);
		}
		return TRUE;
	}

	// TODO import list

	public function parse_item($item){

	}
}
