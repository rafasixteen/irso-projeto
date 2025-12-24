function load(id) {
	$.ajax({
		url: `/api/sensors/${encodeURIComponent(id)}`,
		method: 'GET',
		headers: {
			Authorization: 'Bearer 5b57634f8e96f1c24ae7748f6069e5cf',
		},
		success: function (history) {
			$('#history-error').hide();
			$('#table').show();

			const $tbody = $('#table tbody');
			$tbody.empty();

			history.forEach((entry) => {
				$tbody.append(`
                    <tr>
                        <td>${formatIsoString(entry.timestamp)}</td>
                        <td>${formatValue(entry.value)}</td>
                    </tr>
                `);
			});
		},
		error: function (xhr) {
			if (window.sensorHistoryInterval) {
				clearInterval(window.sensorHistoryInterval);
				window.sensorHistoryInterval = null;
			}

			$('#table').hide();

			if (xhr.status === 404) {
				$('#history-error').text('Sensor not found.').show();
			} else {
				$('#history-error').text('Failed to load sensor history.').show();
			}

			console.error(`Failed to load sensor ${id} history`, xhr);
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

const pathSegments = window.location.pathname.split('/').filter(Boolean);
const sensorId = pathSegments[pathSegments.length - 1];

window.sensorHistoryInterval = setInterval(() => load(sensorId), 1000);
load(sensorId);
