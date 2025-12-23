<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;

$doorService = new DoorService();
$door = $doorService->get_door_by_id($id);

if ($door === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "Door '{$id}' not found",
		],
	]);
	exit();
} else {
	http_response_code(200);
	echo json_encode($door);
}