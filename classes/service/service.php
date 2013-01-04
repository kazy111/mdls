<?php

interface service {
	function __construct($user);

	public function get_user_id();
	public function get_client_id();
	public function get_client_secret();
	public function get_access_token();
	public function get_access_token_secret();
	public function get_access_token_expire();
	public function get_refresh_token();
	public function get_enable();
	public function get_service_user();
	public function get_public();
	public function get_type();
	public function get_last_update();
	public function set_user_id($x);
	public function set_client_id($x);
	public function set_client_secret($x);
	public function set_access_token($x);
	public function set_access_token_secret($x);
	public function set_access_token_expire($x);
	public function set_refresh_token($x);
	public function set_enable($x);
	public function set_service_user($x);
	public function set_public($x);
	public function set_last_update($x);


	public static function auth();				// redirect to OAuth provider
	public function auth_refresh();		// when token expire, refresh token

	public function update_timeline($manager);	// update timeline
	public function update_likes($manager);		// update likes

	// TODO import list

	public function parse_item($item);

}

?>
