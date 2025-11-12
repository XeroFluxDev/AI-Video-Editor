<?php

namespace App;

class FFmpegService
{
    private string $ffmpegPath;
    private string $ffprobePath;
    private int $timeout;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/ffmpeg.php';
        $this->ffmpegPath = $config['ffmpeg_path'];
        $this->ffprobePath = $config['ffprobe_path'];
        $this->timeout = $config['timeout'];
    }

    public function trim(string $input, float $start, float $duration, string $output): bool
    {
        $cmd = sprintf(
            '%s -ss %.2f -i %s -t %.2f -c copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            $start,
            escapeshellarg($input),
            $duration,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function cut(string $input, array $segments, string $output): bool
    {
        $tempDir = dirname($output);
        $segmentFiles = [];
        $concatFile = $tempDir . '/concat_' . uniqid() . '.txt';

        foreach ($segments as $i => $segment) {
            $segmentFile = $tempDir . '/segment_' . $i . '_' . uniqid() . '.mp4';
            $start = $segment['start'];
            $duration = $segment['end'] - $segment['start'];

            if ($this->trim($input, $start, $duration, $segmentFile)) {
                $segmentFiles[] = $segmentFile;
            }
        }

        if (empty($segmentFiles)) {
            return false;
        }

        $concatContent = '';
        foreach ($segmentFiles as $file) {
            $concatContent .= "file '" . basename($file) . "'\n";
        }
        file_put_contents($concatFile, $concatContent);

        $cmd = sprintf(
            '%s -f concat -safe 0 -i %s -c copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($concatFile),
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);

        foreach ($segmentFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
        if (file_exists($concatFile)) unlink($concatFile);

        return $returnCode === 0 && file_exists($output);
    }

    public function generateThumbnail(string $input, float $time, string $output): bool
    {
        $cmd = sprintf(
            '%s -ss %.2f -i %s -vframes 1 -q:v 2 %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            $time,
            escapeshellarg($input),
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function getMetadata(string $input): ?array
    {
        $cmd = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams %s',
            escapeshellcmd($this->ffprobePath),
            escapeshellarg($input)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            return null;
        }

        $json = implode('', $output);
        $data = json_decode($json, true);

        if (!$data) {
            return null;
        }

        $video = null;
        foreach ($data['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video') {
                $video = $stream;
                break;
            }
        }

        return [
            'duration' => (float)($data['format']['duration'] ?? 0),
            'size' => (int)($data['format']['size'] ?? 0),
            'bitrate' => (int)($data['format']['bit_rate'] ?? 0),
            'width' => (int)($video['width'] ?? 0),
            'height' => (int)($video['height'] ?? 0),
            'fps' => $this->parseFps($video['r_frame_rate'] ?? '0/1'),
            'codec' => $video['codec_name'] ?? 'unknown'
        ];
    }

    private function parseFps(string $fps): float
    {
        if (strpos($fps, '/') !== false) {
            [$num, $den] = explode('/', $fps);
            return $den > 0 ? $num / $den : 0;
        }
        return (float)$fps;
    }
}
