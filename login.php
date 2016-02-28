<?php

use rdx\http\HTTP;

require 'inc.bootstrap.php';

header('Content-type: text/plain; charset=utf8');

// GET index
$request = HTTP::create(LT_BASE_URL . '/', array(
	'method' => 'GET',
));

$response0 = $request->request();
// print_r($response0->cookies);
echo trim($response0->info['request_header']);
echo "\n\n";
echo $response0->head;
echo "\n\n\n\n";
// exit;

// enter/start
$http = HTTP::create(LT_BASE_URL . '/enter/start', array(
	'method' => 'POST',
	'data' => array(
		'formusername' => LT_USER_NAME,
		'formpassword' => LT_USER_PASS,
		'index_signin_already' => 'Sign in',
	),
	'cookies' => $response0->cookies,
));

$response1 = $http->request();
// print_r($response1);

echo trim($response1->info['request_header']);
echo "\n\n";
echo $response1->head;
echo "\n\n\n\n";
// exit;

$cookies1 = $response1->cookies_by_name;
// print_r($cookies1);

$headers1 = $response1->headers;
// print_r($headers1);

if ( $response1->code == 302 && @$headers1['location'] && @$cookies1['cookie_test_session'] ) {
	// enter/checkcookies/2843267644
	$http = HTTP::create(LT_BASE_URL . $headers1['location'][0], array(
		'method' => 'GET',
		'cookies' => array_merge($response1->cookies, $response0->cookies),
	));

	$response2 = $http->request();
	// print_r($response2);

	echo trim($response2->info['request_header']);
	echo "\n\n";
	echo $response2->head;
	echo "\n\n\n\n";
	exit;

	$cookies2 = $response2->cookies_by_name;
	// print_r($cookies2);

	$headers2 = $response2->headers;
	// print_r($headers2);

	if ( $response2->code == 302 && @$headers2['location'] ) {
		// enter/process/signinform
		$http = HTTP::create(LT_BASE_URL . $headers2['location'][0], array(
			'method' => 'GET',
			'cookies' => array_merge($response2->cookies, $response1->cookies, $response0->cookies),
		));

		$response3 = $http->request();
		// print_r($response3);

		echo $response3->head;
		echo "\n\n\n\n";
		// exit;

		$http = HTTP::create(LT_BASE_URL . '/addbooks', array(
			'method' => 'GET',
			'cookies' => array_merge($response3->cookies, $response2->cookies, $response1->cookies, $response0->cookies),
		));

		$response4 = $http->request();
		// print_r($response4);

		echo $response4->head;
		echo "\n\n\n\n";
		exit;
	}
}
