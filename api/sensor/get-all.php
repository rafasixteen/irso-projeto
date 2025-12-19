<?php
declare(strict_types=1);

use App\Service\SensorService;

$service = new SensorService();
$sensors = $service->getAll();

header('Content-Type: application/json');
echo json_encode([
	'sensors' => $sensors,
]);
