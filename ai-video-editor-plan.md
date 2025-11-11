# AI-Powered Video Editing Tool - Comprehensive Development Plan

## Executive Summary

A web-based video editing platform similar to CapCut that leverages AI for intelligent editing suggestions. The system will be built using native PHP backend with vanilla JavaScript frontend, utilizing modern browser APIs (WebCodecs) for client-side processing and FFmpeg for server-side heavy operations.

## Core Architecture

### Technology Stack

**Backend**
- Native PHP 8.1+ (no frameworks)
- FFmpeg for server-side video processing
- PHP-FFMpeg wrapper for FFmpeg integration
- OpenAI Whisper API for subtitle generation
- Composer for dependency management

**Frontend**
- Vanilla JavaScript (ES6+)
- WebCodecs API for browser-based video processing
- Tailwind CSS for styling (dark theme default)
- Video.js for video playback
- Wavesurfer.js for audio waveform visualization
- Font Awesome for icons

**AI Services**
- OpenAI API for intelligent editing suggestions
- OpenAI Whisper API for speech-to-text and subtitle generation
- AssemblyAI as alternative for subtitle generation

## System Components

### 1. Frontend Video Editor

#### Core Libraries to Use

**Video Processing**
- **Etro.js** - TypeScript framework for programmatic video editing in browser
  - GitHub: https://github.com/etro-js/etro
  - Features: Layer compositing, GLSL effects, text/video/audio layers
  - Use for: Real-time preview and basic edits

**Video Player & Timeline**
- **Video.js** - HTML5 video player
  - Use for: Main video playback interface
  - Customizable controls and plugins

- **videojs-wavesurfer** - Waveform plugin for Video.js
  - GitHub: https://github.com/collab-project/videojs-wavesurfer
  - Use for: Audio timeline visualization
  - Features: Navigable waveform, region marking, real-time updates

**UI Components**
- Custom-built timeline editor with drag-and-drop
- Frame-accurate seeking using WebCodecs
- Multi-track support (video, audio, subtitles, effects)

#### Browser Features

**WebCodecs API Integration**
- Video decoding: `VideoDecoder` for frame extraction
- Video encoding: `VideoEncoder` for export
- Audio processing: `AudioDecoder` and `AudioEncoder`
- Hardware acceleration for performance
- Browser support: Chrome, Edge, Firefox 110+, Safari 16.4+

**Key Capabilities**
- Frame-by-frame video manipulation
- Real-time effect application
- Local processing (no upload for preview)
- Fast seeking and scrubbing
- Export high-quality videos

### 2. Backend Video Processing

#### PHP Video Processing Setup

**FFmpeg Installation**
- Required: FFmpeg binary with libass support (for subtitle burning)
- FFprobe for video metadata extraction
- Installation path: System-dependent (configurable)

**PHP-FFMpeg Wrapper**
```
composer require php-ffmpeg/php-ffmpeg
```

**Core Features**
- Video format conversion
- Video cutting and trimming
- Resolution and bitrate adjustment
- Subtitle embedding (soft and hard)
- Audio extraction and replacement
- Thumbnail generation
- Video concatenation

#### Video Processing Endpoints

**1. Upload & Analysis** (`/api/video/upload.php`)
- Accept video upload (chunk upload for large files)
- Extract metadata using FFprobe
- Generate video ID and temporary working directory
- Return video info JSON

**2. AI Analysis Request** (`/api/ai/analyze.php`)
- Accept video segment or full video
- Send to OpenAI API with editing prompt
- Parse AI response into structured JSON
- Cache request/response in diagnostics directory

**3. Process Edits** (`/api/video/process.php`)
- Receive edit instructions JSON
- Execute FFmpeg commands based on instructions
- Return processed video or preview
- Handle background processing for long operations

**4. Export Final Video** (`/api/video/export.php`)
- Compile all edits into final video
- Apply encoding settings
- Return download link

### 3. AI Integration Layer

#### OpenAI Integration

**Request Structure**
```json
{
  "model": "gpt-4",
  "messages": [
    {
      "role": "system",
      "content": "You are a professional video editor. Analyze the video and provide editing suggestions in JSON format."
    },
    {
      "role": "user",
      "content": "Analyze this video and suggest edits. Video duration: 120s, resolution: 1920x1080. User request: 'Add subtitles and remove silent parts'"
    }
  ],
  "response_format": { "type": "json_object" }
}
```

**Expected Response Format**
```json
{
  "edits": [
    {
      "type": "add_subtitles",
      "start_time": 0,
      "end_time": 120,
      "language": "en",
      "style": {
        "font": "Arial",
        "size": 24,
        "color": "white",
        "background": "black",
        "position": "bottom"
      }
    },
    {
      "type": "remove_silence",
      "threshold_db": -40,
      "min_duration": 1.0
    },
    {
      "type": "cut_segment",
      "start_time": 45.5,
      "end_time": 52.3,
      "reason": "silent_part"
    }
  ],
  "summary": "Added subtitles for entire video and removed 3 silent segments totaling 12 seconds"
}
```

#### Whisper API for Subtitles

**PHP Implementation**
```php
// Using openai-php/client
use OpenAI;

$client = OpenAI::client($apiKey);

$response = $client->audio()->transcribe([
    'model' => 'whisper-1',
    'file' => fopen('video_audio.mp3', 'r'),
    'response_format' => 'srt',
    'language' => 'en',
    'timestamp_granularities' => ['word', 'segment']
]);

file_put_contents('subtitles.srt', $response->text);
```

**Alternative: Local Whisper.php**
```
composer require codewithkyrian/whisper.php
```
- Runs locally without API costs
- Requires PHP 8.1+ with FFI extension
- Good for privacy-sensitive projects

### 4. Edit Operation Implementation

#### Supported Edit Types

**1. Subtitle Operations**

**Add Subtitles (Soft)**
```bash
ffmpeg -i input.mp4 -i subtitles.srt -c copy -c:s mov_text -metadata:s:s:0 language=eng output.mp4
```

**Burn Subtitles (Hard)**
```bash
ffmpeg -i input.mp4 -vf "subtitles=subtitles.srt:force_style='FontName=Arial,FontSize=24,PrimaryColour=&HFFFFFF&,OutlineColour=&H000000&'" -c:a copy output.mp4
```

**Generate Subtitles via AI**
1. Extract audio from video
2. Send to Whisper API
3. Receive SRT/VTT file
4. Apply to video (soft or hard)

**2. Audio Operations**

**Remove Audio**
```bash
ffmpeg -i input.mp4 -c:v copy -an output.mp4
```

**Replace Audio**
```bash
ffmpeg -i input.mp4 -i new_audio.mp3 -c:v copy -map 0:v:0 -map 1:a:0 -shortest output.mp4
```

**Sync Audio (Delay)**
```bash
ffmpeg -i input.mp4 -itsoffset 2.5 -i input.mp4 -map 0:v -map 1:a -c copy output.mp4
```

**Remove Silence**
```bash
ffmpeg -i input.mp4 -af "silenceremove=stop_periods=-1:stop_duration=1:stop_threshold=-40dB" -c:v copy output.mp4
```

**3. Video Trimming & Cutting**

**Trim Video**
```bash
ffmpeg -ss 00:00:10 -i input.mp4 -t 00:00:30 -c copy output.mp4
```

**Cut and Concatenate**
1. Create segments
2. Generate concat file
3. Merge segments
```bash
ffmpeg -f concat -safe 0 -i segments.txt -c copy output.mp4
```

**4. Visual Effects**

**Add Text Overlay**
```bash
ffmpeg -i input.mp4 -vf "drawtext=text='Sample Text':fontcolor=white:fontsize=24:x=(w-text_w)/2:y=(h-text_h)/2" -c:a copy output.mp4
```

**Add Watermark**
```bash
ffmpeg -i input.mp4 -i logo.png -filter_complex "overlay=10:10" -c:a copy output.mp4
```

**Adjust Speed**
```bash
# 2x speed
ffmpeg -i input.mp4 -filter:v "setpts=0.5*PTS" -filter:a "atempo=2.0" output.mp4
```

**5. Quality Adjustments**

**Change Resolution**
```bash
ffmpeg -i input.mp4 -vf scale=1280:720 -c:a copy output.mp4
```

**Adjust Brightness/Contrast**
```bash
ffmpeg -i input.mp4 -vf "eq=brightness=0.1:contrast=1.2" -c:a copy output.mp4
```

### 5. File Structure

```
/project-root/
  /public/                      # Web-accessible files
    index.php                   # Main editor interface
    /assets/
      /css/
        tailwind.min.css
        editor.css              # Custom styles
      /js/
        /lib/
          video.js
          wavesurfer.js
          etro.js
        editor.js               # Main editor logic
        timeline.js             # Timeline component
        ai-integration.js       # AI API calls
        video-processor.js      # WebCodecs processing
      /images/
      /fonts/

  /api/                         # Backend API endpoints
    /video/
      upload.php                # Video upload handler
      process.php               # Process edit operations
      export.php                # Export final video
      metadata.php              # Get video info
    /ai/
      analyze.php               # AI analysis endpoint
      suggest.php               # Get AI suggestions
    /subtitles/
      generate.php              # Generate subtitles via Whisper
      upload.php                # Upload SRT file

  /src/                         # PHP source code
    /Controllers/
      VideoController.php
      AIController.php
      SubtitleController.php
    /Services/
      FFmpegService.php
      OpenAIService.php
      WhisperService.php
      VideoProcessor.php
    /Models/
      Video.php
      EditOperation.php
      Subtitle.php
    /Utilities/
      FileUploadHandler.php
      JsonValidator.php
      CacheManager.php

  /config/
    app.php                     # App configuration
    ai.php                      # AI API keys
    ffmpeg.php                  # FFmpeg paths

  /storage/                     # File storage
    /uploads/                   # Uploaded videos
    /temp/                      # Temporary processing
    /exports/                   # Exported videos
    /thumbnails/                # Video thumbnails

  /diagnostics/                 # Debug & cache
    /api-cache/                 # AI request/response cache
      2025-01-15_14-30-22_analyze_POST.json
    /error-logs/
      php-errors-2025-01-15.json

  /templates/                   # PHP templates
    /layouts/
      main.php
    /components/
      video-player.php
      timeline.php
      controls.php
    /pages/
      editor.php
      library.php

  /tests/                       # Test files
    VideoProcessingTest.php
    AIIntegrationTest.php

  /docs/                        # Documentation
    api-documentation.md
    edit-operations.md

  /vendor/                      # Composer dependencies

  composer.json
  .env.example
  .htaccess                     # URL rewriting
```

## Implementation Phases

### Phase 1: Foundation Setup (Week 1-2)

**Backend Setup**
1. Initialize project structure
2. Configure Composer and install dependencies
   - php-ffmpeg/php-ffmpeg
   - openai-php/client
3. Setup FFmpeg integration and test basic operations
4. Create database schema for video metadata
5. Implement file upload system with chunking

**Frontend Setup**
1. Setup Tailwind CSS with dark theme
2. Install Video.js and Wavesurfer.js
3. Create basic HTML structure (Header, Content, Footer)
4. Implement video player component
5. Create timeline visualization

**Testing**
- Test FFmpeg commands via CLI
- Test file upload functionality
- Test video playback in browser

### Phase 2: Core Video Operations (Week 3-4)

**Backend Development**
1. Create VideoController and FFmpegService
2. Implement core edit operations
   - Trim/cut video
   - Add/remove audio
   - Generate thumbnails
   - Extract metadata
3. Create API endpoints for each operation
4. Test each operation via CLI/curl

**Frontend Development**
1. Build timeline editor with drag-and-drop
2. Implement video preview with WebCodecs
3. Create controls for basic edits
4. Add real-time preview capability

**Testing**
- Backend: Test each FFmpeg operation independently
- Frontend: Test UI interactions without backend
- Integration: Test complete flows

### Phase 3: AI Integration (Week 5-6)

**AI Services**
1. Setup OpenAI API integration
2. Create structured JSON request/response formats
3. Implement request/response caching in diagnostics
4. Create AIController and OpenAIService
5. Build prompt engineering for video editing suggestions

**Whisper Integration**
1. Setup Whisper API integration
2. Create audio extraction pipeline
3. Implement subtitle generation
4. Create SRT/VTT parser

**Frontend AI Features**
1. Create AI suggestion panel
2. Implement "Ask AI" feature
3. Show AI-suggested edits visually
4. Allow user to accept/reject suggestions

**Testing**
- Test AI responses with various prompts
- Verify JSON structure compliance
- Test subtitle generation accuracy

### Phase 4: Subtitle System (Week 7)

**Backend**
1. Implement soft subtitle embedding
2. Implement hard subtitle burning
3. Create subtitle styling system
4. Build subtitle synchronization tools

**Frontend**
1. Create subtitle editor interface
2. Implement subtitle timeline track
3. Add subtitle styling controls
4. Build subtitle preview

**Testing**
- Test subtitle generation from speech
- Test manual subtitle editing
- Test subtitle styling options

### Phase 5: Advanced Features (Week 8-9)

**Video Effects**
1. Text overlay system
2. Watermark support
3. Speed adjustment (slow-mo, timelapse)
4. Filters and color correction
5. Transitions between clips

**Audio Processing**
1. Silence removal
2. Audio normalization
3. Volume adjustment
4. Audio fade in/out

**Multi-clip Editing**
1. Video concatenation
2. Picture-in-picture
3. Split screen
4. Multi-track audio

**Testing**
- Test each effect independently
- Test combinations of effects
- Test export with multiple operations

### Phase 6: Export & Optimization (Week 10)

**Export System**
1. Multiple format support (MP4, WebM, MOV)
2. Quality presets (720p, 1080p, 4K)
3. Bitrate optimization
4. Background processing queue
5. Progress tracking

**Performance Optimization**
1. Implement video proxy for large files
2. Optimize WebCodecs processing
3. Add caching for processed segments
4. Implement lazy loading

**Testing**
- Test export with various settings
- Test with large files (>1GB)
- Measure processing times

### Phase 7: UI/UX Polish (Week 11)

**Design Implementation**
1. Refine dark theme styling
2. Add hover effects and transitions
3. Implement skeleton loaders
4. Create custom notifications (no alerts)
5. Add optimistic UI updates

**User Experience**
1. Keyboard shortcuts
2. Undo/redo functionality
3. Auto-save drafts
4. Tutorial/onboarding
5. Help documentation

**Testing**
- Playwright tests for complete user flows
- Test all interactive elements
- Verify responsive design

### Phase 8: Testing & Deployment (Week 12)

**Comprehensive Testing**
1. Backend verification
   - All functions error-free
   - Correct outputs for all inputs
   - Clean error logs
   - Valid database operations
2. Frontend verification
   - No console errors
   - Network requests succeed
   - Visual elements render correctly
   - User interactions work properly

**Deployment**
1. Setup production environment
2. Configure FTP deployment (if using beta.liquidloop.net)
3. Optimize for shared hosting (exec() disabled workaround)
4. Create deployment documentation

## API Endpoint Documentation

### Video Management

**POST /api/video/upload.php**
- Upload video file (multipart/form-data)
- Supports chunked uploads
- Returns: video_id, metadata

**GET /api/video/metadata.php?id={video_id}**
- Get video information
- Returns: duration, resolution, format, size, framerate

**POST /api/video/process.php**
- Process edit operations
- Body: JSON with edit instructions
- Returns: processed video URL or job ID

**POST /api/video/export.php**
- Export final video
- Body: format, quality, video_id
- Returns: download URL

### AI Integration

**POST /api/ai/analyze.php**
- Send video for AI analysis
- Body: video_id, prompt, segment (optional)
- Returns: AI suggestions in JSON format

**POST /api/ai/suggest.php**
- Get AI editing suggestions
- Body: video_metadata, user_intent
- Returns: suggested_edits array

### Subtitle Management

**POST /api/subtitles/generate.php**
- Generate subtitles via Whisper
- Body: video_id, language
- Returns: subtitle_file (SRT format)

**POST /api/subtitles/upload.php**
- Upload custom subtitle file
- Body: video_id, subtitle_file (SRT/VTT)
- Returns: subtitle_id

**POST /api/subtitles/burn.php**
- Burn subtitles into video
- Body: video_id, subtitle_id, style_options
- Returns: processed_video_url

## Edit Operations JSON Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "video_id": {
      "type": "string",
      "description": "Unique video identifier"
    },
    "operations": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "operation_type": {
            "type": "string",
            "enum": [
              "trim",
              "cut",
              "add_subtitle",
              "burn_subtitle",
              "remove_audio",
              "replace_audio",
              "sync_audio",
              "remove_silence",
              "add_text",
              "add_watermark",
              "adjust_speed",
              "change_resolution",
              "apply_filter"
            ]
          },
          "parameters": {
            "type": "object"
          },
          "start_time": {
            "type": "number",
            "description": "Start time in seconds"
          },
          "end_time": {
            "type": "number",
            "description": "End time in seconds"
          }
        },
        "required": ["operation_type"]
      }
    }
  },
  "required": ["video_id", "operations"]
}
```

## Security Considerations

1. **File Upload Security**
   - Validate file types (video formats only)
   - Limit file size (configurable, default 2GB)
   - Sanitize filenames
   - Store uploads outside public directory
   - Generate unique video IDs (UUID)

2. **API Security**
   - CSRF protection on all POST requests
   - Rate limiting on AI API calls
   - Validate JSON structures
   - Sanitize user inputs
   - Secure API key storage (.env file)

3. **FFmpeg Security**
   - Validate all FFmpeg parameters
   - Prevent command injection
   - Use PHP-FFMpeg wrapper (safer than exec)
   - Limit processing time
   - Cleanup temp files after processing

4. **Shared Hosting Workaround**
   - Since exec() is disabled on hosting
   - Options:
     1. Use webhook to trigger processing on separate VPS
     2. Queue system with external worker
     3. Client-side processing only (WebCodecs + export)
     4. Upgrade hosting plan

## Performance Optimization

1. **Video Proxy System**
   - Generate lower quality proxy for editing
   - Apply edits to proxy in real-time
   - Render final video with original quality

2. **Caching Strategy**
   - Cache FFmpeg metadata extraction
   - Cache AI responses for similar requests
   - Cache processed segments
   - Store in /diagnostics/api-cache/

3. **Background Processing**
   - Queue long operations
   - Use job system for exports
   - Notify user when complete
   - Show progress updates

4. **WebCodecs Optimization**
   - Use hardware acceleration
   - Decode only visible frames
   - Implement frame caching
   - Lazy load video chunks

## Cost Estimation

**AI API Costs (Monthly)**
- OpenAI GPT-4: $0.03 per 1K tokens (input), $0.06 per 1K tokens (output)
- OpenAI Whisper: $0.006 per minute of audio
- Estimated: $50-200/month for 1000 videos

**Alternative: Self-hosted Whisper**
- Use codewithkyrian/whisper.php
- One-time setup cost
- No per-request fees
- Requires VPS with GPU (optional but faster)

## Development Timeline

| Phase | Duration | Deliverables |
|-------|----------|--------------|
| Phase 1: Foundation | 2 weeks | Project setup, basic upload/playback |
| Phase 2: Core Operations | 2 weeks | Video editing operations, timeline |
| Phase 3: AI Integration | 2 weeks | OpenAI + Whisper integration |
| Phase 4: Subtitles | 1 week | Subtitle generation and editing |
| Phase 5: Advanced Features | 2 weeks | Effects, transitions, multi-clip |
| Phase 6: Export | 1 week | Export system, optimization |
| Phase 7: UI/UX | 1 week | Polish, animations, UX improvements |
| Phase 8: Testing | 1 week | Comprehensive testing, deployment |
| **Total** | **12 weeks** | **Production-ready application** |

## Success Criteria

**Backend Verification**
- All functions execute without errors
- Returns correct output for all test inputs
- Error logs remain clean (no warnings/notices)
- Database operations complete correctly
- Handles both valid and invalid data appropriately

**Frontend Verification**
- Original bug does not reproduce
- All related functionality works
- No console errors
- Network requests complete successfully
- Visual elements render properly
- Interface responds correctly to user interactions

**Performance Benchmarks**
- Video upload: <5s for 100MB file
- Subtitle generation: <30s for 10min video
- Basic edit preview: Real-time (60fps)
- Export 1080p video: <2x video duration
- AI analysis: <10s per request

## Future Enhancements

1. **Collaborative Editing**
   - Multi-user editing sessions
   - Real-time collaboration
   - Comment system on timeline

2. **Templates & Presets**
   - Pre-built editing templates
   - Style presets
   - One-click effects

3. **Advanced AI Features**
   - Auto-cut to music beat
   - Scene detection
   - Face tracking
   - Object removal
   - Voice cloning for dubbing

4. **Mobile Support**
   - Responsive mobile interface
   - Touch-optimized timeline
   - Mobile app (Progressive Web App)

5. **Cloud Storage**
   - Direct import from cloud services
   - Auto-save to cloud
   - Share projects

## Resources & References

**Open Source Projects**
- Omniclip: https://github.com/omni-media/omniclip
- Etro.js: https://github.com/etro-js/etro
- Video.js: https://videojs.com/
- Wavesurfer.js: https://wavesurfer.xyz/

**PHP Libraries**
- PHP-FFMpeg: https://github.com/PHP-FFMpeg/PHP-FFMpeg
- OpenAI PHP: https://github.com/openai-php/client
- Whisper.php: https://github.com/CodeWithKyrian/whisper.php

**Documentation**
- FFmpeg Documentation: https://ffmpeg.org/documentation.html
- WebCodecs API: https://developer.mozilla.org/en-US/docs/Web/API/WebCodecs_API
- OpenAI API: https://platform.openai.com/docs
- Whisper API: https://platform.openai.com/docs/guides/speech-to-text

**Learning Resources**
- FFmpeg Subtitle Guide: https://en.wikibooks.org/wiki/FFMPEG_An_Intermediate_Guide/subtitle_options
- WebCodecs Tutorial: https://developer.chrome.com/docs/web-platform/best-practices/webcodecs
- Video.js Plugins: https://videojs.com/plugins/

## Conclusion

This plan provides a comprehensive roadmap for building a professional AI-powered video editing tool using native PHP and vanilla JavaScript. The architecture leverages modern browser capabilities (WebCodecs) for performance while maintaining server-side processing power through FFmpeg. The AI integration adds intelligent automation for common editing tasks, making the tool both powerful and user-friendly.

Key advantages of this approach:
- No heavy JavaScript frameworks (React/Next.js)
- Native PHP for maximum compatibility
- Modern browser APIs for performance
- AI-powered automation
- Open-source foundation
- Cost-effective implementation

The 12-week timeline is realistic for a single developer or small team, with clear phases and deliverables. The modular architecture allows for iterative development and easy feature additions in the future.
