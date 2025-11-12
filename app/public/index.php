<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Video Editor</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: { primary: '#0f0f0f', secondary: '#1a1a1a', tertiary: '#242424', hover: '#2a2a2a', active: '#303030' },
                            text: { primary: '#f5f5f5', secondary: '#a0a0a0', tertiary: '#6b6b6b' },
                            border: { primary: '#333333', secondary: '#404040', focus: '#4a9eff' }
                        },
                        accent: { primary: '#4a9eff', hover: '#6ab0ff', active: '#3a8eef', success: '#10b981', warning: '#f59e0b', error: '#ef4444' }
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="assets/css/editor.css">
</head>
<body class="bg-dark-bg-primary text-dark-text-primary font-sans min-h-screen flex flex-col">

    <header id="app-header" class="bg-dark-bg-secondary border-b border-dark-border-primary">
        <div class="container mx-auto px-6 py-4" style="width: 80%;">
            <nav id="main-nav" class="flex items-center justify-between">
                <div id="nav-logo" class="flex items-center gap-3">
                    <i class="fas fa-video text-accent-primary text-2xl"></i>
                    <span class="text-xl font-bold">AI Video Editor</span>
                </div>

                <div id="nav-actions" class="flex items-center gap-3">
                    <button id="theme-toggle" class="w-10 h-10 text-dark-text-secondary hover:text-dark-text-primary hover:bg-dark-bg-hover rounded-lg transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <main id="app-main" class="flex-1 py-6">
        <div class="container mx-auto px-6" style="width: 80%;">

            <div id="upload-section" class="mb-6">
                <div id="upload-area" class="border-2 border-dashed border-dark-border-primary rounded-xl p-12 text-center hover:border-accent-primary transition-all cursor-pointer bg-dark-bg-secondary">
                    <i class="fas fa-cloud-upload text-6xl text-accent-primary mb-4"></i>
                    <h2 class="text-2xl font-bold mb-2">Upload Video</h2>
                    <p class="text-dark-text-secondary mb-4">Drag and drop or click to select</p>
                    <input type="file" id="video-input" accept="video/*" class="hidden">
                    <button id="upload-btn" class="px-6 py-3 bg-accent-primary hover:bg-accent-hover text-white font-medium rounded-lg transition-all">
                        Select Video
                    </button>
                </div>
            </div>

            <div id="editor-section" class="hidden">
                <div class="grid grid-cols-1 gap-6">

                    <div id="player-container" class="bg-dark-bg-secondary border border-dark-border-primary rounded-xl overflow-hidden">
                        <video id="video-player" class="video-js vjs-default-skin w-full" controls preload="auto">
                            <p class="vjs-no-js">To view this video please enable JavaScript</p>
                        </video>
                    </div>

                    <div id="timeline-container" class="bg-dark-bg-secondary border border-dark-border-primary rounded-xl p-4">
                        <h3 class="text-lg font-semibold mb-4">Timeline</h3>
                        <div id="timeline" class="relative h-20 bg-dark-bg-tertiary rounded-lg overflow-hidden mb-4">
                            <div id="playhead" class="absolute top-0 w-0.5 h-full bg-accent-error z-10" style="left: 0%">
                                <div class="absolute -top-1 -left-1.5 w-3 h-3 bg-accent-error rounded-full"></div>
                            </div>
                            <div id="timeline-track" class="absolute top-0 left-0 w-full h-full"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-dark-text-secondary">
                            <span id="timeline-start">00:00</span>
                            <span id="timeline-current" class="font-mono font-semibold text-dark-text-primary">00:00</span>
                            <span id="timeline-end">00:00</span>
                        </div>
                    </div>

                    <div id="editing-panel" class="bg-dark-bg-secondary border border-dark-border-primary rounded-xl p-6">
                        <h3 class="text-lg font-semibold mb-4">Editing Tools</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Trim Video</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-dark-text-secondary mb-1">Start Time (seconds)</label>
                                        <input type="number" id="trim-start" step="0.1" min="0" value="0" class="w-full px-3 py-2 bg-dark-bg-tertiary border border-dark-border-primary text-dark-text-primary rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-dark-text-secondary mb-1">End Time (seconds)</label>
                                        <input type="number" id="trim-end" step="0.1" min="0" value="0" class="w-full px-3 py-2 bg-dark-bg-tertiary border border-dark-border-primary text-dark-text-primary rounded-lg">
                                    </div>
                                </div>
                                <button id="trim-btn" class="mt-3 px-4 py-2 bg-accent-primary hover:bg-accent-hover text-white rounded-lg transition-all w-full">
                                    <i class="fas fa-cut mr-2"></i>Apply Trim
                                </button>
                            </div>

                            <div class="border-t border-dark-border-primary pt-4">
                                <label class="block text-sm font-medium mb-2">Quick Actions</label>
                                <div class="flex gap-3">
                                    <button id="mark-in-btn" class="px-4 py-2 bg-dark-bg-tertiary hover:bg-dark-bg-hover text-dark-text-primary border border-dark-border-primary rounded-lg transition-all flex-1">
                                        <i class="fas fa-flag mr-2"></i>Mark In
                                    </button>
                                    <button id="mark-out-btn" class="px-4 py-2 bg-dark-bg-tertiary hover:bg-dark-bg-hover text-dark-text-primary border border-dark-border-primary rounded-lg transition-all flex-1">
                                        <i class="fas fa-flag-checkered mr-2"></i>Mark Out
                                    </button>
                                </div>
                            </div>

                            <div id="processing-status" class="hidden border-t border-dark-border-primary pt-4">
                                <div class="flex items-center gap-3">
                                    <div class="animate-spin h-5 w-5 border-2 border-accent-primary border-t-transparent rounded-full"></div>
                                    <span class="text-sm text-dark-text-secondary">Processing video...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="ai-panel" class="bg-dark-bg-secondary border border-dark-border-primary rounded-xl p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-robot text-accent-primary text-xl"></i>
                            <h3 class="text-lg font-semibold">AI Assistant</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ask AI for editing suggestions</label>
                                <textarea
                                    id="ai-prompt"
                                    rows="3"
                                    placeholder="e.g., Make this video more engaging for social media, remove boring parts, suggest where to add transitions..."
                                    class="w-full px-3 py-2 bg-dark-bg-tertiary border border-dark-border-primary text-dark-text-primary rounded-lg resize-none"
                                ></textarea>
                            </div>

                            <button id="ask-ai-btn" class="px-4 py-2 bg-accent-primary hover:bg-accent-hover text-white rounded-lg transition-all w-full">
                                <i class="fas fa-sparkles mr-2"></i>Get AI Suggestions
                            </button>

                            <div id="ai-loading" class="hidden border-t border-dark-border-primary pt-4">
                                <div class="flex items-center gap-3">
                                    <div class="animate-spin h-5 w-5 border-2 border-accent-primary border-t-transparent rounded-full"></div>
                                    <span class="text-sm text-dark-text-secondary">AI is analyzing your video...</span>
                                </div>
                            </div>

                            <div id="ai-suggestions" class="hidden border-t border-dark-border-primary pt-4">
                                <h4 class="text-sm font-semibold mb-3">AI Suggestions</h4>
                                <div id="suggestions-list" class="space-y-3"></div>
                            </div>
                        </div>
                    </div>

                    <div id="video-info" class="bg-dark-bg-secondary border border-dark-border-primary rounded-xl p-6">
                        <h3 class="text-lg font-semibold mb-4">Video Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-dark-text-secondary">Filename:</span>
                                <span id="info-filename" class="font-mono">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-dark-text-secondary">Size:</span>
                                <span id="info-size" class="font-mono">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-dark-text-secondary">Type:</span>
                                <span id="info-type" class="font-mono">-</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <footer id="app-footer" class="bg-dark-bg-secondary border-t border-dark-border-primary mt-auto">
        <div class="container mx-auto px-6 py-4" style="width: 80%;">
            <div class="flex items-center justify-between text-sm text-dark-text-tertiary">
                <p>&copy; 2025 AI Video Editor</p>
                <p>Phase 3: AI Integration</p>
            </div>
        </div>
    </footer>

    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
    <script src="assets/js/timeline.js"></script>
    <script src="assets/js/ai.js"></script>
    <script src="assets/js/editor.js"></script>

</body>
</html>
