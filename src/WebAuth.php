<?php

namespace rdx\librarything;

use GuzzleHttp\Cookie\FileCookieJar;

class WebAuth {

	public $cookies; // GuzzleHttp\Cookie\FileCookieJar
	public $user = '';
	public $pass = '';

	/**
	 * Dependency constructor
	 */
	public function __construct( $cookiesFile, $user, $pass ) {
		$this->cookies = $this->getCookieJar($cookiesFile);
		$this->user = $user;
		$this->pass = $pass;
	}

	/**
	 *
	 */
	protected function getCookieJar( $file ) {
		@touch($file);
		@chmod($file, 0600);
		return new FileCookieJar($file, true);
	}

}
