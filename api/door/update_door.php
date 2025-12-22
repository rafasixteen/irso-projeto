<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;

$body = json_decode(file_get_contents('php://input'), true);
$open = $body['open'] ?? null;

if (!is_bool($open)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'open' => 'boolean',
			],
		],
	]);
	exit();
}

$doorService = new DoorService();

if (!$doorService->exists($id)) {
	http_response_code(404);
	echo json_encode(['error' => 'Door not found']);
	exit();
}

if ($doorService->is_door_open($id) === $open) {
	echo json_encode(['message' => 'Door is already ' . ($open ? 'open' : 'closed')]);
	exit();
}

$success = $doorService->update_state($id, $open);

if ($success) {
	echo json_encode(['message' => 'Door ' . ($open ? 'opened' : 'closed') . ' successfully']);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to ' . ($open ? 'open' : 'close') . ' door']);
}
