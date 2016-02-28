<?php

use GuzzleHttp\Client;
use GuzzleHttp\RedirectMiddleware;

require 'inc.bootstrap.php';

header('Content-type: text/plain; charset=utf8');

$client = new Client([
	'cookies' => true,
	'allow_redirects' => array(
		'track_redirects' => true,
	) + RedirectMiddleware::$defaultSettings,
]);

// GET /
$res = $client->request('GET', LT_BASE_URL . '/', []);
var_dump($res->getStatusCode());
// print_r($res->getHeaders());

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
var_dump($res->getStatusCode());
// print_r($res->getHeaders());
// print_r($res);
// echo $res->getBody();

// GET /catalog/rudiedirkx
$res = $client->request('GET', LT_BASE_URL . '/', []);
var_dump($res->getStatusCode());
echo $res->getBody();
