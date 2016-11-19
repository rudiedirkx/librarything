<?php

namespace rdx\librarything;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RedirectMiddleware;
use rdx\jsdom\Node;
use rdx\librarything\Book;
use rdx\librarything\FileCache;
use rdx\librarything\WebAuth;
use rdx\librarything\BookRow;

class Client {

	public $base = 'https://www.librarything.com';

	public $auth; // rdx\librarything\WebAuth
	public $cache; // rdx\librarything\FileCache

	public $guzzle; // GuzzleHttp\Client

	/**
	 * Dependency constructor
	 */
	public function __construct(WebAuth $auth, FileCache $cache) {
		$this->auth = $auth;
		$this->cache = $cache;

		$this->setUpGuzzle();
	}

	/**
	 *
	 */
	public function getCollections(array $books, &$skipCollections = []) {
		// Gather all collections from all books
		$collections = [];
		foreach ($books as $book) {
			foreach ($book->getCollections() as $collection) {
				@$collections[$collection]++;
			}
		}

		// Skip and remember the ones that exist everywhere
		foreach ($collections as $name => $usage) {
			if ($usage == count($books)) {
				$skipCollections[] = $name;
				unset($collections[$name]);
			}
		}

		$collections = array_keys($collections);
		$collections = array_combine($collections, $collections);

		return $collections;
	}

	/**
	 *
	 */
	public function getCatalogue() {
		return $this->cache->retrieve('catalogue', function() {
			// Get the first page
			$res = $this->guzzle->request('GET', '/catalog_bottom.php', []);
			$htmls = [$res->getBody()];

			$getNextPageUri = function($html) {
				$dom = Node::create($html);
				$els = $dom->queryAll('.pageShuttleButton');
				foreach ($els as $el) {
					if ($el->innerText == 'next page') {
						return $el;
					}
				}
			};

			// Get all next pages
			while ($next = $getNextPageUri(end($htmls))) {
				$res = $this->guzzle->request('GET', $next['href'], []);
				$htmls[] = $res->getBody();
			}

			// Collect Node objects, one for every row
			$books = [];
			foreach ($htmls as $html) {
				$dom = Node::create($html);
				$rows = $dom->queryAll('tr.cat_catrow', BookRow::class);
				foreach ($rows as $row) {
					$books[] = new Book($row);
				}
			}

			usort($books, function($a, $b) {
				return strcmp($b->entry_date, $a->entry_date);
			});

			return $books;
		});
	}

	/**
	 *
	 */
	public function ensureLogin() {
		// GET /home
		$res = $this->guzzle->request('GET', '/home', []);

		if (strpos($res->getBody(), 'formusername')) {
			// POST /enter/start
			// GET /enter/checkcookies/2403928250
			// GET /enter/process/signinform
			// GET /home
			// GET /
			$res = $this->guzzle->request('POST', '/enter/start', [
				'form_params' => array(
					'formusername' => $this->auth->user,
					'formpassword' => $this->auth->pass,
					'index_signin_already' => 'Sign in',
				),
			]);
		}

	}

	/**
	 *
	 */
	protected function setUpGuzzle() {
		$cookies = $this->auth->cookies;
		$stack = HandlerStack::create();
		$this->guzzle = new Guzzle([
			'base_uri' => $this->base,
			'handler' => $stack,
			'cookies' => $cookies,
			'allow_redirects' => array(
				'track_redirects' => true,
			) + RedirectMiddleware::$defaultSettings,
		]);

		$this->setUpLog($stack);
	}

	/**
	 *
	 */
	protected function setUpLog(HandlerStack $stack) {
		$stack->push(Middleware::tap(
			function($request, $options) {
				$this->guzzle->log[] = ['request' => (string) $request->getUri()];
			},
			function($request, $options, $response) {
				$response->then(function($response) {
					$this->guzzle->log[ count($this->guzzle->log) - 1 ]['response'] = $response->getStatusCode();
				});
			}
		));

		$this->guzzle->log = [];
	}

}
