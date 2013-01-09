<?php
	include dirname(__FILE__) . '/header.php';

	$user_screen_name = $_GET['user'];
	$user = $manager->get_user_by_screen_name($user_screen_name);
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
	$manager->get_likes($user);
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

	$data = array();
	$data['info_data'] = $info;
	$data['item_data'] = $contents_list;
	$page->set('user', $data);

	include 'footer.php';

?>
