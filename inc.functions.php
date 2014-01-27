<?php

function get_request( $method, $function, $data = array() ) {
	global $accessToken;
	$data += array(
		'method' => $method,
		'function' => $function,
		'key' => APP_KEY,
		'secret' => APP_SECRET,
		'token' => $accessToken,
	);
	$url = LT_BASE_URL . '/services/rest/1.1/';
	$http = HTTP::create($url, array(
		'method' => 'POST',
		'data' => $data,
	));
	return $http;
}

function html( $text ) {
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function get_url( $path, $query = array() ) {
	$query = $query ? '?' . http_build_query($query) : '';
	if ( !preg_match('#^https?://#', $path) ) {
		$path = $path ? $path . '.php' : basename($_SERVER['SCRIPT_NAME']);
	}
	return $path . $query;
}

function do_redirect( $path, $query = array() ) {
	$url = get_url($path, $query);
	header('Location: ' . $url);
}
