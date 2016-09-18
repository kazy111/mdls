<?php
	include dirname(__FILE__) . '/header.php';

	$user_screen_name = $_GET['user'];
	$user = $manager->get_user_by_screen_name($user_screen_name);
    $page_index = ( array_key_exists('p', $_GET) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 0 );
    $items_par_page = 50;
	if(!$user){
		redirect('index.php');
	}

	$page->set_title($user_screen_name);

	$arr = array();
	$arr['id'] = $user->id;
	$arr['user_name'] = $user->user_name;
	$arr['user_screen_name'] = $user->user_screen_name;
	$arr['last_login'] = $user->last_login;
	$user_info = $page->get_once('user_info', $arr);

	$contents_list = '<div>';
	$arr = array();
	// aggregate likes
	$manager->get_likes($user, $page_index, $items_par_page);
	foreach($user->get_list(1) as $item){
		$arr['title'] = $item->get_title();
		$arr['permalink'] = $item->get_permalink();
		$arr['author'] = $item->get_author();
		$arr['post_date'] = $item->get_post_date();
		$arr['thumbnail'] = $item->get_thumbnail();
		$arr['type'] = $item->get_service_type();
		$contents_list .= $page->get_once('list_item', $arr);
	}
	$contents_list .= '</div>';

	$info = $user_info;

	// create pager
	$likes_count = $manager->get_likes_count($user);
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
	$page->set('user', $data);

	include 'footer.php';

?>
