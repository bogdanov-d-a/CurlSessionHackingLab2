<?php

include('simple_html_dom.php');

class PostTools
{
	function curl_exec_and_close($curl)
	{
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	function get_initial_cookies($cookies)
	{
		$curl = curl_init('http://pikabu.ru');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/' . $cookies);
		return PostTools::curl_exec_and_close($curl);
	}

	function get_session_from_cookies($cookies)
	{
		// http://stackoverflow.com/questions/410109/php-reading-a-cookie-file

		$lines = file(dirname(__FILE__) . '/' . $cookies);
		foreach ($lines as $line)
		{
			if ($line[0] != '#' && substr_count($line, "\t") == 6)
			{
				$tokens = array_map('trim', explode("\t", $line));
				if ($tokens[5] == 'PHPSESS')
					return $tokens[6];
			}
		}
		die('Session not found!');
	}

	function log_in($initial_cookies, $session, $username, $password, $response_cookies)
	{
		$curl = curl_init('http://pikabu.ru/ajax/auth.php');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		{
			$header[0] = 'X-Csrf-Token: ' . $session;
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/' . $initial_cookies);
		curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/' . $response_cookies);

		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array(
			'g-recaptcha-response'=>'',
			'mode'=>'login',
			'password'=>$password,
			'username'=>$username
		));

		return PostTools::curl_exec_and_close($curl);
	}

	function get_first_story_id()
	{
		$html = file_get_html('http://pikabu.ru');
		$skipped_ad = false;

		foreach ($html->find('div') as $link)
		{
			if ($link->getAttribute('class') == 'story')
			{
				if (!$skipped_ad)
					$skipped_ad = true;
				else
					return $link->getAttribute('data-story-id');
			}
		}

		die('Story not found!');
	}

	function post_comment($cookies, $session, $story_id, $comment)
	{
		$curl = curl_init('http://pikabu.ru/ajax/comments_actions.php');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		{
			$header[0] = 'X-Csrf-Token: ' . $session;
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/' . $cookies);

		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array(
			'action'=>'create',
			'story_id'=>$story_id,
			'desc'=>$comment,
			'images'=>'[]',
			'parent_id'=>'0'
		));

		return PostTools::curl_exec_and_close($curl);
	}
}

?>
