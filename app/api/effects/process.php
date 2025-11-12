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

$operation = $_POST['operation'] ?? '';
$filename = $_POST['filename'] ?? '';

if (empty($filename)) {
    http_response_code(400);
    echo json_encode(['error' => 'Filename required']);
    exit;
}

$filename = basename($filename);
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
        case 'text_overlay':
            $text = $_POST['text'] ?? '';
            if (empty($text)) {
                http_response_code(400);
                echo json_encode(['error' => 'Text required']);
                exit;
            }

            $options = [
                'x' => $_POST['x'] ?? '(w-text_w)/2',
                'y' => $_POST['y'] ?? 'h-th-50',
                'fontSize' => (int)($_POST['fontSize'] ?? 24),
                'fontColor' => $_POST['fontColor'] ?? 'white',
                'boxColor' => $_POST['boxColor'] ?? 'black@0.5'
            ];

            if ($ffmpeg->addTextOverlay($inputPath, $outputPath, $text, $options)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'text_overlay'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add text overlay']);
            }
            break;

        case 'watermark':
            if (!isset($_FILES['watermark'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Watermark file required']);
                exit;
            }

            $watermarkPath = $config['storage_path'] . '/temp/watermark_' . uniqid() . '.png';
            move_uploaded_file($_FILES['watermark']['tmp_name'], $watermarkPath);

            $options = [
                'position' => $_POST['position'] ?? 'bottom-right',
                'margin' => (int)($_POST['margin'] ?? 10)
            ];

            if ($ffmpeg->addWatermark($inputPath, $watermarkPath, $outputPath, $options)) {
                if (file_exists($watermarkPath)) unlink($watermarkPath);
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'watermark'
                ]);
            } else {
                if (file_exists($watermarkPath)) unlink($watermarkPath);
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add watermark']);
            }
            break;

        case 'adjust_speed':
            $speed = (float)($_POST['speed'] ?? 1.0);

            if ($speed <= 0 || $speed > 4) {
                http_response_code(400);
                echo json_encode(['error' => 'Speed must be between 0.1 and 4.0']);
                exit;
            }

            if ($ffmpeg->adjustSpeed($inputPath, $outputPath, $speed)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'adjust_speed',
                    'speed' => $speed
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to adjust speed']);
            }
            break;

        case 'change_resolution':
            $width = (int)($_POST['width'] ?? 0);
            $height = (int)($_POST['height'] ?? 0);

            if ($width <= 0 || $height <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid resolution']);
                exit;
            }

            if ($ffmpeg->changeResolution($inputPath, $outputPath, $width, $height)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'change_resolution',
                    'width' => $width,
                    'height' => $height
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to change resolution']);
            }
            break;

        case 'apply_filters':
            $filters = [];

            if (isset($_POST['brightness'])) {
                $filters['brightness'] = (float)$_POST['brightness'];
            }

            if (isset($_POST['contrast'])) {
                $filters['contrast'] = (float)$_POST['contrast'];
            }

            if (isset($_POST['saturation'])) {
                $filters['saturation'] = (float)$_POST['saturation'];
            }

            if (isset($_POST['gamma'])) {
                $filters['gamma'] = (float)$_POST['gamma'];
            }

            if (empty($filters)) {
                http_response_code(400);
                echo json_encode(['error' => 'No filters specified']);
                exit;
            }

            if ($ffmpeg->applyFilters($inputPath, $outputPath, $filters)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'operation' => 'apply_filters',
                    'filters' => $filters
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to apply filters']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown operation']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Effects processing error: ' . $e->getMessage()]);
}
