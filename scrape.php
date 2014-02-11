<?php

require 'inc.bootstrap.php';

if ( !isset($_COOKIE['lt_phpsessid']) ) {
	exit('Need cookie `lt_phpsessid` for LT session. Log in and get it from PHPSESSID.');
}

// header('Content-type: text/plain; charset=utf8');

$http = new HTTP('http://www.librarything.com/catalog_bottom.php?view=rudiedirkx&offset=0');
$http->cookies = 'PHPSESSID=' . $_COOKIE['lt_phpsessid'];

$response = $http->request();
// print_r($response);

if ( $response->code != 200 ) {
	exit('Invalid response [' . $response->code . '] ' . $response->status . ":\n\n" . trim($response->body) . "\n");
}

$body = $response->body;

preg_match_all('#<tr.+?"catrow_[^>]*>([\s\S]+?)</tr>#', $body, $matches);
// print_r($matches[1]);

echo '<table border="1">';
foreach ( $matches[1] as $tr ) {
	// $xml = simplexml_load_string('<tr>' . $tr . '</tr>');
// print_r($xml);
// echo $tr;

	echo '<tr>' . $tr . '</tr>';

	break;
}
echo '</table>';



// echo $body;
