<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RedirectMiddleware;

require 'env.php';
require 'inc.functions.php';

require 'vendor/autoload.php';

header('Content-type: text/html; charset=utf-8');

define('LT_BASE_URL', 'https://www.librarything.com');

umask(0);
$cookies = new FileCookieJar(APP_COOKIE_FILE, true);

$stack = HandlerStack::create();
$stack->push(Middleware::tap(
	function($request, $options) use (&$guzzle) {
		$guzzle->log[] = ['request' => (string) $request->getUri()];
	},
	function($request, $options, $response) use (&$guzzle) {
		$response->then(function($response) use (&$guzzle) {
			$guzzle->log[ count($guzzle->log) - 1 ]['response'] = $response->getStatusCode();
		});
	}
));

$guzzle = new Client([
	'base_uri' => LT_BASE_URL,
	'handler' => $stack,
	'cookies' => $cookies,
	'allow_redirects' => array(
		'track_redirects' => true,
	) + RedirectMiddleware::$defaultSettings,
]);

$guzzle->log = [];
