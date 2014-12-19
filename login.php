<?php

require 'inc.bootstrap.php';

header('Content-type: text/plain; charset=utf8');

// enter/start
$http = HTTP::create(LT_BASE_URL . '/enter/start', array(
	'method' => 'POST',
	'data' => array(
		'formusername' => 'foo',
		'formpassword' => 'bar',
		'index_signin_already' => 'Sign in',
	),
	// 'redirects' => 4,
));

$response1 = $http->request();
// print_r($response1);

echo $response1;

$cookies1 = $response1->cookies_by_name;
// print_r($cookies1);

$headers1 = $response1->headers;
// print_r($headers1);

if ( $response1->code == 302 && @$headers1['location'] && @$cookies1['cookie_test_session'] ) {
	// enter/checkcookies/2843267644
	$http = HTTP::create(LT_BASE_URL . $headers1['location'][0], array(
		'method' => 'GET',
		'cookies' => $response1->cookies,
	));

	echo "\n\n";

	$response2 = $http->request();
	// print_r($response2);

	echo $response2;

	$cookies2 = $response2->cookies_by_name;
	// print_r($cookies2);

	$headers2 = $response2->headers;
	// print_r($headers2);

	if ( $response2->code == 302 && @$headers2['location'] && @$cookies2['cookie_test_session'] ) {
		// enter/process/signinform
		$http = HTTP::create(LT_BASE_URL . $headers2['location'][0], array(
			'method' => 'GET',
			'cookies' => array_merge($response1->cookies, $response2->cookies),
		));

		echo "\n\n";

		$response3 = $http->request();
		// print_r($response3);

		echo $response3;

		// $http = HTTP::create(LT_BASE_URL . '/addbooks', array(
		// 	'method' => 'GET',
		// 	'cookies' => array_merge($response1->cookies, $response2->cookies),
		// ));

		// echo "\n\n";

		// $response4 = $http->request();
		// // print_r($response4);

		// echo $response4;
	}
}
