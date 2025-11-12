const Subtitles = {
    currentSRT: null,
    srtFilename: null,

    init() {
        const generateBtn = document.getElementById('generate-subtitles-btn');
        const burnBtn = document.getElementById('burn-subtitles-btn');
        const embedBtn = document.getElementById('embed-subtitles-btn');

        if (generateBtn) {
            generateBtn.addEventListener('click', () => this.generate());
        }

        if (burnBtn) {
            burnBtn.addEventListener('click', () => this.burn());
        }

        if (embedBtn) {
            embedBtn.addEventListener('click', () => this.embed());
        }
    },

    async generate() {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const language = document.getElementById('subtitle-language').value;

        this.showLoading(true);
        showNotification('Generating subtitles... This may take a few minutes', 'success');

        try {
            const response = await fetch('../api/subtitles/generate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    operation: 'generate',
                    language: language
                })
            });

            const data = await response.json();
            this.showLoading(false);

            if (data.success) {
                this.currentSRT = data.srt_content;
                this.srtFilename = data.srt_filename;

                showNotification(
                    `Subtitles generated! ${data.segments} segments found`,
                    'success'
                );

                this.showOptions(true);
                this.displayPreview(data.srt_content);
            } else {
                showNotification(data.error || 'Subtitle generation failed', 'error');
            }
        } catch (error) {
            this.showLoading(false);
            showNotification('Subtitle generation error: ' + error.message, 'error');
        }
    },

    async burn() {
        if (!this.srtFilename) {
            showNotification('Generate subtitles first', 'error');
            return;
        }

        const fontsize = parseInt(document.getElementById('subtitle-fontsize').value);
        const fontcolor = document.getElementById('subtitle-color').value;

        showProcessing(true);
        showNotification('Burning subtitles into video...', 'success');

        try {
            const response = await fetch('../api/subtitles/generate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    operation: 'burn',
                    srt_filename: this.srtFilename,
                    style: {
                        fontsize: fontsize,
                        fontcolor: fontcolor,
                        bordercolor: 'black',
                        borderw: 2
                    }
                })
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Subtitles burned successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Burn failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Burn error: ' + error.message, 'error');
        }
    },

    async embed() {
        if (!this.srtFilename) {
            showNotification('Generate subtitles first', 'error');
            return;
        }

        showProcessing(true);
        showNotification('Embedding subtitles...', 'success');

        try {
            const response = await fetch('../api/subtitles/generate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    operation: 'embed',
                    srt_filename: this.srtFilename
                })
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Subtitles embedded! Enable in video player', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Embed failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Embed error: ' + error.message, 'error');
        }
    },

    displayPreview(srtContent) {
        const container = document.getElementById('subtitle-preview-content');

        if (!srtContent) {
            container.innerHTML = '<p class="text-dark-text-tertiary">No preview available</p>';
            return;
        }

        const segments = this.parseSRT(srtContent);
        const preview = segments.slice(0, 5);

        container.innerHTML = preview.map(seg => `
            <div class="border-l-2 border-accent-primary pl-2">
                <p class="text-dark-text-tertiary">${seg.timestamp}</p>
                <p class="text-dark-text-primary">${seg.text}</p>
            </div>
        `).join('');
    },

    parseSRT(content) {
        const blocks = content.trim().split('\n\n');
        return blocks.map(block => {
            const lines = block.split('\n');
            return {
                index: lines[0],
                timestamp: lines[1] || '',
                text: lines.slice(2).join(' ')
            };
        }).filter(seg => seg.timestamp);
    },

    showLoading(show) {
        const loading = document.getElementById('subtitle-loading');
        if (show) {
            loading.classList.remove('hidden');
        } else {
            loading.classList.add('hidden');
        }
    },

    showOptions(show) {
        const options = document.getElementById('subtitle-options');
        if (show) {
            options.classList.remove('hidden');
        } else {
            options.classList.add('hidden');
        }
    }
};
