<?php
declare(strict_types=1);

use App\Service\SensorService;

$value = $_POST['value'] ?? null;

if ($value === null) {
	http_response_code(404);
	echo json_encode(['error' => 'Missing value parameter']);
	exit();
}

$service = new SensorService();
$success = $service->set($name, $value);

if (!$success) {
	http_response_code(500);
	echo json_encode(['error' => "Sensor '{$name}' not found."]);
	exit();
}

header('Content-Type: application/json');
echo json_encode([
	'message' => "Updated '{$name}' sensor to '{$value}'.",
]);
