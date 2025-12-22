<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;

$doorService = new DoorService();

if (!$doorService->exists($id)) {
	http_response_code(404);
	echo json_encode(['error' => 'Door not found']);
	exit();
}

$success = $doorService->delete($id);

if ($success) {
	echo json_encode(['message' => 'Door deleted successfully']);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to delete door']);
}
