<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'RFID Sensor History';
$view = __DIR__ . '/../pages/rfid-sensor-history.php';
$styles = ['/assets/css/shared/table.css', '/assets/css/shared/history-error.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/rfid-sensor-history.js', '/assets/js/utils/format-iso.js'];

require __DIR__ . '/../layouts/auth.php';
