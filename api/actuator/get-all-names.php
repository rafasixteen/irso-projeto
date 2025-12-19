<?php
declare(strict_types=1);

use App\Service\ActuatorService;

$service = new ActuatorService();
$names = $service->getAllNames();

header('Content-Type: application/json');
echo json_encode([
	'actuators' => $names,
]);