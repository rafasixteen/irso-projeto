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

$id = (int) $id;
$userService = new UserService();

if (!$userService->exists($id)) {
	http_response_code(404);
	header('Content-Type: application/json');
	echo json_encode(['error' => 'User not found']);
	exit();
}

$success = $userService->update($id, $name, $role, $gender);

if ($success) {
	echo json_encode(['message' => 'User updated successfully']);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to update user']);
}
