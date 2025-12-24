<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\Door;
use App\Doors\DoorService;

$body = json_decode(file_get_contents('php://input'), true);
$type = $body['type'] ?? null;
$allowedGender = $body['allowed_gender'] ?? null;

if (empty($type)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => 'Missing required fields: type',
			'expected' => [
				'type' => 'string',
				'allowed_gender (optional)' => 'M/F',
			],
		],
	]);
	exit();
}

if ($allowedGender !== null && !in_array($allowedGender, ['M', 'F'], true)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => "Invalid allowed_gender value. Must be 'M' or 'F'.",
			'expected' => [
				'allowed_gender' => 'M/F',
			],
		],
	]);
	exit();
}

$doorService = new DoorService();

if ($doorService->get_door_by_id($id) === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "Door '{$id}' not found",
		],
	]);
	exit();
}

$door = new Door($id, $type, $allowedGender);
$success = $doorService->update_door($door);

if ($success) {
	http_response_code(200);
	echo json_encode($door);
} else {
	http_response_code(500);
	echo json_encode([
		'error' => [
			'code' => 'SERVER_ERROR',
			'message' => "Failed to update door '{$id}'",
		],
	]);
}