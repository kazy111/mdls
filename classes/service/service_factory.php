<?php

class service_factory {

	static function create_instance($user_id, $type)
	{
		switch ($type) {
			case service_type::youtube:
				return New service_youtube($user_id);
				break;
			case service_type::vimeo:
				return New service_vimeo($user_id);
				break;
			case service_type::soundcloud:
				return New service_soundcloud($user_id);
				break;
			
			default:
				return FALSE;
				break;
		}
	}

	static function auth($type)
	{

		switch ($type) {
			case service_type::youtube:
				return service_youtube::auth();
				break;
			case service_type::vimeo:
				return service_vimeo::auth();
				break;
			case service_type::soundcloud:
				return service_soundcloud::auth();
				break;
			
			default:
				return FALSE;
				break;
		}
	}

}

?>