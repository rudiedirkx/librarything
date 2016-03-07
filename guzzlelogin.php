<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\RedirectMiddleware;

require 'inc.bootstrap.php';

header('Content-type: text/plain; charset=utf8');

$cookies = new FileCookieJar('/tmp/librarything-guzzle-2.cookies', true);

$client = new Client([
	'cookies' => $cookies,
	'allow_redirects' => array(
		'track_redirects' => true,
	) + RedirectMiddleware::$defaultSettings,
]);

// GET /
$res = $client->request('GET', LT_BASE_URL . '/', []);

if ( strpos($res->getBody(), 'formusername') ) {
	// POST /enter/start
	// GET /enter/checkcookies/2403928250
	// GET /enter/process/signinform
	// GET /home
	// GET /
	$res = $client->request('POST', LT_BASE_URL . '/enter/start', [
		'form_params' => array(
			'formusername' => LT_USER_NAME,
			'formpassword' => LT_USER_PASS,
			'index_signin_already' => 'Sign in',
		),
	]);
}

// GET /catalog/rudiedirkx
$res = $client->request('GET', LT_BASE_URL . '/', []);
echo $res->getBody();
