<?php

require 'inc.bootstrap.php';

// $client->ensureLogin();
$books = $client->getCatalogue();
$collections = $client->getCollections($books, $skipCollections);

include 'tpl.header.php';

?>

<style>
div.table {
	width: 100%;
	overflow: auto;
}
tr.filter-hide {
	display: none;
}
td.rating-1-5 {
	background: red;
}
td.rating-2-5 {
	background: orange;
}
td.rating-3-5 {
	background: yellow;
}
td.rating-4-5 {
	background: lime;
}
td.rating-5-5 {
	background: green;
	color: white;
}
</style>

<h1><span id="filter-showing"><?= count($books) ?></span> / <?= count($books) ?> books</h1>

<p><select id="filter-collection"><?= html_options($collections, null, '-- All') ?></select></p>

<div class="table">
<table border="1" cellspacing="0" cellpadding="6">
	<thead id="sorters">
		<tr>
			<th data-sort="author">Author</th>
			<th>Title</th>
			<th data-sort="entry_date">Entry date</th>
			<th data-sort="rating">Rating</th>
			<th>Collections</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($books as $book): ?>
			<tr data-collections="<?= html(json_encode($book->getCollections())) ?>">
				<td data-sort="author"><?= html($book->author) ?></td>
				<td><?= html($book->title) ?></td>
				<td data-sort="entry_date" nowrap><?= html($book->entry_date) ?></td>
				<td data-sort="rating" data-value="<?= (5 - $book->rating) ?>" class="rating-<?= $book->rating ?>-5"><?= $book->rating ? $book->rating . ' / 5' : '' ?></td>
				<td><?= implode(', ', $book->getCollections($skipCollections)) ?></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>
</div>

<script>
var rows = [].slice.call(document.querySelectorAll('tr[data-collections]'));
var filterShowingElement = document.querySelector('#filter-showing');
var filterCollectionElement = document.querySelector('#filter-collection');
var sortersElement = document.querySelector('#sorters');

for (var i = 0; i < rows.length; i++) {
	rows[i]._collections = JSON.parse(rows[i].dataset.collections);
}

function filter() {
	var value = filterCollectionElement.value;
	var count = 0;
	for (var i = 0; i < rows.length; i++) {
		var hide = value && rows[i]._collections.indexOf(value) == -1;
		rows[i].classList[hide ? 'add' : 'remove']('filter-hide');
		count += hide ? 0 : 1;
	}

	filterShowingElement.textContent = count;
}

filterCollectionElement.value && filter();

filterCollectionElement.onchange = function(e) {
	filter();
};

sortersElement.onclick = function(e) {
	var sorter = e.target.dataset.sort;
	if (sorter) {
		console.time('Sorting rows');
		rows.sort(function(a, b) {
			a = a.querySelector('td[data-sort="' + sorter + '"]');
			a = a.dataset.value || a.textContent.trim();
			b = b.querySelector('td[data-sort="' + sorter + '"]');
			b = b.dataset.value || b.textContent.trim();
			return a == b ? 0 : a > b ? 1 : -1;
		});
		console.timeEnd('Sorting rows');

		console.time('Positioning rows');
		var container = rows[0].parentNode;
		for (var i = 0; i < rows.length; i++) {
			container.appendChild(rows[i]);
		}
		console.timeEnd('Positioning rows');
	}
};
</script>

<?php

include 'tpl.footer.php';
