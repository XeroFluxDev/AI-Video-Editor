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

    public function removeAudio(string $input, string $output): bool
    {
        $cmd = sprintf(
            '%s -i %s -an -c:v copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function replaceAudio(string $videoInput, string $audioInput, string $output): bool
    {
        $cmd = sprintf(
            '%s -i %s -i %s -c:v copy -c:a aac -map 0:v:0 -map 1:a:0 %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($videoInput),
            escapeshellarg($audioInput),
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function adjustVolume(string $input, string $output, float $volume): bool
    {
        $cmd = sprintf(
            '%s -i %s -af "volume=%.2f" -c:v copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            $volume,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function normalizeAudio(string $input, string $output): bool
    {
        $cmd = sprintf(
            '%s -i %s -af "loudnorm=I=-16:TP=-1.5:LRA=11" -c:v copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function removeSilence(string $input, string $output, float $threshold = -50, float $duration = 0.5): bool
    {
        $cmd = sprintf(
            '%s -i %s -af "silenceremove=stop_periods=-1:stop_duration=%.2f:stop_threshold=%ddB" -c:v copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            $duration,
            (int)$threshold,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function extractAudioWaveform(string $input): ?array
    {
        $cmd = sprintf(
            '%s -i %s -filter_complex "aformat=channel_layouts=mono,compand,showwavespic=s=1920x200" -frames:v 1 -f image2pipe -vcodec png - 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return ['data' => base64_encode(implode('', $output))];
        }

        return null;
    }

    public function addTextOverlay(string $input, string $output, string $text, array $options = []): bool
    {
        $x = $options['x'] ?? '(w-text_w)/2';
        $y = $options['y'] ?? 'h-th-50';
        $fontSize = $options['fontSize'] ?? 24;
        $fontColor = $options['fontColor'] ?? 'white';
        $boxColor = $options['boxColor'] ?? 'black@0.5';

        $text = addslashes($text);

        $cmd = sprintf(
            '%s -i %s -vf "drawtext=text=\'%s\':x=%s:y=%s:fontsize=%d:fontcolor=%s:box=1:boxcolor=%s" -c:a copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            $text,
            $x,
            $y,
            $fontSize,
            $fontColor,
            $boxColor,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function addWatermark(string $videoInput, string $watermarkInput, string $output, array $options = []): bool
    {
        $position = $options['position'] ?? 'bottom-right';
        $margin = $options['margin'] ?? 10;

        $overlayPos = match($position) {
            'top-left' => sprintf('x=%d:y=%d', $margin, $margin),
            'top-right' => sprintf('x=W-w-%d:y=%d', $margin, $margin),
            'bottom-left' => sprintf('x=%d:y=H-h-%d', $margin, $margin),
            'bottom-right' => sprintf('x=W-w-%d:y=H-h-%d', $margin, $margin),
            'center' => 'x=(W-w)/2:y=(H-h)/2',
            default => sprintf('x=W-w-%d:y=H-h-%d', $margin, $margin)
        };

        $cmd = sprintf(
            '%s -i %s -i %s -filter_complex "overlay=%s" -c:a copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($videoInput),
            escapeshellarg($watermarkInput),
            $overlayPos,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function adjustSpeed(string $input, string $output, float $speed): bool
    {
        $videoSpeed = 1 / $speed;
        $audioSpeed = $speed;

        $cmd = sprintf(
            '%s -i %s -filter_complex "[0:v]setpts=%.2f*PTS[v];[0:a]atempo=%.2f[a]" -map "[v]" -map "[a]" %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            $videoSpeed,
            $audioSpeed,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function changeResolution(string $input, string $output, int $width, int $height): bool
    {
        $cmd = sprintf(
            '%s -i %s -vf "scale=%d:%d" -c:a copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            $width,
            $height,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function applyFilters(string $input, string $output, array $filters): bool
    {
        $filterChain = [];

        if (isset($filters['brightness'])) {
            $brightness = $filters['brightness'];
            $filterChain[] = sprintf('eq=brightness=%.2f', $brightness);
        }

        if (isset($filters['contrast'])) {
            $contrast = $filters['contrast'];
            $filterChain[] = sprintf('eq=contrast=%.2f', $contrast);
        }

        if (isset($filters['saturation'])) {
            $saturation = $filters['saturation'];
            $filterChain[] = sprintf('eq=saturation=%.2f', $saturation);
        }

        if (isset($filters['gamma'])) {
            $gamma = $filters['gamma'];
            $filterChain[] = sprintf('eq=gamma=%.2f', $gamma);
        }

        if (empty($filterChain)) {
            return copy($input, $output);
        }

        $filterString = implode(',', $filterChain);

        $cmd = sprintf(
            '%s -i %s -vf "%s" -c:a copy %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($input),
            $filterString,
            escapeshellarg($output)
        );

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function exportVideo(string $input, string $output, array $options = []): bool
    {
        $format = $options['format'] ?? 'mp4';
        $quality = $options['quality'] ?? '1080p';
        $preset = $options['preset'] ?? 'medium';

        $qualityMap = [
            '720p' => ['width' => 1280, 'height' => 720, 'bitrate' => '2500k'],
            '1080p' => ['width' => 1920, 'height' => 1080, 'bitrate' => '5000k'],
            '1440p' => ['width' => 2560, 'height' => 1440, 'bitrate' => '10000k'],
            '4k' => ['width' => 3840, 'height' => 2160, 'bitrate' => '20000k']
        ];

        $q = $qualityMap[$quality] ?? $qualityMap['1080p'];

        if ($format === 'webm') {
            $cmd = sprintf(
                '%s -i %s -vf "scale=%d:%d" -c:v libvpx-vp9 -b:v %s -c:a libopus -b:a 128k -preset %s %s 2>&1',
                escapeshellcmd($this->ffmpegPath),
                escapeshellarg($input),
                $q['width'],
                $q['height'],
                $q['bitrate'],
                $preset,
                escapeshellarg($output)
            );
        } else {
            $cmd = sprintf(
                '%s -i %s -vf "scale=%d:%d" -c:v libx264 -b:v %s -c:a aac -b:a 192k -preset %s %s 2>&1',
                escapeshellcmd($this->ffmpegPath),
                escapeshellarg($input),
                $q['width'],
                $q['height'],
                $q['bitrate'],
                $preset,
                escapeshellarg($output)
            );
        }

        exec($cmd, $result, $returnCode);
        return $returnCode === 0 && file_exists($output);
    }

    public function getExportProgress(string $input): array
    {
        $metadata = $this->getMetadata($input);
        return [
            'duration' => $metadata['duration'] ?? 0,
            'status' => 'processing'
        ];
    }
}
