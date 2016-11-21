<?php

namespace rdx\librarything;

use rdx\librarything\BookRow;

class Book {

	/**
	 * Extract DOM node
	 */
	public function __construct(BookRow $node) {
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
			$collections = array_diff_key($collections, $skipCollections);
		}

		return $collections;
	}

}
