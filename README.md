Library Thing API
====

	use rdx\librarything\Client;
	use rdx\librarything\WebAuth;

	// Create an LT client.
	$client = new Client(new WebAuth(LT_USER_NAME, LT_USER_PASS));

	// Get all books from the catalogue. Returns array of rdx\librarything\Book.
	$books = $client->getCatalogue();

	// Get all collections.
	$collections = $client->getCollections();

	foreach ($books as $book) {
		var_dump($book->title);
		var_dump($book->author);
		var_dump($book->entry_date);
		var_dump($book->rating);
		var_dump($book->collections);
		var_dump($book->getCollections());
		var_dump($book->hasCollection(13));
	}

	$client->rateBook($book, 3); // 3 / 5

	$client->toggleBookCollection($book, $collectionId, true); // true to add, false to remove
