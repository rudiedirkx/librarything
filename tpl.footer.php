
	<p>Loaded in <span id="domready">?</span> ms</p>

	<details>
		<summary><?= count($client->guzzle->log) ?> requests</summary>
		<pre><? print_r($client->guzzle->log) ?></pre>
	</details>

	<br />

	<script>
	window.onload = function() {
		setTimeout(function() {
			document.querySelector('#domready').textContent = (performance.timing.loadEventEnd - performance.timing.navigationStart);
		});
	};
	</script>

</body>

</html>
