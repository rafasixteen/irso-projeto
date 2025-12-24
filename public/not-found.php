<?php
$title = 'Not Found';
$view = __DIR__ . '/../pages/not-found.php';
$styles = ['/assets/css/not-found.css'];

ob_start();
require $view;
$content = ob_get_clean();

require __DIR__ . '/../layouts/root.php';