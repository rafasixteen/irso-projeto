<?php
declare(strict_types=1);

use App\Doors\DoorService;
use App\Users\UserService;
use App\Rfids\RfidService;

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$rfidTag = $body['rfid_tag'] ?? null;
$doorId = $body['door_id'] ?? null;

if (empty($rfidTag) || empty($doorId)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'rfid_tag' => 'string',
				'door_id' => 'string',
			],
		],
	]);
	exit();
}

$userService = new UserService();
$doorService = new DoorService();
$rfidService = new RfidService();

$user = $userService->get_user_by_rfid((string) $rfidTag);
$door = $doorService->get_door_by_id($doorId);

if ($user === null || $door === null) {
	echo json_encode([
		'authorized' => false,
		'message' => 'Invalid RFID tag or door ID',
		'rfid_tag' => $rfidTag,
		'door_id' => $doorId,
	]);
} else {
	$accessResult = $rfidService->check_access($user, $door);

	echo json_encode([
		'authorized' => $accessResult['authorized'],
		'message' => $accessResult['reason'],
		'user' => $user->name,
		'door_id' => $door->id,
	]);
}
