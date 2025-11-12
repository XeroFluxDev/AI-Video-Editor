<?php

namespace App;

require_once __DIR__ . '/../openrouter-client.php';

use OpenRouterClient\OpenRouterClient;

class AIService
{
    private OpenRouterClient $client;
    private string $model = 'anthropic/claude-3.5-sonnet';
    private array $promptTemplates;

    public function __construct(string $apiKey)
    {
        $this->client = new OpenRouterClient($apiKey);
        $this->initPromptTemplates();
    }

    private function initPromptTemplates(): void
    {
        $this->promptTemplates = [
            'analyze_video' => "You are a professional video editor. Analyze this video and suggest editing improvements.

Video Details:
- Duration: {duration} seconds
- Resolution: {width}x{height}
- Format: {codec}

User Request: {user_prompt}

Provide 3-5 specific, actionable editing suggestions. For each suggestion, specify:
1. The edit type (trim, cut, effects, audio, subtitles)
2. Exact timestamps or ranges
3. Clear reasoning

Format as JSON array with structure:
[
  {
    \"type\": \"trim|cut|effect|audio|subtitle\",
    \"action\": \"Brief description\",
    \"reason\": \"Why this improves the video\",
    \"params\": {\"start\": 0, \"end\": 10}
  }
]",

            'suggest_cuts' => "Analyze this video and suggest where to make cuts or remove sections.

Duration: {duration}s
User context: {user_prompt}

Return JSON array of cut suggestions with timestamps.",

            'improve_pacing' => "Suggest pacing improvements for this video.

Duration: {duration}s
{user_prompt}

Return JSON with specific trim/speed recommendations."
        ];
    }

    public function analyzeVideo(array $metadata, string $userPrompt, string $template = 'analyze_video'): array
    {
        $prompt = $this->buildPrompt($template, $metadata, $userPrompt);

        try {
            $response = $this->client->completions()->chat(
                model: $this->model,
                messages: [
                    ['role' => 'user', 'content' => $prompt]
                ],
                temperature: 0.7,
                maxTokens: 2000
            );

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            throw new \Exception('AI analysis failed: ' . $e->getMessage());
        }
    }

    private function buildPrompt(string $template, array $metadata, string $userPrompt): string
    {
        $prompt = $this->promptTemplates[$template] ?? $this->promptTemplates['analyze_video'];

        $replacements = [
            '{duration}' => $metadata['duration'] ?? 0,
            '{width}' => $metadata['width'] ?? 0,
            '{height}' => $metadata['height'] ?? 0,
            '{codec}' => $metadata['codec'] ?? 'unknown',
            '{fps}' => $metadata['fps'] ?? 0,
            '{user_prompt}' => $userPrompt
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $prompt);
    }

    private function parseResponse(array $response): array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        if (preg_match('/\[.*\]/s', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return [
                    'success' => true,
                    'suggestions' => $json,
                    'raw' => $content
                ];
            }
        }

        return [
            'success' => true,
            'suggestions' => $this->extractSuggestionsFromText($content),
            'raw' => $content
        ];
    }

    private function extractSuggestionsFromText(string $content): array
    {
        $suggestions = [];
        $lines = explode("\n", $content);
        $currentSuggestion = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^(\d+)\.\s*(.+)/', $line, $matches)) {
                if ($currentSuggestion) {
                    $suggestions[] = $currentSuggestion;
                }
                $currentSuggestion = [
                    'type' => 'general',
                    'action' => $matches[2],
                    'reason' => '',
                    'params' => []
                ];
            } elseif ($currentSuggestion && !empty($line)) {
                $currentSuggestion['reason'] .= ' ' . $line;
            }
        }

        if ($currentSuggestion) {
            $suggestions[] = $currentSuggestion;
        }

        return $suggestions;
    }

    public function cacheResponse(string $key, array $data): bool
    {
        $cacheDir = __DIR__ . '/../storage/ai-cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir . '/' . md5($key) . '.json';
        return file_put_contents($file, json_encode($data)) !== false;
    }

    public function getCachedResponse(string $key): ?array
    {
        $file = __DIR__ . '/../storage/ai-cache/' . md5($key) . '.json';
        if (!file_exists($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        return $data ?: null;
    }
}
