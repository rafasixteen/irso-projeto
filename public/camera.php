<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'Camera';
$view = __DIR__ . '/../pages/camera.php';
$styles = ['/assets/css/camera.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/camera.js'];

require __DIR__ . '/../layouts/auth.php';
