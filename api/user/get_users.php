<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\USers\UserService;

$userService = new UserService();
$users = $userService->getUsers();

if ($users) {
	echo json_encode($users);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to get users']);
}
