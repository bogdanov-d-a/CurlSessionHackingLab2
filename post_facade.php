<?php

include('post_tools.php');

class PostFacade
{
	static $cookies_file = 'cookies.txt';

	function LoginAndPost($username, $password, $comment)
	{
		PostTools::get_initial_cookies(PostFacade::$cookies_file);
		sleep(1);

		$session_id = PostTools::get_session_from_cookies(PostFacade::$cookies_file);

		PostTools::log_in(PostFacade::$cookies_file, $session_id, $username, $password, PostFacade::$cookies_file);
		sleep(1);

		$story_id = PostTools::get_first_story_id();
		sleep(1);

		PostTools::post_comment(PostFacade::$cookies_file, $session_id, $story_id, $comment);

		return $story_id;
	}
}

?>
