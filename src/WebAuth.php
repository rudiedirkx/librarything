<?php

namespace rdx\librarything;

class WebAuth {

	public $user = '';
	public $pass = '';

	/**
	 * Dependency constructor
	 */
	public function __construct( $user, $pass ) {
		$this->user = $user;
		$this->pass = $pass;
	}

}
