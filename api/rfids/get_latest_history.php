<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Rfids\RfidService;

$rfidService = new RfidService();
echo json_encode($rfidService->get_latest_history());