<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;
use App\Rfids\RfidService;
use App\Users\UserService;

$body = json_decode(file_get_contents('php://input'), true);
$rfidTag = $body['rfid_tag'] ?? null;
$message = $body['message'] ?? null;

if (empty($rfidTag) || empty($message)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'rfid_tag' => 'string',
				'message' => 'string',
			],
		],
	]);
	exit();
}

$doorService = new DoorService();
$rfidService = new RfidService();
$userService = new UserService();

if ($doorService->get_door_by_id($id) === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "RFID reader '{$id}' not found",
		],
	]);
	exit();
}

if ($userService->get_user_by_rfid($rfidTag) === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "User RFID tag '{$rfidTag}' not found",
		],
	]);
	exit();
}

$latest = $rfidService->get_latest_history_entry($id);

if ($rfidService->add_history_entry($id, $rfidTag, $message)) {
	http_response_code(204);
	exit();
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Could not add history entry']);
}