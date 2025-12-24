<?php
declare(strict_types=1);

use App\Service\SensorService;

$service = new SensorService();
$history = $service->get_history($name);

header('Content-Type: application/json');
echo json_encode($history);
