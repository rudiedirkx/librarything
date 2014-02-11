<?php

require 'inc.bootstrap.php';

if ( !isset($_COOKIE['lt_phpsessid']) ) {
	exit('Need cookie `lt_phpsessid` for LT session. Log in and get it from PHPSESSID.');
}

header('Content-type: text/plain; charset=utf8');

$http = new HTTP('http://www.librarything.com/export-tab');
$http->cookies = 'PHPSESSID=' . $_COOKIE['lt_phpsessid'];

$response = $http->request();
print_r($response);

echo $response->body;
