<?php
header('Content-Type: application/json');

$config = require __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['filename'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Filename required']);
    exit;
}

$filename = basename($input['filename']);
$tempPath = $config['storage_path'] . '/temp/' . $filename;

if (!file_exists($tempPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Video not found']);
    exit;
}

$exportFilename = 'export_' . uniqid() . '_' . time() . '.mp4';
$exportPath = $config['storage_path'] . '/exports/' . $exportFilename;

if (copy($tempPath, $exportPath)) {
    echo json_encode([
        'success' => true,
        'filename' => $exportFilename,
        'download_url' => '/storage/exports/' . $exportFilename,
        'size' => filesize($exportPath)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Export failed']);
}
