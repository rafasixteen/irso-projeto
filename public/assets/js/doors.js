function loadDoors() {
	$.ajax({
		url: '/api/doors/history/latest',
		method: 'GET',
		headers: {
			Authorization: 'Bearer 5b57634f8e96f1c24ae7748f6069e5cf',
		},
		success: function (doors) {
			const $tbody = $('#doors-table tbody');
			$tbody.empty();

			doors.forEach((door) => {
				if (door.latest === null) return;

				$tbody.append(`
                    <tr>
                        <td>${door.id}</td>
                        <td>${formatIsoString(door.latest.timestamp)}</td>
                        <td>${door.latest.state}</td>
						<td>
                            <a href="/doors/${encodeURIComponent(door.id)}">
                                View History
                            </a>
                        </td>
                    </tr>
                `);
			});
		},
		error: function () {
			console.error('Failed to load doors latest history');
		},
	});
}

loadDoors();
setInterval(loadDoors, 1000);
