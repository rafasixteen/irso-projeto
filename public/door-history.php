<?php
require __DIR__ . '/../vendor/autoload.php';

$title = 'Door History';
$view = __DIR__ . '/../pages/door-history.php';
$styles = ['/assets/css/table.css'];
$scripts = ['https://code.jquery.com/jquery-3.7.1.min.js', '/assets/js/door-history.js', '/assets/js/utils/format-iso.js'];

require __DIR__ . '/../layouts/auth.php';
