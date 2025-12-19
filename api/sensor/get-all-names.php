<?php
declare(strict_types=1);

use App\Service\SensorService;

$service = new SensorService();
$names = $service->getAllNames();

header('Content-Type: application/json');
echo json_encode([
	'sensors' => $names,
]);