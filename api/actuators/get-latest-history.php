<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Service\ActuatorService;
$actuatorService = new ActuatorService();
echo json_encode($actuatorService->get_latest_history());
