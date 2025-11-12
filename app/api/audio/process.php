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
$operation = $input['operation'] ?? '';

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
    $outputFilename = $operation . '_' . uniqid() . '.mp4';
    $outputPath = $config['storage_path'] . '/temp/' . $outputFilename;

    switch ($operation) {
        case 'remove_audio':
            if ($ffmpeg->removeAudio($inputPath, $outputPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'remove_audio'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to remove audio']);
            }
            break;

        case 'replace_audio':
            if (!isset($_FILES['audio'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Audio file required']);
                exit;
            }

            $audioPath = $config['storage_path'] . '/temp/audio_' . uniqid() . '.mp3';
            move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath);

            if ($ffmpeg->replaceAudio($inputPath, $audioPath, $outputPath)) {
                if (file_exists($audioPath)) unlink($audioPath);
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'replace_audio'
                ]);
            } else {
                if (file_exists($audioPath)) unlink($audioPath);
                http_response_code(500);
                echo json_encode(['error' => 'Failed to replace audio']);
            }
            break;

        case 'adjust_volume':
            $volume = (float)($input['volume'] ?? 1.0);

            if ($ffmpeg->adjustVolume($inputPath, $outputPath, $volume)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'adjust_volume',
                    'volume' => $volume
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to adjust volume']);
            }
            break;

        case 'normalize_audio':
            if ($ffmpeg->normalizeAudio($inputPath, $outputPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'normalize_audio'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to normalize audio']);
            }
            break;

        case 'remove_silence':
            $threshold = (float)($input['threshold'] ?? -50);
            $duration = (float)($input['duration'] ?? 0.5);

            if ($ffmpeg->removeSilence($inputPath, $outputPath, $threshold, $duration)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'remove_silence'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to remove silence']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown operation']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Audio processing error: ' . $e->getMessage()]);
}
