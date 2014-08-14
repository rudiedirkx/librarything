<?php

require 'inc.bootstrap.php';

header('Content-type: text/plain; charset=utf8');

if ( isset($_POST['status']) ) {
	$status = $_POST['status'];

	if ( $status == 200 && isset($_POST['user_token'], $_POST['user_token_expires']) ) {
		setCookie('lt_token', $_POST['user_token'], $_POST['user_token_expires']);
		do_redirect('index');
		exit;
	}

	print_r($_POST);

	exit;
}



$accessToken = @$_COOKIE['lt_token'];
if ( !$accessToken ) {
	do_redirect(LT_BASE_URL . '/services/thingAuth/authorize/' . APP_AUTHER);
	exit('Go external');
}



// var_dump($accessToken);
// exit;

$request = get_request('read', 'profile', array());
print_r($request);
// exit;

$response = $request->request();
print_r($response);
exit;

echo trim($response->raw) . "\n\n";
print_r($response);

// $appInfo = json_decode(file_get_contents('../inc/_librarything.cache/librarything-app.json'));
// var_dump($appInfo);

// $accessToken = trim(file_get_contents('../inc/_librarything.cache/librarything-token.cache'));
// var_dump($accessToken);
