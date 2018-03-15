<?php

namespace rdx\librarything;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RedirectMiddleware;
use rdx\jsdom\Node;

class Client {

	public $base = 'https://www.librarything.com';

	public $auth; // rdx\librarything\WebAuth
	public $cache; // rdx\librarything\FileCache

	public $guzzle; // GuzzleHttp\Client

	/**
	 * Dependency constructor
	 */
	public function __construct( WebAuth $auth, FileCache $cache ) {
		$this->auth = $auth;
		$this->cache = $cache;

		$this->setUpGuzzle();
	}

	/**
	 *
	 */
	public function toggleBookCollection( Book $book, $collectionId, $add ) {
		$res = $this->guzzle->request('POST', '/ajax_collectionsToggleBook.php', [
			'form_params' => [
				'bookid' => $book->id,
				'c_id' => $collectionId,
				'addRemove' => (int) $add,
				'returnUI' => '1',
				'containerID' => 'collections79005727',
				'excludeContainer' => '1',
			],
		]);
		return $res->getStatusCode() == 200;
	}

	/**
	 *
	 */
	public function rateBook( Book $book, $rating ) {
		$book->rating = $rating;

		$res = $this->guzzle->request('POST', '/ajax_setBookRating.php', [
			'form_params' => [
				'uid' => 'ErI40u79',
				'book' => $book->id,
				'editable' => '1',
				'container' => 'rate-ult_128243263',
				'style' => '0',
				'rating' => (string) ($rating * 2),
			],
		]);
		return $res->getStatusCode() == 200;
	}

	/**
	 *
	 */
	public function markCollections( array $collections, array $books ) {
		$used = [];
		foreach ( $books as $book ) {
			foreach ( $book->collections as $id ) {
				@$used[$id]++;
			}
		}

		foreach ( $collections as $id => $collection ) {
			$collection->used = (int) @$used[$id];
			if ( $collection->used === count($books) ) {
				$collection->relevant = false;
			}
		}
	}

	/**
	 *
	 */
	public function getCollections() {
		return $this->cache->retrieve('collections', function() {
			// Get the first page
			$res = $this->guzzle->request('GET', '/catalog_bottom.php', []);

			return $this->extractCollections($res->getBody());
		});
	}

	/**
	 *
	 */
	protected function extractCollections( $html ) {
		preg_match_all("#LibraryThing\.collections\.pickCollection\('*(\d+)'*, 1\).+?>(.+?)<#", $html, $matches, PREG_SET_ORDER);
		$collectionNames = array_reduce($matches, function(array $list, array $match) {
			return $list + [$match[1] => html_entity_decode($match[2])];
		}, []);

		$collections = [];
		foreach ( $collectionNames as $id => $name ) {
			$collections[$id] = new Collection($id, $name);
		}

		return $collections;
	}

	/**
	 *
	 */
	public function setCatalogue( array $books ) {
		return $this->cache->store('catalogue', $books, false);
	}

	/**
	 *
	 */
	public function getCatalogue() {
		return $this->cache->retrieve('catalogue', function() {
			// Reset collection filter
			$this->auth->cookies->setCookie(new SetCookie([
				'Name' => 'collectionPick_change',
				'Value' => '-1',
				'Domain' => 'www.librarything.com',
				'Expires' => time() + 999999,
			]));

			// Get the first page
			$res = $this->guzzle->request('GET', '/catalog_bottom.php', []);
			$htmls = [$res->getBody()];

			$collections = $this->extractCollections($htmls[0]);
			$this->cache->store('collections', $collections);

			$getNextPageUri = function( $html ) {
				$dom = Node::create($html);
				$els = $dom->queryAll('.pageShuttleButton');
				foreach ( $els as $el ) {
					if ( $el->innerText == 'next page' ) {
						return $el;
					}
				}
			};

			// Get all next pages
			while ( $next = $getNextPageUri(end($htmls)) ) {
				$res = $this->guzzle->request('GET', $next['href'], []);
				$htmls[] = $res->getBody();
			}

			// Collect Node objects, one for every row
			$books = [];
			foreach ( $htmls as $html ) {
				$dom = Node::create($html);
				$rows = $dom->queryAll('tr.cat_catrow', BookRow::class);
				foreach ( $rows as $row ) {
					$books[$row->getID()] = new Book($row);
				}
			}

			uasort($books, function( $a, $b ) {
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

		$loggedIn = strpos($res->getBody(), 'Sign out') !== false;

		if ( !$loggedIn ) {
			// GET /
			// $res = $this->guzzle->request('GET', '/', []);

			// POST /enter/start
			// GET /enter/checkcookies/2403928250
			// GET /enter/process/signinform
			// GET /home
			// GET /
			$res = $this->guzzle->request('POST', '/enter/start', [
				'form_params' => [
					'formusername' => $this->auth->user,
					'formpassword' => $this->auth->pass,
					'index_signin_already' => 'Sign in',
				],
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
			'allow_redirects' => [
				'track_redirects' => true,
			] + RedirectMiddleware::$defaultSettings,
		]);

		$this->setUpLog($stack);
	}

	/**
	 *
	 */
	protected function setUpLog( HandlerStack $stack ) {
		$stack->push(Middleware::tap(
			function( $request, $options ) {
				$this->guzzle->log[] = [
					'time' => microtime(1),
					'request' => (string) $request->getUri(),
				];
			},
			function( $request, $options, $response ) {
				$response->then(function( $response ) {
					$log = &$this->guzzle->log[count($this->guzzle->log) - 1];
					$log['time'] = microtime(1) - $log['time'];
					$log['response'] = $response->getStatusCode();
				});
			}
		));

		$this->guzzle->log = [];
	}

}
