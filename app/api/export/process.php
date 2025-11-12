<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use App\FFmpegService;

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
$format = $input['format'] ?? 'mp4';
$quality = $input['quality'] ?? '1080p';
$preset = $input['preset'] ?? 'medium';

$inputPath = $config['storage_path'] . '/uploads/' . $filename;

if (!file_exists($inputPath)) {
    $inputPath = $config['storage_path'] . '/temp/' . $filename;
    if (!file_exists($inputPath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Video not found']);
        exit;
    }
}

try {
    $ffmpeg = new FFmpegService();

    $extension = $format === 'webm' ? '.webm' : '.mp4';
    $outputFilename = 'export_' . time() . '_' . $quality . $extension;
    $outputPath = $config['storage_path'] . '/exports/' . $outputFilename;

    if (!is_dir($config['storage_path'] . '/exports')) {
        mkdir($config['storage_path'] . '/exports', 0755, true);
    }

    $options = [
        'format' => $format,
        'quality' => $quality,
        'preset' => $preset
    ];

    if ($ffmpeg->exportVideo($inputPath, $outputPath, $options)) {
        $fileSize = filesize($outputPath);

        echo json_encode([
            'success' => true,
            'filename' => $outputFilename,
            'format' => $format,
            'quality' => $quality,
            'size' => $fileSize,
            'download_url' => '../storage/exports/' . $outputFilename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Export failed']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Export error: ' . $e->getMessage()]);
}
