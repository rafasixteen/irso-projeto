<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title><?= $title ?? 'App' ?></title>

	<link rel="stylesheet" href="/assets/css/global.css">

	<?php if (!empty($styles)): ?>
	<?php foreach ($styles as $style): ?>
	<link rel="stylesheet" href="<?= htmlspecialchars($style, ENT_QUOTES) ?>">
	<?php endforeach; ?>
	<?php endif; ?>
</head>

<body>

	<?= $content ?? '' ?>

	<script src="/assets/js/global.js"></script>

	<?php if (!empty($scripts)): ?>
	<?php foreach ($scripts as $script): ?>
	<script src="<?= htmlspecialchars($script, ENT_QUOTES) ?>"></script>
	<?php endforeach; ?>
	<?php endif; ?>

</body>

</html>