<?php
declare(strict_types=1);

use App\Service\ActuatorService;

$service = new ActuatorService();
$actuators = $service->getAll();

header('Content-Type: application/json');
echo json_encode([
	'actuators' => $actuators,
]);
