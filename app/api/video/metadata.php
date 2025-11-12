<?php
header('Content-Type: application/json');

$config = require __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$filename = $_GET['filename'] ?? '';
if (empty($filename)) {
    http_response_code(400);
    echo json_encode(['error' => 'Filename required']);
    exit;
}

$filepath = $config['storage_path'] . '/uploads/' . basename($filename);

if (!file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Video not found']);
    exit;
}

$size = filesize($filepath);
$mime = mime_content_type($filepath);

echo json_encode([
    'success' => true,
    'filename' => basename($filename),
    'path' => $filepath,
    'size' => $size,
    'mime_type' => $mime,
    'size_mb' => round($size / 1024 / 1024, 2)
]);
