<?php
declare(strict_types=1);

use App\Doors\DoorService;
use App\Users\UserService;
use App\Access\AccessService;

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$rfidTag = $body['tag'] ?? null;
$doorId = $body['door'] ?? null;

if (empty($rfidTag) || empty($doorId)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'tag' => 'TAG',
				'door' => 'DOOR',
			],
		],
	]);
	exit();
}

$userService = new UserService();
$doorService = new DoorService();
$accessService = new AccessService();

$user = $userService->get_user_by_rfid($rfidTag);

if (!$user) {
	http_response_code(404);
	echo json_encode(['authorized' => false, 'message' => 'User not found']);
	exit();
}

$door = $doorService->get_door_by_id($doorId);

if (!$door) {
	http_response_code(404);
	echo json_encode(['authorized' => false, 'message' => 'Door not found']);
	exit();
}

$accessResult = $accessService->checkAccess($user, $door);

echo json_encode([
	'authorized' => $accessResult['authorized'],
	'message' => $accessResult['reason'],
	'user' => $user->name,
]);
