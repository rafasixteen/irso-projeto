<?php

$UPLOAD_DIR = __DIR__ . '/../storage/uploads';

// Ensure upload directory exists
if (!is_dir($UPLOAD_DIR)) {
	mkdir($UPLOAD_DIR, 0777, true);
}

if (!isset($_FILES['image'])) {
	http_response_code(400);
	echo json_encode(['error' => 'No image uploaded']);
	exit();
}

$image = $_FILES['image'];

if ($image['error'] !== UPLOAD_ERR_OK) {
	http_response_code(500);
	echo json_encode(['error' => 'Upload failed', 'code' => $image['error']]);
	exit();
}

// Save as fixed filename
$destination = $UPLOAD_DIR . '/camera_snapshot.jpg';

// Move uploaded file
if (!move_uploaded_file($image['tmp_name'], $destination)) {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to save image']);
	exit();
}

// Success
http_response_code(200);
echo json_encode([
	'success' => true,
	'path' => $destination,
]);
