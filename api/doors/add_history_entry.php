<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;

$body = json_decode(file_get_contents('php://input'), true);
$state = $body['state'] ?? null;

if (empty($state)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => 'Missing required field: state',
			'expected' => [
				'state' => 'OPEN/CLOSED',
			],
		],
	]);
	exit();
}

if ($state !== null && !in_array($state, ['OPEN', 'CLOSED', 'LOCKED', 'UNLOCKED'], true)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => "Invalid state value. Must be 'OPEN', 'CLOSED', 'LOCKED', or 'UNLOCKED'.",
			'expected' => [
				'state' => 'OPEN/CLOSED/LOCKED/UNLOCKED',
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

$latest = $doorService->get_latest_history_entry($id);

if ($latest !== null && $latest->state === $state) {
	http_response_code(204);
	exit();
}

if ($doorService->add_history_entry($id, $state)) {
	http_response_code(204);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Could not add history entry']);
}
