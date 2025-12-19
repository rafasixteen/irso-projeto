<?php
declare(strict_types=1);

use App\Service\ActuatorService;

$service = new ActuatorService();
$Actuators = $service->getHistory($name);

header('Content-Type: application/json');
echo json_encode([
	'history' => $Actuators,
]);