<h1>Door History: <?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></h1>

<div id="door-history-error"></div>

<table id="door-history-table">
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>State</th>
		</tr>
	</thead>
	<tbody>
		<!-- History rows will be populated here -->
	</tbody>
</table>