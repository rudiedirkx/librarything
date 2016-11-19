<?php

namespace rdx\librarything\catalogue;

use DateTime;
use rdx\jsdom\Node;

class BookRow extends Node {

	protected $cache = [];

	protected function _cache($name, $callback) {
		if (!isset($this->cache[$name])) {
			$this->cache[$name] = $callback();
		}
		return $this->cache[$name];
	}

	protected function _plain($node) {
		return $node ? $node->innerText : '';
	}

	public function getTitle() {
		return $this->_cache(__FUNCTION__, function() {
			return $this->_plain($this->query('a.lt-title'));
		});
	}

	public function getAuthor() {
		return $this->_cache(__FUNCTION__, function() {
			return $this->_plain($this->query('a.lt-author'));
		});
	}

	public function getRating() {
		return $this->_cache(__FUNCTION__, function() {
			return ((int) $this->query('input[name="form_rating"]')['value']) / 2;
		});
	}

	public function getEntryDate() {
		return $this->_cache(__FUNCTION__, function() {
			foreach ($this->children() as $child) {
				if (preg_match('#^\d\d\d\d\-\d\d?\-\d\d?$#', $date = $child->innerText)) {
					return DateTime::createFromFormat('Y-m-d', $date);
				}
			}
		});
	}

	public function getCollections(array $skipCollections = []) {
		$collections = $this->_cache(__FUNCTION__, function() {
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
		});

		if ($skipCollections) {
			$collections = array_filter($collections, function($name) use ($skipCollections) {
				return !in_array($name, $skipCollections);
			});
		}

		return $collections;
	}

}
