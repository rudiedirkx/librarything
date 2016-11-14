<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\RedirectMiddleware;
use rdx\jsdom\Node;

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
$res = $client->request('GET', LT_BASE_URL . '/catalog_bottom.php', []);
$html = $res->getBody();
// echo $html;

$node = Node::create($html);
$rows = $node->queryAll('tr.cat_catrow');
var_dump(count($rows));
foreach ($rows as $row) {
	echo "\n\n";
	$title = $row->query('a.lt-title')->innerText;
	var_dump($title);
	$author = $row->query('a.lt-author')->innerText;
	var_dump($author);
	$rating = (int) $row->query('input[name="form_rating"]')['value'];
	var_dump($rating);
}
