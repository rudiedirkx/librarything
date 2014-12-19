<?php

require 'inc.bootstrap.php';

if ( isset($_GET['cookie_userchecksum'], $_GET['cookie_userid'], $_GET['cookie_usernum']) ) {
	$expire = time() + 999999;
	setcookie('cookie_userchecksum', $_COOKIE['cookie_userchecksum'] = $_GET['cookie_userchecksum'], $expire);
	setcookie('cookie_userid', $_COOKIE['cookie_userid'] = $_GET['cookie_userid'], $expire);
	setcookie('cookie_usernum', $_COOKIE['cookie_usernum'] = $_GET['cookie_usernum'], $expire);
}

if ( !isset($_COOKIE['cookie_userchecksum'], $_COOKIE['cookie_userid'], $_COOKIE['cookie_usernum']) ) {
	?>
	<p>Need cookies `cookie_userchecksum`, `cookie_userid` and `cookie_usernum` for LT session.</p>
	<p>Log in and take them.</p>

	<form action>
		<p>cookie_userchecksum: <input name="cookie_userchecksum" /></p>
		<p>cookie_userid: <input name="cookie_userid" /></p>
		<p>cookie_usernum: <input name="cookie_usernum" /></p>
		<p><button>Do it</button></p>
	</form>
	<?php
	exit;
}

header('Content-type: text/plain; charset=utf8');

$http = new HTTP('https://www.librarything.com/catalog_bottom.php?view=rudiedirkx&offset=0');
$http->cookies = array(
	'cookie_userchecksum' => $_COOKIE['cookie_userchecksum'],
	'cookie_userid' => $_COOKIE['cookie_userid'],
	'cookie_usernum' => $_COOKIE['cookie_usernum'],
);

$response = $http->request();
// print_r($response);
echo $response;

exit;

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
