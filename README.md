Library Thing API
====

	use rdx\librarything\Client;
	use rdx\librarything\WebAuth;

	// Create an LT client.
	$client = new Client(new WebAuth(LT_USER_NAME, LT_USER_PASS));

	// Get all books from the catalogue. Returns array of rdx\librarything\Book.
	$books = $client->getCatalogue();

	// Get all collections in this catalogue. Optionally skip and remember the ones
	// that are enabled in **every** book.
	$collections = $client->getCollections($books, $skipCollections);

	foreach ($books as $book) {
		var_dump($book->title);
		var_dump($book->author);
		var_dump($book->entry_date);
		var_dump($book->rating);
		var_dump($book->collections);
		var_dump($book->getCollections($skipCollections));
	}
