<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\USers\UserService;

$body = json_decode(file_get_contents('php://input'), true);
$name = $body['name'] ?? null;

if (empty($name)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'name' => 'NAME',
			],
		],
	]);
	exit();
}

$userService = new UserService();
$success = $userService->create($name);

if ($success) {
	echo json_encode(['message' => "User '{$name}' added successfully"]);
} else {
	http_response_code(500);
	echo json_encode(['error' => "Failed to add user '{$name}'"]);
}
