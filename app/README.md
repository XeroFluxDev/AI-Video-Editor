# AI Video Editor - Application

**Status:** Phase 1 Complete ✓
**Current Features:** Video upload, playback, basic interface

## Quick Start

### 1. Install Dependencies
```bash
cd app
composer install
```

### 2. Install FFmpeg
```bash
# Ubuntu/Debian
sudo apt-get install ffmpeg

# macOS
brew install ffmpeg

# Verify installation
ffmpeg -version
```

### 3. Set Permissions
```bash
chmod -R 775 storage/
```

### 4. Configure Environment
```bash
cp .env.example .env
# Edit .env with your API keys
```

### 5. Start Server
```bash
# Using PHP built-in server
cd public
php -S localhost:8000

# Or configure Apache/Nginx to point to /app/public
```

### 6. Access Application
Open browser: `http://localhost:8000`

## Folder Structure

```
app/
├── public/              # Web root
│   ├── index.php        # Main interface
│   └── assets/          # CSS, JS
├── api/                 # API endpoints
│   ├── video/           # Video operations
│   ├── ai/              # AI integration (Phase 3)
│   └── subtitles/       # Subtitles (Phase 4)
├── src/                 # PHP classes
├── config/              # Configuration
├── storage/             # File storage
│   ├── uploads/         # Uploaded videos
│   ├── temp/            # Processing temp files
│   └── exports/         # Exported videos
├── composer.json        # Dependencies
└── .env.example         # Environment template
```

## Phase 1 Features ✓

- ✅ Video upload (drag & drop)
- ✅ Video playback (Video.js)
- ✅ Basic controls (play/pause)
- ✅ Video information display
- ✅ Dark theme interface
- ✅ Custom notifications
- ✅ Responsive design

## Upcoming Phases

- **Phase 2:** Basic editing (trim, cut, timeline)
- **Phase 3:** AI integration (OpenRouter)
- **Phase 4:** Subtitle generation (Whisper)
- **Phase 5:** Audio operations
- **Phase 6:** Effects & filters
- **Phase 7:** Export & polish
- **Phase 8:** Testing & deployment

## Supported Formats

- MP4
- WebM
- MOV
- AVI

## Requirements

- PHP 8.1+
- FFmpeg 4.0+
- 512MB+ RAM
- Apache/Nginx (optional)

## Troubleshooting

### Upload fails
- Check storage/ permissions: `chmod -R 775 storage/`
- Increase PHP limits in .htaccess or php.ini

### Video won't play
- Ensure video is in supported format
- Check browser console for errors
- Verify Video.js CDN is accessible

### FFmpeg not found
- Install FFmpeg: `sudo apt-get install ffmpeg`
- Update path in config/ffmpeg.php

## Development

See `/docs/PROGRESS.md` for implementation progress and next steps.

## API Documentation

### POST /api/video/upload.php
Upload video file
- **Input:** multipart/form-data with 'video' field
- **Output:** JSON with video_id, filename, size

### GET /api/video/metadata.php?filename=xxx
Get video metadata
- **Input:** filename parameter
- **Output:** JSON with file info

---

**Last Updated:** 2025-11-12
**Phase:** 1 (Foundation) Complete
