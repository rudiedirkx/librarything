<?php

namespace rdx\librarything;

class Collection {

	public $id;
	public $name;
	public $relevant = true;
	public $used = 0;

	public function __construct( $id, $name ) {
		$this->id = (int) $id;
		$this->name = $name;
	}

	public function __toString() {
		return $this->name;
	}

}
