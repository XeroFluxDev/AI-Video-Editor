const Audio = {
    wavesurfer: null,
    currentVolume: 1.0,

    init() {
        this.initWavesurfer();
        this.initControls();
    },

    initWavesurfer() {
        const container = document.getElementById('waveform');
        if (!container) return;

        this.wavesurfer = WaveSurfer.create({
            container: container,
            waveColor: '#6b6b6b',
            progressColor: '#4a9eff',
            cursorColor: '#ef4444',
            barWidth: 2,
            barRadius: 3,
            cursorWidth: 2,
            height: 80,
            barGap: 2,
            normalize: true,
            backend: 'WebAudio'
        });

        this.wavesurfer.on('ready', () => {
            showNotification('Waveform loaded', 'success');
        });

        this.wavesurfer.on('error', (err) => {
            console.error('Wavesurfer error:', err);
        });
    },

    loadWaveform(videoPath) {
        if (!this.wavesurfer) return;

        const audioPath = videoPath.replace(/\.[^/.]+$/, '.mp3');

        this.wavesurfer.load(videoPath);
    },

    initControls() {
        const volumeSlider = document.getElementById('volume-slider');
        const volumeValue = document.getElementById('volume-value');
        const muteBtn = document.getElementById('mute-btn');
        const removeAudioBtn = document.getElementById('remove-audio-btn');
        const replaceAudioBtn = document.getElementById('replace-audio-btn');
        const normalizeBtn = document.getElementById('normalize-audio-btn');
        const removeSilenceBtn = document.getElementById('remove-silence-btn');
        const audioInput = document.getElementById('audio-input');

        if (volumeSlider) {
            volumeSlider.addEventListener('input', (e) => {
                this.currentVolume = parseFloat(e.target.value);
                volumeValue.textContent = Math.round(this.currentVolume * 100) + '%';
                if (player) player.volume(this.currentVolume);
            });
        }

        if (muteBtn) {
            muteBtn.addEventListener('click', () => {
                if (player) {
                    const isMuted = player.muted();
                    player.muted(!isMuted);
                    muteBtn.innerHTML = isMuted
                        ? '<i class="fas fa-volume-up"></i>'
                        : '<i class="fas fa-volume-mute"></i>';
                }
            });
        }

        if (removeAudioBtn) {
            removeAudioBtn.addEventListener('click', () => this.removeAudio());
        }

        if (replaceAudioBtn) {
            replaceAudioBtn.addEventListener('click', () => audioInput.click());
        }

        if (audioInput) {
            audioInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    this.replaceAudio(e.target.files[0]);
                }
            });
        }

        if (normalizeBtn) {
            normalizeBtn.addEventListener('click', () => this.normalizeAudio());
        }

        if (removeSilenceBtn) {
            removeSilenceBtn.addEventListener('click', () => this.removeSilence());
        }
    },

    async removeAudio() {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        showProcessing(true);
        showNotification('Removing audio...', 'success');

        try {
            const response = await fetch('../api/audio/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    operation: 'remove_audio'
                })
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Audio removed successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Remove audio failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Remove audio error: ' + error.message, 'error');
        }
    },

    async replaceAudio(audioFile) {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('audio', audioFile);

        showProcessing(true);
        showNotification('Replacing audio...', 'success');

        try {
            const url = `../api/audio/process.php?filename=${encodeURIComponent(currentVideo.filename)}&operation=replace_audio`;

            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Audio replaced successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Replace audio failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Replace audio error: ' + error.message, 'error');
        }
    },

    async normalizeAudio() {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        showProcessing(true);
        showNotification('Normalizing audio...', 'success');

        try {
            const response = await fetch('../api/audio/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    operation: 'normalize_audio'
                })
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Audio normalized successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Normalize audio failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Normalize audio error: ' + error.message, 'error');
        }
    },

    async removeSilence() {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const threshold = parseFloat(document.getElementById('silence-threshold').value || -50);
        const duration = parseFloat(document.getElementById('silence-duration').value || 0.5);

        showProcessing(true);
        showNotification('Removing silence...', 'success');

        try {
            const response = await fetch('../api/audio/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    operation: 'remove_silence',
                    threshold: threshold,
                    duration: duration
                })
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Silence removed successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Remove silence failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Remove silence error: ' + error.message, 'error');
        }
    },

    cleanup() {
        if (this.wavesurfer) {
            this.wavesurfer.destroy();
            this.wavesurfer = null;
        }
    }
};
