<?php

namespace App;

class SubtitleService
{
    private string $ffmpegPath;
    private string $tempDir;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/ffmpeg.php';
        $appConfig = require __DIR__ . '/../config/app.php';
        $this->ffmpegPath = $config['ffmpeg_path'];
        $this->tempDir = $appConfig['storage_path'] . '/temp';
    }

    public function extractAudio(string $videoPath): ?string
    {
        $audioPath = $this->tempDir . '/audio_' . uniqid() . '.mp3';

        $cmd = sprintf(
            '%s -i %s -vn -acodec libmp3lame -q:a 2 %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($videoPath),
            escapeshellarg($audioPath)
        );

        exec($cmd, $output, $returnCode);

        return ($returnCode === 0 && file_exists($audioPath)) ? $audioPath : null;
    }

    public function transcribeWithWhisper(string $audioPath, string $apiKey, string $language = 'en'): ?array
    {
        $ch = curl_init('https://api.openai.com/v1/audio/transcriptions');

        $file = new \CURLFile($audioPath, 'audio/mpeg', basename($audioPath));

        $postData = [
            'file' => $file,
            'model' => 'whisper-1',
            'response_format' => 'verbose_json',
            'language' => $language
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 300
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        return json_decode($response, true);
    }

    public function convertToSRT(array $whisperResponse): string
    {
        $srt = '';
        $segments = $whisperResponse['segments'] ?? [];

        foreach ($segments as $index => $segment) {
            $srt .= ($index + 1) . "\n";
            $srt .= $this->formatSRTTime($segment['start']) . ' --> ' . $this->formatSRTTime($segment['end']) . "\n";
            $srt .= trim($segment['text']) . "\n\n";
        }

        return $srt;
    }

    private function formatSRTTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $millis = round(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $secs, $millis);
    }

    public function burnSubtitles(string $videoPath, string $srtPath, string $outputPath, array $style = []): bool
    {
        $fontsize = $style['fontsize'] ?? 24;
        $fontcolor = $style['fontcolor'] ?? 'white';
        $bordercolor = $style['bordercolor'] ?? 'black';
        $borderw = $style['borderw'] ?? 2;

        $subtitlesFilter = sprintf(
            "subtitles=%s:force_style='FontSize=%d,PrimaryColour=&H%s,OutlineColour=&H%s,BorderStyle=1,Outline=%d'",
            str_replace(':', '\\:', $srtPath),
            $fontsize,
            $this->colorToHex($fontcolor),
            $this->colorToHex($bordercolor),
            $borderw
        );

        $cmd = sprintf(
            '%s -i %s -vf %s -c:a copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($videoPath),
            escapeshellarg($subtitlesFilter),
            escapeshellarg($outputPath)
        );

        exec($cmd, $output, $returnCode);

        return $returnCode === 0 && file_exists($outputPath);
    }

    public function embedSubtitles(string $videoPath, string $srtPath, string $outputPath): bool
    {
        $cmd = sprintf(
            '%s -i %s -i %s -c copy -c:s mov_text -metadata:s:s:0 language=eng %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($videoPath),
            escapeshellarg($srtPath),
            escapeshellarg($outputPath)
        );

        exec($cmd, $output, $returnCode);

        return $returnCode === 0 && file_exists($outputPath);
    }

    private function colorToHex(string $color): string
    {
        $colors = [
            'white' => 'FFFFFF',
            'black' => '000000',
            'red' => 'FF0000',
            'green' => '00FF00',
            'blue' => '0000FF',
            'yellow' => 'FFFF00'
        ];

        return $colors[strtolower($color)] ?? 'FFFFFF';
    }

    public function saveSRT(string $content, string $filename): string
    {
        $srtPath = $this->tempDir . '/' . $filename;
        file_put_contents($srtPath, $content);
        return $srtPath;
    }
}
