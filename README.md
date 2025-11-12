# AI Video Editor

A powerful, web-based video editing application with AI-powered features built with PHP and vanilla JavaScript.

## Features

### Core Editing
- **Trim & Cut**: Precise video trimming with Mark In/Mark Out functionality
- **Interactive Timeline**: Visual timeline with draggable playhead
- **Video Player**: HTML5 video player with full controls

### AI Integration
- **AI Assistant**: Get intelligent editing suggestions powered by Claude 3.5 Sonnet
- **Smart Analysis**: AI analyzes your video and suggests improvements
- **Context-Aware**: Ask AI for specific editing advice

### Subtitles
- **Auto-Generation**: Generate subtitles using OpenAI Whisper
- **Multi-Language**: Support for 8 languages (English, Spanish, French, German, Italian, Portuguese, Chinese, Japanese)
- **Styling Options**: Customize font size and color
- **Hard/Soft Subtitles**: Burn in or embed as separate track

### Audio Operations
- **Remove/Replace Audio**: Complete audio track management
- **Volume Control**: Precise volume adjustment (0-200%)
- **Audio Normalization**: Automatic loudness normalization
- **Silence Removal**: Detect and remove silent sections
- **Waveform Display**: Visual audio waveform using Wavesurfer.js

### Effects & Filters
- **Text Overlay**: Add custom text with position and styling
- **Watermarks**: Add image watermarks with 5 position presets
- **Speed Control**: Adjust video speed (0.5x - 4x)
- **Resolution**: Change video resolution (720p, 1080p, 1440p, 4K)
- **Color Filters**: Adjust brightness, contrast, and saturation

### Export
- **Multi-Format**: Export as MP4 (H.264) or WebM (VP9)
- **Quality Presets**: 720p, 1080p, 1440p, 4K
- **Encoding Options**: Balance between speed and file size
- **Progress Tracking**: Visual progress bar during export

### UI/UX
- **Dark Theme**: Modern dark interface
- **Keyboard Shortcuts**:
  - `Space` - Play/Pause
  - `Arrow Left/Right` - Seek backward/forward 5 seconds
  - `M` - Toggle mute
  - `F` - Toggle fullscreen
- **Notifications**: Clean, non-intrusive notifications

## Requirements

- PHP 8.1 or higher
- FFmpeg 4.x or higher
- Composer
- Web server (Apache/Nginx)
- OpenRouter API key (for AI features)
- OpenAI API key (for subtitles)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/XeroFluxDev/AI-Video-Editor.git
cd AI-Video-Editor
```

### 2. Install PHP Dependencies

```bash
cd app
composer install
```

### 3. Install FFmpeg

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install ffmpeg
```

**macOS:**
```bash
brew install ffmpeg
```

**Windows:**
Download from [ffmpeg.org](https://ffmpeg.org/download.html)

### 4. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and add your API keys:
```env
OPENROUTER_API_KEY=your_openrouter_api_key_here
OPENAI_API_KEY=your_openai_api_key_here
```

### 5. Create Required Directories

```bash
chmod +x setup.sh
./setup.sh
```

Or manually:
```bash
mkdir -p storage/{uploads,temp,exports,ai-cache}
chmod -R 755 storage
```

### 6. Configure Web Server

**Apache (.htaccess already included):**
Point document root to `app/public`

**Nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/AI-Video-Editor/app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Set Permissions

```bash
chmod -R 755 app/public
chmod -R 775 storage
chown -R www-data:www-data storage
```

### 8. Access the Application

Navigate to `http://your-domain.com` or `http://localhost:8000`

For development:
```bash
cd app/public
php -S localhost:8000
```

## Usage

### Uploading a Video

1. Click "Select Video" or drag and drop a video file
2. Supported formats: MP4, AVI, MOV, WebM, MKV
3. Maximum file size: 500MB (configurable)

### Basic Editing

1. Use the timeline to navigate your video
2. Click "Mark In" to set start point
3. Click "Mark Out" to set end point
4. Click "Apply Trim" to trim the video

### AI Suggestions

1. Type your request in the AI Assistant panel
2. Examples:
   - "Make this video more engaging"
   - "Remove boring parts"
   - "Suggest where to add transitions"
3. Click "Get AI Suggestions"
4. Review suggestions and click "Apply" on any suggestion

### Generating Subtitles

1. Select language from the dropdown
2. Click "Generate Subtitles"
3. Wait for transcription (uses OpenAI Whisper)
4. Customize font size and color
5. Choose "Burn In" (permanent) or "Embed" (toggleable)

### Audio Editing

1. Use volume slider to adjust audio (0-200%)
2. Click "Remove Audio" to strip audio track
3. Click "Replace Audio" to upload new audio
4. Click "Normalize" for automatic level adjustment
5. Click "Remove Silence" to cut silent sections

### Adding Effects

1. **Text Overlay**: Enter text, choose position/size/color, click "Add Text"
2. **Watermark**: Select position, click "Add Watermark", upload image
3. **Speed**: Drag slider, click "Apply Speed"
4. **Resolution**: Select preset, click "Apply"
5. **Filters**: Adjust sliders, click "Apply" (or "Reset" to clear)

### Exporting

1. Click "Export Video" button
2. Choose format (MP4 or WebM)
3. Select quality (720p, 1080p, 1440p, 4K)
4. Choose encoding speed preset
5. Click "Start Export"
6. Wait for completion
7. Click "Download Video"

## API Endpoints

All API endpoints are located in `app/api/`:

- `POST /api/video/upload.php` - Upload video
- `POST /api/video/process.php` - Trim/cut operations
- `POST /api/video/metadata.php` - Get video metadata
- `POST /api/ai/analyze.php` - AI analysis
- `POST /api/subtitles/generate.php` - Generate/burn/embed subtitles
- `POST /api/audio/process.php` - Audio operations
- `POST /api/effects/process.php` - Effects and filters
- `POST /api/export/process.php` - Export video

## Project Structure

```
AI-Video-Editor/
├── app/
│   ├── api/              # API endpoints
│   │   ├── video/        # Video processing
│   │   ├── ai/           # AI integration
│   │   ├── subtitles/    # Subtitle generation
│   │   ├── audio/        # Audio operations
│   │   ├── effects/      # Effects and filters
│   │   └── export/       # Export functionality
│   ├── config/           # Configuration files
│   ├── public/           # Public web files
│   │   ├── assets/       # CSS, JS, images
│   │   │   ├── css/      # Stylesheets
│   │   │   └── js/       # JavaScript modules
│   │   └── index.php     # Main application
│   ├── src/              # PHP classes
│   │   ├── FFmpegService.php
│   │   ├── AIService.php
│   │   └── SubtitleService.php
│   ├── storage/          # File storage
│   │   ├── uploads/      # Uploaded videos
│   │   ├── temp/         # Temporary files
│   │   ├── exports/      # Exported videos
│   │   └── ai-cache/     # AI response cache
│   ├── composer.json     # PHP dependencies
│   └── openrouter-client.php
├── docs/                 # Documentation
│   ├── PROGRESS.md       # Implementation progress
│   ├── IMPLEMENTATION-PLAN.md
│   └── README.md
├── .env.example          # Environment template
└── README.md             # This file
```

## Configuration

### FFmpeg Settings

Edit `app/config/ffmpeg.php`:
```php
return [
    'ffmpeg_path' => '/usr/bin/ffmpeg',
    'ffprobe_path' => '/usr/bin/ffprobe',
    'timeout' => 3600
];
```

### Application Settings

Edit `app/config/app.php`:
```php
return [
    'upload_max_size' => 524288000,  // 500MB
    'allowed_types' => ['video/mp4', 'video/avi', ...],
    'storage_path' => __DIR__ . '/../storage'
];
```

## Troubleshooting

### FFmpeg Not Found

```bash
which ffmpeg
which ffprobe
```

Update paths in `app/config/ffmpeg.php`

### Permission Errors

```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Upload Fails

Check `php.ini`:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 600
memory_limit = 512M
```

### API Key Errors

Verify your API keys in `.env`:
- OpenRouter: https://openrouter.ai/keys
- OpenAI: https://platform.openai.com/api-keys

## Development

### Running Locally

```bash
cd app/public
php -S localhost:8000
```

### Code Style

- Simple, direct code
- Minimal comments
- No over-engineering
- Essential features only

### Adding New Features

1. Create service class in `app/src/`
2. Create API endpoint in `app/api/`
3. Create JS module in `app/public/assets/js/`
4. Update `index.php` with UI
5. Initialize module in `editor.js`

## Security

- All user inputs are sanitized
- File uploads are validated
- API endpoints check request methods
- FFmpeg commands use `escapeshellarg()`
- XSS protection in UI
- CSRF tokens recommended for production

## Performance

- File-based caching for AI responses
- Efficient FFmpeg commands
- Minimal JavaScript libraries
- Optimized encoding presets

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

MIT License - See LICENSE file for details

## Credits

Built with:
- PHP & FFmpeg for video processing
- OpenRouter API (Claude 3.5 Sonnet) for AI features
- OpenAI Whisper for subtitle generation
- Video.js for video playback
- Wavesurfer.js for audio visualization
- Tailwind CSS for styling
- Font Awesome for icons

## Support

For issues and questions:
- GitHub Issues: https://github.com/XeroFluxDev/AI-Video-Editor/issues
- Documentation: See `docs/` folder

## Roadmap

Future enhancements:
- Real-time collaboration
- More AI features
- Advanced color grading
- Multi-track editing
- Cloud storage integration
- Video templates

---

**Version:** 1.0.0
**Status:** Production Ready
**Last Updated:** 2025-11-12
