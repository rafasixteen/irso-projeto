<?php
declare(strict_types=1);

use App\Service\ActuatorService;

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);

if (!is_array($payload) || !array_key_exists('value', $payload)) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing value parameter']);
	exit();
}

$value = $payload['value'];

$service = new ActuatorService();
$success = $service->appendValue($name, $value);

if (!$success) {
	http_response_code(500);
	echo json_encode(['error' => "Actuator '{$name}' could not be updated."]);
	exit();
}

echo json_encode([
	'message' => "Updated '{$name}' actuator successfully.",
	'value' => $value,
]);