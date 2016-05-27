<?php

include('simple_html_dom.php');

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
	return curl_exec_and_close($curl);
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

	return curl_exec_and_close($curl);
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
}

$cookies_file = 'cookies.txt';

get_initial_cookies($cookies_file);
sleep(1);

log_in($cookies_file, get_session_from_cookies($cookies_file), $_GET['username'], $_GET['password'], $cookies_file);
sleep(1);

{
	$curl = curl_init('http://pikabu.ru');
	curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/' . $cookies_file);
	curl_exec_and_close($curl);
}

?>
