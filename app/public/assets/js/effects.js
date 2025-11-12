const Effects = {
    init() {
        this.initTextOverlay();
        this.initWatermark();
        this.initSpeed();
        this.initResolution();
        this.initFilters();
    },

    initTextOverlay() {
        const addTextBtn = document.getElementById('add-text-btn');
        const textInput = document.getElementById('text-overlay-input');
        const textPosition = document.getElementById('text-position');
        const textSize = document.getElementById('text-size');
        const textColor = document.getElementById('text-color');

        if (addTextBtn) {
            addTextBtn.addEventListener('click', () => {
                const text = textInput?.value?.trim();
                if (!text) {
                    showNotification('Please enter text', 'error');
                    return;
                }

                const position = textPosition?.value || 'bottom';
                const fontSize = parseInt(textSize?.value || 24);
                const fontColor = textColor?.value || 'white';

                this.addTextOverlay(text, position, fontSize, fontColor);
            });
        }
    },

    initWatermark() {
        const addWatermarkBtn = document.getElementById('add-watermark-btn');
        const watermarkInput = document.getElementById('watermark-input');
        const watermarkPosition = document.getElementById('watermark-position');

        if (addWatermarkBtn) {
            addWatermarkBtn.addEventListener('click', () => watermarkInput.click());
        }

        if (watermarkInput) {
            watermarkInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    const position = watermarkPosition?.value || 'bottom-right';
                    this.addWatermark(e.target.files[0], position);
                }
            });
        }
    },

    initSpeed() {
        const speedSlider = document.getElementById('speed-slider');
        const speedValue = document.getElementById('speed-value');
        const applySpeedBtn = document.getElementById('apply-speed-btn');

        if (speedSlider && speedValue) {
            speedSlider.addEventListener('input', (e) => {
                const speed = parseFloat(e.target.value);
                speedValue.textContent = speed.toFixed(1) + 'x';
            });
        }

        if (applySpeedBtn) {
            applySpeedBtn.addEventListener('click', () => {
                const speed = parseFloat(speedSlider?.value || 1.0);
                this.adjustSpeed(speed);
            });
        }
    },

    initResolution() {
        const resolutionSelect = document.getElementById('resolution-select');
        const applyResolutionBtn = document.getElementById('apply-resolution-btn');

        if (applyResolutionBtn) {
            applyResolutionBtn.addEventListener('click', () => {
                const resolution = resolutionSelect?.value || '1920x1080';
                const [width, height] = resolution.split('x').map(Number);
                this.changeResolution(width, height);
            });
        }
    },

    initFilters() {
        const brightnessSlider = document.getElementById('brightness-slider');
        const brightnessValue = document.getElementById('brightness-value');
        const contrastSlider = document.getElementById('contrast-slider');
        const contrastValue = document.getElementById('contrast-value');
        const saturationSlider = document.getElementById('saturation-slider');
        const saturationValue = document.getElementById('saturation-value');
        const applyFiltersBtn = document.getElementById('apply-filters-btn');
        const resetFiltersBtn = document.getElementById('reset-filters-btn');

        if (brightnessSlider && brightnessValue) {
            brightnessSlider.addEventListener('input', (e) => {
                brightnessValue.textContent = e.target.value;
            });
        }

        if (contrastSlider && contrastValue) {
            contrastSlider.addEventListener('input', (e) => {
                contrastValue.textContent = e.target.value;
            });
        }

        if (saturationSlider && saturationValue) {
            saturationSlider.addEventListener('input', (e) => {
                saturationValue.textContent = e.target.value;
            });
        }

        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', () => {
                const filters = {
                    brightness: parseFloat(brightnessSlider?.value || 0),
                    contrast: parseFloat(contrastSlider?.value || 1),
                    saturation: parseFloat(saturationSlider?.value || 1)
                };
                this.applyFilters(filters);
            });
        }

        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', () => {
                if (brightnessSlider) brightnessSlider.value = 0;
                if (brightnessValue) brightnessValue.textContent = '0';
                if (contrastSlider) contrastSlider.value = 1;
                if (contrastValue) contrastValue.textContent = '1';
                if (saturationSlider) saturationSlider.value = 1;
                if (saturationValue) saturationValue.textContent = '1';
            });
        }
    },

    async addTextOverlay(text, position, fontSize, fontColor) {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const yPos = {
            'top': '50',
            'center': '(h-text_h)/2',
            'bottom': 'h-th-50'
        };

        const formData = new FormData();
        formData.append('filename', currentVideo.filename);
        formData.append('operation', 'text_overlay');
        formData.append('text', text);
        formData.append('x', '(w-text_w)/2');
        formData.append('y', yPos[position] || yPos['bottom']);
        formData.append('fontSize', fontSize);
        formData.append('fontColor', fontColor);

        showProcessing(true);
        showNotification('Adding text overlay...', 'success');

        try {
            const response = await fetch('../api/effects/process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Text overlay added successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Add text overlay failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Add text overlay error: ' + error.message, 'error');
        }
    },

    async addWatermark(watermarkFile, position) {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('filename', currentVideo.filename);
        formData.append('operation', 'watermark');
        formData.append('watermark', watermarkFile);
        formData.append('position', position);

        showProcessing(true);
        showNotification('Adding watermark...', 'success');

        try {
            const response = await fetch('../api/effects/process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Watermark added successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Add watermark failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Add watermark error: ' + error.message, 'error');
        }
    },

    async adjustSpeed(speed) {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('filename', currentVideo.filename);
        formData.append('operation', 'adjust_speed');
        formData.append('speed', speed);

        showProcessing(true);
        showNotification('Adjusting video speed...', 'success');

        try {
            const response = await fetch('../api/effects/process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification(`Speed adjusted to ${speed}x successfully!`, 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Adjust speed failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Adjust speed error: ' + error.message, 'error');
        }
    },

    async changeResolution(width, height) {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('filename', currentVideo.filename);
        formData.append('operation', 'change_resolution');
        formData.append('width', width);
        formData.append('height', height);

        showProcessing(true);
        showNotification('Changing resolution...', 'success');

        try {
            const response = await fetch('../api/effects/process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification(`Resolution changed to ${width}x${height} successfully!`, 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Change resolution failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Change resolution error: ' + error.message, 'error');
        }
    },

    async applyFilters(filters) {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('filename', currentVideo.filename);
        formData.append('operation', 'apply_filters');

        if (filters.brightness !== 0) {
            formData.append('brightness', filters.brightness);
        }
        if (filters.contrast !== 1) {
            formData.append('contrast', filters.contrast);
        }
        if (filters.saturation !== 1) {
            formData.append('saturation', filters.saturation);
        }

        showProcessing(true);
        showNotification('Applying filters...', 'success');

        try {
            const response = await fetch('../api/effects/process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            showProcessing(false);

            if (data.success) {
                showNotification('Filters applied successfully!', 'success');
                currentVideo.filename = data.filename;
                loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
            } else {
                showNotification(data.error || 'Apply filters failed', 'error');
            }
        } catch (error) {
            showProcessing(false);
            showNotification('Apply filters error: ' + error.message, 'error');
        }
    }
};
