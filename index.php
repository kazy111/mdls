<?php
	include dirname(__FILE__) . '/header.php';


	$GLOBALS['extra'] = '';//is_mobile() ? 'mobile_' : '';

	$page_index = ( array_key_exists('p', $_GET) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 0 );
	$items_par_page = 50;

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
		$likes_count = $manager->get_likes_count($_SESSION['user']);
		$manager->get_likes($_SESSION['user'], $page_index, $items_par_page);
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
		$likes_count = 0;
	}

	$info = $user_link;
	$info .= '<div>'.$service_link.'</div>';


	// create pager
	$max_pages = (int)($likes_count / $items_par_page);
	$pager_info = '<div>';
	for( $i = 0; $i <= $max_pages; $i++){
		if($i == $page_index){
			$pager_info .= ' ' . ($i + 1);
		} else {
			$pager_info .= ' <a href='.get_url_query(Array('p'=>$i)).'>' . ($i + 1) . '</a>';
		}
	}
	$pager_info .= '</div>';

	$data = array();
	$data['page_index'] = $page_index;
	$data['items_par_page'] = $items_par_page;
	$data['max_pages'] = $max_pages;
	$data['item_count'] = $likes_count;
	$data['info_data'] = $info;
	$data['pager_data'] = $pager_info;
	$data['item_data'] = $contents_list;
	$page->set('index', $data);

	include 'footer.php';



	//print_r($_SESSION);
?>
