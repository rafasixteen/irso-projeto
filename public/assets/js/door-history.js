function load(id) {
	$.ajax({
		url: `/api/doors/${encodeURIComponent(id)}/history`,
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
                        <td>${entry.state}</td>
                    </tr>
                `);
			});
		},
		error: function (xhr) {
			if (window.doorHistoryInterval) {
				clearInterval(window.doorHistoryInterval);
				window.doorHistoryInterval = null;
			}

			$('#table').hide();

			if (xhr.status === 404) {
				$('#history-error').text('Door not found.').show();
			} else {
				$('#history-error').text('Failed to load door history.').show();
			}

			console.error(`Failed to load door ${id} history`, xhr);
		},
	});
}

const pathSegments = window.location.pathname.split('/').filter(Boolean);
const doorId = pathSegments[pathSegments.length - 1];

window.doorHistoryInterval = setInterval(() => load(doorId), 1000);
load(doorId);
