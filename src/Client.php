<?php

namespace rdx\librarything;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RedirectMiddleware;
use rdx\jsdom\Node;
use rdx\librarything\WebAuth;
use rdx\librarything\catalogue\BookRow;

class Client {

	public $base = 'https://www.librarything.com';
	public $auth; // rdx\librarything\WebAuth
	public $guzzle; // GuzzleHttp\Client

	/**
	 * Dependency constructor
	 */
	public function __construct( WebAuth $auth ) {
		$this->auth = $auth;

		$this->setUpGuzzle();
	}

	/**
	 *
	 */
	public function getCatalogue() {
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
			$books = array_merge($books, $rows);
		}

		usort($books, function($a, $b) {
			return $b->getEntryDate()->getTimestamp() - $a->getEntryDate()->getTimestamp();
		});

		return $books;
	}

	/**
	 *
	 */
	public function ensureLogin() {
		// GET /home
		$res = $this->guzzle->request('GET', '/home', []);

		if ( strpos($res->getBody(), 'formusername') ) {
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
		$cookies = $this->setUpCookieJar();
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

	/**
	 *
	 */
	protected function setUpCookieJar() {
		umask(0);
		return new FileCookieJar(APP_COOKIE_FILE, true);
	}

}
