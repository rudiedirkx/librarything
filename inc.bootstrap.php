<?php

use rdx\librarything\Client;
use rdx\librarything\FileCache;
use rdx\librarything\WebAuth;

require 'env.php';
require 'inc.functions.php';

require 'vendor/autoload.php';

header('Content-type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
	exit("CSRF error");
}

$client = new Client(
	new WebAuth(APP_COOKIE_FILE, LT_USER_NAME, LT_USER_PASS),
	new FileCache(APP_CACHE_DIR, APP_CACHE_TTL)
);
