# AI Video Editor - Simple Implementation Plan

## Overview
Build a web-based video editor with AI-powered suggestions. Keep it simple, avoid over-engineering.

## Core Features Only
1. Upload and play videos
2. Basic editing (trim, cut, split)
3. AI suggestions via OpenRouter
4. Subtitle generation via Whisper
5. Audio manipulation
6. Text overlays and filters
7. Export in multiple formats

## Architecture

### Backend (PHP 8.1+)
```
/public/index.php          - Main interface
/api/video/upload.php      - Handle uploads
/api/video/process.php     - Process edits
/api/video/export.php      - Export video
/api/ai/analyze.php        - AI suggestions
/api/subtitles/generate.php - Generate subs
/src/FFmpegService.php     - FFmpeg wrapper
/config/                   - Configuration files
/storage/                  - File storage
```

### Frontend (Vanilla JS)
```
/public/assets/js/
  editor.js              - Main editor logic
  timeline.js            - Timeline component
  ai.js                  - AI integration
/public/assets/css/
  editor.css             - Custom styles
```

## Key Implementation Details

### 1. File Upload (Simple)
```php
// api/video/upload.php
$upload_dir = '../storage/uploads/';
$video_id = uniqid('vid_');
move_uploaded_file($_FILES['video']['tmp_name'], $upload_dir . $video_id . '.mp4');
// Extract metadata with FFprobe
// Return video ID + metadata JSON
```

### 2. Video Processing (Direct FFmpeg)
```php
// src/FFmpegService.php - Keep it simple
function trim($input, $start, $end, $output) {
    $cmd = "ffmpeg -ss $start -i $input -t " . ($end - $start) . " -c copy $output";
    exec($cmd);
}
```

### 3. AI Integration (Use provided client)
```php
// api/ai/analyze.php
require '../openrouter-client.php';
$client = new OpenRouterClient\OpenRouterClient($apiKey);
$response = $client->completions()->chat(
    model: 'anthropic/claude-3.5-sonnet',
    messages: [['role' => 'user', 'content' => $prompt]]
);
// Parse and return suggestions
```

### 4. Timeline (Simple Canvas/Divs)
```javascript
// No complex libraries - just divs with CSS
const timeline = {
    duration: 120,
    currentTime: 0,
    clips: [],
    render() { /* Draw clips as positioned divs */ }
};
```

### 5. No Over-Engineering
- **No MVC framework** - Direct PHP files
- **No build tools** - Plain JS + Tailwind CDN
- **No ORM** - Direct file system
- **No WebSockets** - Simple polling for progress
- **No complex state management** - Plain objects

## File Structure (Minimal)
```
/
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/editor.css
│       └── js/
│           ├── editor.js
│           ├── timeline.js
│           └── ai.js
├── api/
│   ├── video/
│   │   ├── upload.php
│   │   ├── process.php
│   │   └── export.php
│   ├── ai/
│   │   └── analyze.php
│   └── subtitles/
│       └── generate.php
├── src/
│   └── FFmpegService.php
├── config/
│   ├── app.php
│   └── ffmpeg.php
├── storage/
│   ├── uploads/
│   ├── temp/
│   └── exports/
├── openrouter-client.php
├── composer.json
├── .env.example
└── README.md
```

## Essential Dependencies Only
```json
{
  "require": {
    "php-ffmpeg/php-ffmpeg": "^1.0"
  }
}
```

## Frontend Libraries (CDN)
- Tailwind CSS
- Video.js
- Wavesurfer.js
- Font Awesome

## Development Approach

### Phase 1: Foundation
1. Create folder structure
2. Setup composer
3. Build upload form
4. Test FFmpeg
5. Create basic video player

### Phase 2: Core Editing
1. Timeline component
2. Trim function
3. Cut function
4. Preview updates

### Phase 3: AI Integration
1. Connect OpenRouter
2. Build prompt system
3. Display suggestions
4. Apply suggestions

### Phase 4-8: See PROGRESS.md

## Code Style

### PHP
```php
// Minimal comments, clear code
function trimVideo($input, $start, $end) {
    $output = tempnam(sys_get_temp_dir(), 'trim_') . '.mp4';
    $duration = $end - $start;
    exec("ffmpeg -ss $start -i $input -t $duration -c copy $output");
    return $output;
}
```

### JavaScript
```javascript
// Direct, no abstractions
function playVideo() {
    const video = document.getElementById('video-player');
    video.play();
    updateTimeline();
}
```

## Testing Strategy
1. Manual testing in browser
2. Test each edit operation individually
3. Test with real videos
4. Check error handling
5. Browser compatibility (Chrome, Firefox, Safari)

## Deployment
- Standard PHP hosting
- Ensure FFmpeg is installed
- Set permissions on storage folders
- Configure .env file
- Upload files via FTP/Git

## Success Metrics
- ✅ Upload video < 5 seconds (100MB file)
- ✅ Basic edit operations work
- ✅ AI suggestions are helpful
- ✅ Subtitle generation works
- ✅ Export completes successfully
- ✅ No console errors
- ✅ Intuitive UI

---

**Remember:** Simple, direct, functional. No premature optimization.
