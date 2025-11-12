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

if (!$input || !isset($input['operation']) || !isset($input['filename'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$operation = $input['operation'];
$filename = basename($input['filename']);
$inputPath = $config['storage_path'] . '/uploads/' . $filename;

if (!file_exists($inputPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Video not found']);
    exit;
}

$ffmpeg = new FFmpegService();
$outputFilename = 'processed_' . uniqid() . '_' . time() . '.mp4';
$outputPath = $config['storage_path'] . '/temp/' . $outputFilename;

try {
    switch ($operation) {
        case 'trim':
            if (!isset($input['start']) || !isset($input['duration'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Start and duration required']);
                exit;
            }

            $start = (float)$input['start'];
            $duration = (float)$input['duration'];

            if ($ffmpeg->trim($inputPath, $start, $duration, $outputPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'trim',
                    'path' => '/storage/temp/' . $outputFilename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Trim operation failed']);
            }
            break;

        case 'cut':
            if (!isset($input['segments']) || !is_array($input['segments'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Segments array required']);
                exit;
            }

            if ($ffmpeg->cut($inputPath, $input['segments'], $outputPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'cut',
                    'path' => '/storage/temp/' . $outputFilename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Cut operation failed']);
            }
            break;

        case 'thumbnail':
            if (!isset($input['time'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Time required']);
                exit;
            }

            $time = (float)$input['time'];
            $thumbFilename = 'thumb_' . uniqid() . '.jpg';
            $thumbPath = $config['storage_path'] . '/temp/' . $thumbFilename;

            if ($ffmpeg->generateThumbnail($inputPath, $time, $thumbPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $thumbFilename,
                    'operation' => 'thumbnail',
                    'path' => '/storage/temp/' . $thumbFilename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Thumbnail generation failed']);
            }
            break;

        case 'metadata':
            $metadata = $ffmpeg->getMetadata($inputPath);
            if ($metadata) {
                echo json_encode([
                    'success' => true,
                    'metadata' => $metadata
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Metadata extraction failed']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown operation']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Processing error: ' . $e->getMessage()]);
}
