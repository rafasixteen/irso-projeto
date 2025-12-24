<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Service\SensorService;

$body = json_decode(file_get_contents('php://input'), true);
$value = $body['value'] ?? null;

if (empty($value)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'code' => 'INVALID_REQUEST',
			'message' => 'Missing required field: value',
			'expected' => [
				'value' => 'some_value',
			],
		],
	]);
	exit();
}

$sensorService = new SensorService();
$latest = $sensorService->get_latest_history_entry($name);

if ($latest !== null && $latest->value === $value) {
	http_response_code(204);
	exit();
}

if ($sensorService->add_history_entry($name, $value)) {
	http_response_code(204);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Could not add history entry']);
}
