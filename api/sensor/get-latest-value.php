<?php
declare(strict_types=1);

use App\Service\SensorService;

$service = new SensorService();
$value = $service->getLatestValue($name);

if ($value === null) {
	http_response_code(404);
	echo json_encode(['error' => "Sensor '{$name}' not found"]);
	exit();
}

header('Content-Type: application/json');
echo json_encode([
	'value' => $value,
]);