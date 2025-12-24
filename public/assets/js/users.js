function loadUsers() {
	$.ajax({
		url: '/api/users',
		method: 'GET',
		headers: {
			Authorization: 'Bearer 5b57634f8e96f1c24ae7748f6069e5cf',
		},
		success: function (users) {
			const $tbody = $('#users-table tbody');
			$tbody.empty();

			users.forEach((user) => {
				$tbody.append(`
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.role}</td>
                        <td>${user.gender}</td>
                        <td>${user.rfidTag}</td>
                    </tr>
                `);
			});
		},
		error: function () {
			console.error('Failed to load users');
		},
	});
}

loadUsers();
setInterval(loadUsers, 5000);
