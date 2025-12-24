function load(id) {
	$.ajax({
		url: `/api/rfids/${encodeURIComponent(id)}/history`,
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
                        <td>${entry.rfid_tag}</td>
                        <td>${entry.message}</td>
                    </tr>
                `);
			});
		},
		error: function (xhr) {
			if (window.rfidSensorHistoryInterval) {
				clearInterval(window.rfidSensorHistoryInterval);
				window.rfidSensorHistoryInterval = null;
			}

			$('#table').hide();

			if (xhr.status === 404) {
				$('#history-error').text('RFID Sensor not found.').show();
			} else {
				$('#history-error').text('Failed to load RFID sensor history.').show();
			}

			console.error(`Failed to load RFID sensor ${id} history`, xhr);
		},
	});
}

const pathSegments = window.location.pathname.split('/').filter(Boolean);
const rfidSensorId = pathSegments[pathSegments.length - 1];

window.rfidSensorHistoryInterval = setInterval(() => load(rfidSensorId), 1000);
load(rfidSensorId);
