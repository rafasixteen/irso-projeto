<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Service\SensorService;
$sensorService = new SensorService();
echo json_encode($sensorService->get_latest_history());
