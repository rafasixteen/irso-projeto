<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Doors\DoorService;

$doorService = new DoorService();
echo json_encode($doorService->get_latest_history());