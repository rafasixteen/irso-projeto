<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Users\UserService;

$body = json_decode(file_get_contents('php://input'), true);
$name = $body['name'] ?? null;
$role = $body['role'] ?? null;
$gender = $body['gender'] ?? null;

if (empty($name) || empty($role) || empty($gender) || !in_array($role, ['member', 'janitor', 'admin'], true) || !in_array($gender, ['M', 'F'], true)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'name' => 'NAME',
				'role' => 'member/janitor/admin',
				'gender' => 'M/F',
			],
		],
	]);
	exit();
}

$userService = new UserService();
$success = $userService->create($name, $role, $gender);

if ($success) {
	echo json_encode(['message' => "User '{$name}' added successfully"]);
} else {
	http_response_code(500);
	echo json_encode(['error' => "Failed to add user '{$name}'"]);
}
