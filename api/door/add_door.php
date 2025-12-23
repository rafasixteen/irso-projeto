<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\Door;
use App\Doors\DoorService;

$body = json_decode(file_get_contents('php://input'), true);
$id = $body['id'] ?? null;
$type = $body['type'] ?? null;
$allowedGender = $body['allowed_gender'] ?? null;

if (empty($id) || empty($type)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'id' => 'ID',
				'type' => 'TYPE',
				'allowed_gender (optional)' => 'M/F',
			],
		],
	]);
	exit();
}

$doorService = new DoorService();

if ($doorService->exists($id)) {
	http_response_code(409);
	echo json_encode(['error' => "Door '{$id}' already exists"]);
	exit();
}

$door = new Door($id, $type, $allowedGender);
$success = $doorService->create($door);

if ($success) {
	echo json_encode(['message' => "Door '{$id}' added successfully"]);
} else {
	http_response_code(500);
	echo json_encode(['error' => "Failed to add door '{$id}'"]);
}