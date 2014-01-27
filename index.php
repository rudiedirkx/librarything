<?php

require 'inc.bootstrap.php';

if ( isset($_POST['status'], $_POST['user_token'], $_POST['user_token_expires']) ) {
	setCookie('lt_token', $_POST['user_token'], $_POST['user_token_expires']);
	do_redirect('index');
	exit;
}

$accessToken = @$_COOKIE['lt_token'];
if ( !$accessToken ) {
	do_redirect(LT_BASE_URL . '/services/thingAuth/authorize/' . APP_AUTHER);
	exit('Go external');
}



header('Content-type: text/plain; charset=utf8');



// var_dump($accessToken);

$request = get_request('read', 'profile', array());
// var_dump($request);

$response = $request->request();
echo trim($response->raw) . "\n\n";

print_r($request);

// $appInfo = json_decode(file_get_contents('../inc/_librarything.cache/librarything-app.json'));
// var_dump($appInfo);

// $accessToken = trim(file_get_contents('../inc/_librarything.cache/librarything-token.cache'));
// var_dump($accessToken);
