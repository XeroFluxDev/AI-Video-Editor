<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use App\SubtitleService;

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

$whisperApiKey = getenv('OPENAI_API_KEY') ?: $_ENV['OPENAI_API_KEY'] ?? null;

if (!$whisperApiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenAI API key not configured']);
    exit;
}

$filename = basename($input['filename']);
$operation = $input['operation'] ?? 'generate';
$language = $input['language'] ?? 'en';
$style = $input['style'] ?? [];

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
    $subtitle = new SubtitleService();

    switch ($operation) {
        case 'generate':
            $audioPath = $subtitle->extractAudio($inputPath);

            if (!$audioPath) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to extract audio']);
                exit;
            }

            $transcription = $subtitle->transcribeWithWhisper($audioPath, $whisperApiKey, $language);

            if (file_exists($audioPath)) {
                unlink($audioPath);
            }

            if (!$transcription) {
                http_response_code(500);
                echo json_encode(['error' => 'Transcription failed']);
                exit;
            }

            $srtContent = $subtitle->convertToSRT($transcription);
            $srtFilename = 'subtitles_' . uniqid() . '.srt';
            $srtPath = $subtitle->saveSRT($srtContent, $srtFilename);

            echo json_encode([
                'success' => true,
                'srt_filename' => $srtFilename,
                'srt_content' => $srtContent,
                'segments' => count($transcription['segments'] ?? []),
                'duration' => $transcription['duration'] ?? 0
            ]);
            break;

        case 'burn':
            if (!isset($input['srt_filename'])) {
                http_response_code(400);
                echo json_encode(['error' => 'SRT filename required']);
                exit;
            }

            $srtPath = $config['storage_path'] . '/temp/' . basename($input['srt_filename']);

            if (!file_exists($srtPath)) {
                http_response_code(404);
                echo json_encode(['error' => 'Subtitle file not found']);
                exit;
            }

            $outputFilename = 'subtitled_' . uniqid() . '.mp4';
            $outputPath = $config['storage_path'] . '/temp/' . $outputFilename;

            if ($subtitle->burnSubtitles($inputPath, $srtPath, $outputPath, $style)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'type' => 'burned',
                    'path' => '/storage/temp/' . $outputFilename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to burn subtitles']);
            }
            break;

        case 'embed':
            if (!isset($input['srt_filename'])) {
                http_response_code(400);
                echo json_encode(['error' => 'SRT filename required']);
                exit;
            }

            $srtPath = $config['storage_path'] . '/temp/' . basename($input['srt_filename']);

            if (!file_exists($srtPath)) {
                http_response_code(404);
                echo json_encode(['error' => 'Subtitle file not found']);
                exit;
            }

            $outputFilename = 'embedded_' . uniqid() . '.mp4';
            $outputPath = $config['storage_path'] . '/temp/' . $outputFilename;

            if ($subtitle->embedSubtitles($inputPath, $srtPath, $outputPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $outputFilename,
                    'type' => 'embedded',
                    'path' => '/storage/temp/' . $outputFilename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to embed subtitles']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown operation']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Subtitle error: ' . $e->getMessage()]);
}
