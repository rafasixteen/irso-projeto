<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../src/auth.php';

require_login();

use App\Users\UserService;

$userService = new UserService();
$users = $userService->get_users();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Users</title>
</head>

<body>
	<h1>User List</h1>
	<table border="1">
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Role</th>
				<th>Gender</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><?php echo $user->id; ?></td>
				<td><?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?></td>
				<td><?php echo htmlspecialchars($user->role, ENT_QUOTES, 'UTF-8'); ?></td>
				<td><?php echo htmlspecialchars($user->gender, ENT_QUOTES, 'UTF-8'); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</body>

</html>