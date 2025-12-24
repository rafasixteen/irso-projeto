<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'Users';
$view = __DIR__ . '/../pages/users.php';
$styles = ['/assets/css/shared/table.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/users.js'];

require __DIR__ . '/../layouts/auth.php';
