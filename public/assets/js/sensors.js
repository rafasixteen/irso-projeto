function load() {
	$.ajax({
		url: '/api/sensors',
		method: 'GET',
		headers: {
			Authorization: 'Bearer 5b57634f8e96f1c24ae7748f6069e5cf',
		},
		success: function (data) {
			const $tbody = $('#table tbody');
			$tbody.empty();

			Object.entries(data).forEach(([name, sensor]) => {
				if (!sensor) return;

				$tbody.append(`
					<tr>
						<td>${name}</td>
						<td>${formatIsoString(sensor.timestamp)}</td>
						<td>${formatValue(sensor.value)}</td>
						<td>
							<a href="/sensors/${encodeURIComponent(name)}">
								View History
							</a>
						</td>
					</tr>
				`);
			});
		},
		error: function () {
			console.error('Failed to load sensors');
		},
	});
}

function formatValue(value) {
	if (value === null) return '';
	if (typeof value !== 'object') return value;

	return Object.entries(value)
		.map(([key, val]) => `${key}: ${val}`)
		.join(', ');
}

load();
setInterval(load, 1000);
