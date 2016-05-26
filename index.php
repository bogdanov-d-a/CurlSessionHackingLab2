<?php

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
	curl_exec_and_close($curl);
}

function log_in($initial_cookies, $session, $username, $password, $response_cookies)
{
	$curl = curl_init('http://pikabu.ru/ajax/auth.php');

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

	curl_exec_and_close($curl);
}

?>
