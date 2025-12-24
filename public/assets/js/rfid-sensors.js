function loadRfidSensors() {
	$.ajax({
		url: '/api/rfids/history/latest',
		method: 'GET',
		headers: {
			Authorization: 'Bearer 5b57634f8e96f1c24ae7748f6069e5cf',
		},
		success: function (rfidSensors) {
			const $tbody = $('#rfid-sensors-table tbody');
			$tbody.empty();

			rfidSensors.forEach((rfidSensor) => {
				if (rfidSensor.latest === null) return;
				$tbody.append(`
                    <tr>
                        <td>${rfidSensor.id}</td>
                        <td>${formatIsoString(rfidSensor.latest.timestamp)}</td>
                        <td>${rfidSensor.latest.rfid_tag}</td>
                        <td>${rfidSensor.latest.message}</td>
						<td>
                            <a href="/rfid-sensors/${encodeURIComponent(rfidSensor.id)}">
                                View History
                            </a>
                        </td>
                    </tr>
                `);
			});
		},
		error: function () {
			console.error('Failed to load RFID sensors latest history');
		},
	});
}

loadRfidSensors();
setInterval(loadRfidSensors, 1000);
