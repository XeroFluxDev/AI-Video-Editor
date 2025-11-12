<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use App\AIService;
use App\FFmpegService;

$config = require __DIR__ . '/../../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['filename']) || !isset($input['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Filename and prompt required']);
    exit;
}

$apiKey = getenv('OPENROUTER_API_KEY') ?: $_ENV['OPENROUTER_API_KEY'] ?? null;

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenRouter API key not configured']);
    exit;
}

$filename = basename($input['filename']);
$userPrompt = $input['prompt'];
$template = $input['template'] ?? 'analyze_video';

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
    $metadata = $ffmpeg->getMetadata($inputPath);

    if (!$metadata) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to extract video metadata']);
        exit;
    }

    $ai = new AIService($apiKey);

    $cacheKey = $filename . ':' . $userPrompt . ':' . $template;
    $cached = $ai->getCachedResponse($cacheKey);

    if ($cached) {
        echo json_encode([
            'success' => true,
            'cached' => true,
            'suggestions' => $cached['suggestions'],
            'metadata' => $metadata
        ]);
        exit;
    }

    $result = $ai->analyzeVideo($metadata, $userPrompt, $template);

    if ($result['success']) {
        $ai->cacheResponse($cacheKey, $result);
    }

    echo json_encode([
        'success' => true,
        'cached' => false,
        'suggestions' => $result['suggestions'],
        'metadata' => $metadata,
        'raw' => $result['raw'] ?? null
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
