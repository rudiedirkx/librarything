<?php

namespace rdx\librarything;

use rdx\jsdom\Node;

class Book {

	/**
	 * Extract DOM node
	 */
	public function __construct(Node $node) {
		$this->id = $node->getID();
		$this->title = $node->getTitle();
		$this->author = $node->getAuthor();
		$this->rating = $node->getRating();
		$this->entry_date = $node->getEntryDate();
		$this->collections = $node->getCollections();
	}

	/**
	 *
	 */
	public function getCollections(array $skipCollections = []) {
		$collections = $this->collections;

		if ($skipCollections) {
			$collections = array_filter($collections, function($name) use ($skipCollections) {
				return !in_array($name, $skipCollections);
			});
		}

		return $collections;
	}

}
