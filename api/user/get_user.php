<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\USers\UserService;

$id = (int) $id;
$userService = new UserService();

if (!$userService->exists($id)) {
	http_response_code(404);
	header('Content-Type: application/json');
	echo json_encode(['error' => 'User not found']);
	exit();
}

$user = $userService->read($id);

if ($user) {
	echo json_encode($user);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to get user']);
}
