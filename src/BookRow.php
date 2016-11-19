<?php

namespace rdx\librarything;

use rdx\jsdom\Node;

class BookRow extends Node {

	protected $cache = [];

	protected function _plain($node) {
		return $node ? $node->innerText : '';
	}

	public function getTitle() {
		return $this->_plain($this->query('a.lt-title'));
	}

	public function getAuthor() {
		return $this->_plain($this->query('a.lt-author'));
	}

	public function getRating() {
		return ((int) $this->query('input[name="form_rating"]')['value']) / 2;
	}

	public function getEntryDate() {
		foreach ($this->children() as $child) {
			if (preg_match('#^\d\d\d\d\-\d\d?\-\d\d?$#', $date = $child->innerText)) {
				return $date;
			}
		}
	}

	public function getCollections() {
		foreach ($this->children() as $child) {
			if (strpos($child->innerText, 'Edit collections')) {
				$nodes = $child->queryAll('.mbmi.mbmiSelected');

				$collections = [];
				foreach ($nodes as $node) {
					$collections[] = $node->innerText;
				}

				return $collections;
			}
		}
	}

}
