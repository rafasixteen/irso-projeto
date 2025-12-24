<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'RFID Sensors';
$view = __DIR__ . '/../pages/rfid-sensors.php';
$styles = ['/assets/css/shared/table.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/rfid-sensors.js', '/assets/js/utils/format-iso.js'];

require __DIR__ . '/../layouts/auth.php';
