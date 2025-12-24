<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;

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

$success = $doorService->delete_door_by_id($id);

if ($success) {
	http_response_code(200);
	echo json_encode([
		'message' => 'Door deleted successfully',
	]);
} else {
	http_response_code(500);
	echo json_encode([
		'error' => [
			'code' => 'SERVER_ERROR',
			'message' => "Failed to delete door '{$id}'",
		],
	]);
}