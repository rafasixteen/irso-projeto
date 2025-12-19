<?php
declare(strict_types=1);

use App\Service\ActuatorService;

$service = new ActuatorService();
$value = $service->getLatestValue($name);

if ($value === null) {
	http_response_code(404);
	echo json_encode(['error' => "Actuator '{$name}' not found"]);
	exit();
}

header('Content-Type: application/json');
echo json_encode([
	'value' => $value,
]);