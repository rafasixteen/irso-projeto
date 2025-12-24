<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Users\UserService;

$body = json_decode(file_get_contents('php://input'), true);

$name = $body['name'] ?? null;
$role = $body['role'] ?? null;
$gender = $body['gender'] ?? null;

if (empty($name) || empty($role) || empty($gender)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => 'Missing required fields: name, role, or gender',
			'expected' => [
				'name' => 'string',
				'role' => 'member/janitor/admin',
				'gender' => 'M/F',
			],
		],
	]);
	exit();
}

if (!in_array($role, ['member', 'janitor', 'admin'], true)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => "Invalid role value. Must be 'member', 'janitor', or 'admin'.",
		],
	]);
	exit();
}

if (!in_array($gender, ['M', 'F'], true)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => "Invalid gender value. Must be 'M' or 'F'.",
		],
	]);
	exit();
}

$userService = new UserService();
$user = $userService->add_user($name, $role, $gender);

if ($user !== null) {
	http_response_code(201);
	echo json_encode($user);
} else {
	http_response_code(500);
	echo json_encode([
		'error' => [
			'code' => 'SERVER_ERROR',
			'message' => "Failed to add user '{$name}'",
		],
	]);
}