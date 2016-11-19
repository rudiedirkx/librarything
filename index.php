<?php

require 'inc.bootstrap.php';

$client->ensureLogin();
$books = $client->getCatalogue();

include 'tpl.header.php';

?>

<h1><?= count($books) ?> books</h1>

<div class="table">
<table border="1" cellspacing="0" cellpadding="6">
	<thead>
		<tr>
			<th>Author</th>
			<th>Title</th>
			<th>Entry date</th>
			<th>Rating</th>
			<th>Collections</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($books as $book):
			$entered = $book->getEntryDate();
			$rating = $book->getRating();
			?>
			<tr>
				<td><?= html($book->getAuthor()) ?></td>
				<td><?= html($book->getTitle()) ?></td>
				<td><?= $entered ? html($entered->format('d-M-Y')) : '' ?></td>
				<td><?= $rating ? $book->getRating() . ' / 5' : '' ?></td>
				<td><?= implode(', ', $book->getCollections()) ?></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>
</div>

<?php

include 'tpl.footer.php';
