<?php

use rdx\librarything\Collection;

require 'inc.bootstrap.php';

$client->ensureLogin();

$books = $client->getCatalogue();
$collections = $client->getCollections();
$client->markCollections($collections, $books);

if (isset($_POST['book'], $_POST['rating'])) {
	if (isset($books[ $_POST['book'] ])) {
		$book = $books[ $_POST['book'] ];

		if ($client->rateBook($book, $_POST['rating'])) {
			$client->setCatalogue($books);
			echo $_POST['rating'];
		}
		else {
			echo '?';
		}
	}

	exit;
}

if (isset($_POST['book'], $_POST['collection'], $_POST['add'])) {
	if (isset($books[ $_POST['book'] ])) {
		$book = $books[ $_POST['book'] ];

		if ($client->toggleBookCollection($book, $_POST['collection'], (bool) $_POST['add'])) {
			$book->toggleCollection($_POST['collection'], $_POST['add']);

			$client->setCatalogue($books);
			echo '1';
		}
		else {
			echo '?';
		}
	}

	exit;
}

include 'tpl.header.php';

$collectionFilterOptions = array_map(function(Collection $collection) {
	return "$collection ({$collection->used})";
}, $collections);

?>

<style>
th[data-sort] {
	cursor: pointer;
	white-space: nowrap;
}
th[data-sort]:after {
	content: " ⇕";
}
th[data-sort]:not([data-sorting]):after {
	color: #ccc;
}

tr.filter-hide {
	display: none;
}
td.rating {
	color: black;
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
a.rate-book {
	display: block;
	color: inherit;
	text-decoration: none;
	outline: dotted 1px #888;
}
a.rate-book.working {
	background-color: white;
}

.collections input {
	visibility: hidden;
	position: absolute;
	z-index: -1;
}
.collections label {
	color: #bbb;
	display: inline-block;
}
.collections :checked + label {
	color: black;
	font-weight: bold;
}
.catalogue:not(.collecting) .collections input:not(:checked) + label:not(.working),
.catalogue:not(.collecting) .collections input:not(:checked) + label:not(.working) + br,
.catalogue:not(.collecting) .collections input.not-relevant + label:not(.working),
.catalogue:not(.collecting) .collections input.not-relevant + label:not(.working) + br {
	display: none;
}
</style>

<h1><span id="filter-showing"><?= count($books) ?></span> / <?= count($books) ?> books</h1>

<p>
	<select id="filter-collection"><?= html_options($collectionFilterOptions, null, '-- All') ?></select>
	<input id="filter-text" type="search" placeholder="Author & title..." />
</p>

<div class="table">
<table class="catalogue" border="1" cellspacing="0" cellpadding="6">
	<thead id="sorters">
		<tr>
			<th data-sort="author">Author</th>
			<th>Title</th>
			<th data-sort="year">Year</th>
			<th data-sort="entry_date" data-sorting="desc">Entry date</th>
			<th data-sort="rating">Rating</th>
			<th onclick="this.parentNode.parentNode.parentNode.classList.toggle('collecting')">Collections</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($books as $book): ?>
			<tr data-id="<?= html($book->id) ?>" data-collections="<?= html(json_encode($book->collections)) ?>">
				<td data-sort="author"><?= html($book->author) ?></td>
				<td><?= html($book->title) ?></td>
				<td data-sort="year"><?= html($book->year) ?></td>
				<td data-sort="entry_date" nowrap><?= html($book->entry_date) ?></td>
				<td data-sort="rating" data-value="<?= (5 - $book->rating) ?>" class="rating rating-<?= $book->rating ?>-5">
					<a class="rate-book" data-rating="<?= $book->rating ?>" href="#">
						<?= $book->rating ? $book->rating . ' / 5' : '&nbsp;' ?>
					</a>
				</td>
				<td class="collections">
					<? foreach ($collections as $id => $collection):
						$on = $book->hasCollection($id);
						?>
						<input  id="b-<?= $book->id ?>-c-<?= $id ?>" type="checkbox" value="<?= $id ?>" <?= $on ? 'checked' : '' ?> class="<?= $collection->relevant ? '' : 'not-relevant' ?>" />
						<label for="b-<?= $book->id ?>-c-<?= $id ?>"><?= html($collection) ?></label>
						<br>
					<? endforeach ?>
				</td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>
</div>

<script>
var rows = [].slice.call(document.querySelectorAll('tr[data-collections]'));
var filterShowingElement = document.querySelector('#filter-showing');
var filterCollectionElement = document.querySelector('#filter-collection');
var filterTextElement = document.querySelector('#filter-text');
var sortersElement = document.querySelector('#sorters');

/**
 * FILTER
 */

function setCollections(row) {
	row._collections = JSON.parse(row.dataset.collections);
}
[].map.call(rows, setCollections);

function filter() {
	var collValue = parseInt(filterCollectionElement.value) || 0;
	var textValue = filterTextElement.value.trim().toLowerCase();

	var count = 0;
	for (var i = 0; i < rows.length; i++) {
		var collHide = collValue && rows[i]._collections.indexOf(collValue) == -1;
		var textHide = textValue && rows[i].textContent.toLowerCase().indexOf(textValue) == -1;
		var hide = collHide || textHide;
		rows[i].classList[hide ? 'add' : 'remove']('filter-hide');
		count += hide ? 0 : 1;
	}

	filterShowingElement.textContent = count;
}

(filterCollectionElement.value || filterTextElement.value) && filter();
filterCollectionElement.onchange = function(e) {
	filter();
};
filterTextElement.oninput = function(e) {
	filter();
};

/**
 * SORT
 */

sortersElement.onclick = function(e) {
	var sorter = e.target.dataset.sort;
	if (sorter) {
		var newSort = e.target;
		var prevSort = document.querySelector('[data-sorting]');
		if (newSort == prevSort) {
			newSort.dataset.sorting = newSort.dataset.sorting == 'asc' ? 'desc' : 'asc';
		}
		else {
			delete prevSort.dataset.sorting;
			newSort.dataset.sorting = 'asc';
		}
		var desc = newSort.dataset.sorting == 'asc' ? 1 : -1;

		console.time('Sorting rows');
		rows.sort(function(a, b) {
			var va = a.querySelector('td[data-sort="' + sorter + '"]');
			va = va.dataset.value || va.textContent.trim();
			var vb = b.querySelector('td[data-sort="' + sorter + '"]');
			vb = vb.dataset.value || vb.textContent.trim();
			var dir = va == vb ? (a.dataset.id - b.dataset.id) : va > vb ? 1 : -1;
			return desc * dir;
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

/**
 * RATE
 */

var raters = document.querySelectorAll('.rate-book');
for (var i = 0; i < raters.length; i++) {
	raters[i].onclick = function(e) {
		e.preventDefault();
		var a = this;
		a.classList.add('working');

		var rating = parseInt(a.dataset.rating);
		rating = prompt('Rating', rating);
		if (isNaN(parseInt(rating))) {
			return;
		}

		var xhr = new XMLHttpRequest;
		xhr.open('POST', '?', true);
		xhr.onload = function(e) {
			a.textContent = this.responseText + ' / 5';
			a.parentNode.classList.remove('rating-' + a.dataset.rating + '-5');
			a.dataset.rating = this.responseText;
			a.parentNode.classList.add('rating-' + a.dataset.rating + '-5');
			a.classList.remove('working');
		};
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.send('book=' + a.parentNode.parentNode.dataset.id + '&rating=' + rating);
	};
}

/**
 * COLLECT
 */

var collecters = document.querySelectorAll('.collections input');
for (var i = 0; i < collecters.length; i++) {
	collecters[i].onchange = function(e) {
		e.preventDefault();
		var inp = this;
		var lab = this.nextElementSibling;
		lab.classList.add('working');
		inp.disabled = true;

		var xhr = new XMLHttpRequest;
		xhr.open('POST', '?', true);
		xhr.onload = function(e) {
			lab.classList.remove('working');
			inp.disabled = false;

			var el = inp;
			while (el.nodeName != 'TR') {
				el = el.parentNode;
			}
			var ids = [].map.call(el.querySelectorAll('.collections :checked'), function(cb) {
				return parseInt(cb.value);
			});
			el.dataset.collections = JSON.stringify(ids);
			setCollections(el);
		};
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.send('book=' + inp.parentNode.parentNode.dataset.id + '&collection=' + inp.value + '&add=' + Number(inp.checked));
	};
}
</script>

<?php

include 'tpl.footer.php';
