<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'Actuator History';
$view = __DIR__ . '/../pages/actuator-history.php';
$styles = ['/assets/css/shared/table.css', '/assets/css/shared/history-error.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/actuator-history.js', '/assets/js/utils/format-iso.js'];

require __DIR__ . '/../layouts/auth.php';
