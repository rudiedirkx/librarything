<?php

namespace rdx\librarything;

class Book {

	/**
	 * Extract DOM node
	 */
	public function __construct( BookRow $node ) {
		$this->id = $node->getID();
		$this->title = $node->getTitle();
		$this->author = $node->getAuthor();
		$this->year = $node->getYear();
		$this->rating = $node->getRating();
		$this->entry_date = $node->getEntryDate();
		$this->collections = array_map('intval', array_keys($node->getCollections()));
	}

	/**
	 *
	 */
	public function toggleCollection( $collectionId, $add ) {
		if ( $add ) {
			$this->collections[] = (int) $collectionId;
		}
		else {
			$i = array_search((int) $collectionId, $this->collections);
			if ( $i !== false ) {
				array_splice($this->collections, $i, 1);
			}
		}
	}

	/**
	 *
	 */
	public function hasCollection( $id ) {
		return in_array((int) $id, $this->collections);
	}

}
