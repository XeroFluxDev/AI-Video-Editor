<?php
header('Content-Type: application/json');

$config = require __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['video'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No video file uploaded']);
    exit;
}

$file = $_FILES['video'];
$allowed = $config['allowed_extensions'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

if ($file['size'] > $config['upload_max_size']) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large']);
    exit;
}

$videoId = 'vid_' . uniqid() . '_' . time();
$filename = $videoId . '.' . $ext;
$uploadPath = $config['storage_path'] . '/uploads/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

echo json_encode([
    'success' => true,
    'video_id' => $videoId,
    'filename' => $filename,
    'size' => $file['size'],
    'type' => $file['type']
]);
