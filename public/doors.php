<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'Doors';
$view = __DIR__ . '/../pages/doors.php';
$styles = ['/assets/css/doors.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/doors.js', '/assets/js/utils/format-iso.js'];

require __DIR__ . '/../layouts/auth.php';