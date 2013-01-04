<?php
	include dirname(__FILE__) . '/header.php';


	$GLOBALS['extra'] = '';//is_mobile() ? 'mobile_' : '';


	$page->set_title('Index');

	$user_link = '';


	$service_link = '';
	if(isset($_SESSION['user'])) {
		// already logined
		$user_link = '<a id="user_link" href="http://twitter.com/'.$_SESSION['user']->user_screen_name.'">'.$_SESSION['user']->user_screen_name . '</a>';
		$user_link .= ' <a id="logout_link" href="Logout.php">Logout</a>';

		if( $_SESSION['user']->get_service(service_type::youtube) ) {
			$service_link .= '<li>Connected to Youtube: '. $_SESSION['user']->get_service(service_type::youtube)->get_service_user() . ' <a href="disconnect_service.php?type='.service_type::youtube.'">disconnect</a>';
		} else {
			$service_link .= '<li><a class="service_link" href="connect_service.php?type='.service_type::youtube.'">Connect to YouTube</a>';
		}


		if( $_SESSION['user']->get_service(service_type::vimeo) ) {
			$service_link .= '<li>Connected to Vimeo: '. $_SESSION['user']->get_service(service_type::vimeo)->get_service_user() . ' <a href="disconnect_service.php?type='.service_type::vimeo.'">disconnect</a>';
		} else {
			$service_link .= '<li><a id="login_link" href="connect_service.php?type='.service_type::vimeo.'">Connect to Vimeo</a>';
		}

		if( $_SESSION['user']->get_service(service_type::soundcloud) ) {
			$service_link .= '<li>Connected to SoundCloud: '. $_SESSION['user']->get_service(service_type::soundcloud)->get_service_user() . ' <a href="disconnect_service.php?type='.service_type::soundcloud.'">disconnect</a>';
		} else {
			$service_link .= '<li><a id="login_link" href="connect_service.php?type='.service_type::soundcloud.'">Connect to SoundCloud</a>';
		}

		$contents_list = '<div>';
		$arr = array();
		$manager->get_likes($_SESSION['user']);
		foreach($_SESSION['user']->get_list(1) as $item){
			$arr['title'] = $item->get_title();
			$arr['permalink'] = $item->get_permalink();
			$arr['author'] = $item->get_author();
			$arr['post_date'] = $item->get_post_date();
			$arr['thumbnail'] = $item->get_thumbnail();
			$arr['type'] = $item->get_service_type();
			$contents_list .= $page->get_once('list_item', $arr);
		}
		$contents_list .= '</div>';
	} else {
		// not logined
		$user_link = '<a id="login_link" href="Login.php">Login with Twitter</a>';
		$contents_list = '';
	}

	$info = $user_link;
	$info .= '<div>'.$service_link.'</div>';



	$data = array();
	$data['info_data'] = $info;
	$data['item_data'] = $contents_list;
	$page->set('index', $data);

	include 'footer.php';



	//print_r($_SESSION);
?>
