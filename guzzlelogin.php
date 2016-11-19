<?php

use rdx\jsdom\Node;

require 'inc.bootstrap.php';

header('Content-type: text/plain; charset=utf8');

// GET /
$res = $guzzle->request('GET', '/home', []);

if ( strpos($res->getBody(), 'formusername') ) {
	// POST /enter/start
	// GET /enter/checkcookies/2403928250
	// GET /enter/process/signinform
	// GET /home
	// GET /
	$res = $guzzle->request('POST', '/enter/start', [
		'form_params' => array(
			'formusername' => LT_USER_NAME,
			'formpassword' => LT_USER_PASS,
			'index_signin_already' => 'Sign in',
		),
	]);
}

// GET /catalog/rudiedirkx
$res = $guzzle->request('GET', '/catalog_bottom.php', []);
$htmls = [$res->getBody()];
// echo $html[0];

$getNextPageUri = function($html) {
	$dom = Node::create($html);
	$els = $dom->queryAll('.pageShuttleButton');
	foreach ($els as $el) {
		if ($el->innerText == 'next page') {
			return $el;
		}
	}
};

while ($next = $getNextPageUri(end($htmls))) {
	$res = $guzzle->request('GET', $next['href'], []);
	$htmls[] = $res->getBody();
}

print_r($guzzle->log);

class BookRow extends Node {
	public function getTitle() {
		return $this->query('a.lt-title')->innerText;
	}

	public function getAuthor() {
		return $this->query('a.lt-author')->innerText;
	}

	public function getRating() {
		return (int) $this->query('input[name="form_rating"]')['value'];
	}

	public function getEntryDate() {
		foreach ($this->children() as $child) {
			if (preg_match('#^\d\d\d\d\-\d\d?\-\d\d?$#', $date = $child->innerText)) {
				return DateTime::createFromFormat('Y-m-d', $date);
			}
		}
	}
}

$books = [];
foreach ($htmls as $html) {
	$dom = Node::create($html);
	$rows = $dom->queryAll('tr.cat_catrow', BookRow::class);
	$books = array_merge($books, $rows);
}

var_dump(count($books));

foreach ($books as $book) {
	echo "\n\n";
	$title = $book->getTitle();
	var_dump($title);
	$author = $book->getAuthor();
	var_dump($author);
	$rating = $book->getRating();
	var_dump($rating);
	$entered = $book->getEntryDate();
	var_dump($entered->format('d-M-Y'));
}
