<?php

	include('header.php');

	$client = new oauth_client_class;
	$client->server = 'Twitter';
	$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
		dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/Login.php';

	$client->client_id = 'f4a5sgXIOkf3Z5Xy6uyhA'; $application_line = __LINE__;
	$client->client_secret = 'QORdrHMuxuqp7W2ETIKQocSp00DMbayiqg1Fc7G3k';

	if(strlen($client->client_id) == 0
	|| strlen($client->client_secret) == 0)
		die('Please go to Twitter Apps page https://dev.twitter.com/apps/new , '.
			'create an application, and in the line '.$application_line.
			' set the client_id to Consumer key and client_secret with Consumer secret. '.
			'The Callback URL must be '.$client->redirect_uri);

	if(($success = $client->Initialize()))
	{
		if(($success = $client->Process()))
		{
			if(strlen($client->access_token))
			{
				$success = $client->CallAPI(
					'https://api.twitter.com/1.1/account/verify_credentials.json', 
					'GET', array(), array('FailOnAccessError'=>true), $user);
			}
		}
		$success = $client->Finalize($success);
	}
	if($client->exit) // when error
		exit;

	if($success)
	{
		// upsert user, and get user object
		$manager->upsert_user($user->id, $user->screen_name, $user->name, 0);
		$userObj = $manager->get_user($user->id);
		if($userObj){

			$_SESSION['user'] = $userObj;

			// redirect
			redirect('index.php');
		} else {
			// error

		}

		$page->set_title('Twitter OAuth client results');

		$data = array();
		$data['contents'] = '<h1>'. HtmlSpecialChars($user->name)
			.' you have logged in successfully with Twitter!</h1>'
		    .'<pre>'. HtmlSpecialChars(print_r($userObj, 1)). '</pre>';
		$page->set('raw', $data);
	}
	else
	{
		$page->set_title('OAuth client error');

		$data = array();
		$data['contents'] = '<h1>OAuth client error</h1><pre>Error: '. HtmlSpecialChars($client->error).'</pre>';
		$page->set('raw', $data);
	}


	include 'footer.php';
?>