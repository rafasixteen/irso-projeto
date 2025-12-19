<?php
declare(strict_types=1);

use App\Service\SensorService;

$service = new SensorService();
$sensors = $service->getHistory($name);

header('Content-Type: application/json');
echo json_encode([
	'history' => $sensors,
]);