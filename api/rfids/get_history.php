<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;
use App\Rfids\RfidService;

$doorService = new DoorService();
$rfidService = new RfidService();

if ($doorService->get_door_by_id($id) === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "Rfid '{$id}' not found",
		],
	]);
	exit();
} else {
	http_response_code(200);
	echo json_encode($rfidService->get_history($id));
}