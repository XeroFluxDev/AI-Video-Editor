const Export = {
    modal: null,
    currentExportFilename: null,

    init() {
        this.modal = document.getElementById('export-modal');
        this.initExportButton();
        this.initModalControls();
    },

    initExportButton() {
        const exportBtn = document.getElementById('export-video-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.showModal());
        }
    },

    initModalControls() {
        const closeBtn = document.getElementById('close-export-modal');
        const cancelBtn = document.getElementById('cancel-export-btn');
        const startExportBtn = document.getElementById('start-export-btn');
        const downloadBtn = document.getElementById('download-export-btn');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hideModal());
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.hideModal());
        }

        if (startExportBtn) {
            startExportBtn.addEventListener('click', () => this.startExport());
        }

        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => this.downloadExport());
        }

        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.hideModal();
                }
            });
        }
    },

    showModal() {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        if (this.modal) {
            this.modal.classList.remove('hidden');
            this.resetModalState();
        }
    },

    hideModal() {
        if (this.modal) {
            this.modal.classList.add('hidden');
        }
    },

    resetModalState() {
        const exportOptions = document.getElementById('export-options');
        const exportProgress = document.getElementById('export-progress');
        const exportComplete = document.getElementById('export-complete');

        if (exportOptions) exportOptions.classList.remove('hidden');
        if (exportProgress) exportProgress.classList.add('hidden');
        if (exportComplete) exportComplete.classList.add('hidden');

        const progressBar = document.getElementById('export-progress-bar');
        const progressText = document.getElementById('export-progress-text');
        if (progressBar) progressBar.style.width = '0%';
        if (progressText) progressText.textContent = 'Starting export...';
    },

    async startExport() {
        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        const format = document.getElementById('export-format')?.value || 'mp4';
        const quality = document.getElementById('export-quality')?.value || '1080p';
        const preset = document.getElementById('export-preset')?.value || 'medium';

        const exportOptions = document.getElementById('export-options');
        const exportProgress = document.getElementById('export-progress');

        if (exportOptions) exportOptions.classList.add('hidden');
        if (exportProgress) exportProgress.classList.remove('hidden');

        this.simulateProgress();

        try {
            const response = await fetch('../api/export/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    format: format,
                    quality: quality,
                    preset: preset
                })
            });

            const data = await response.json();

            if (data.success) {
                this.currentExportFilename = data.filename;
                this.showComplete(data);
            } else {
                this.hideModal();
                showNotification(data.error || 'Export failed', 'error');
            }
        } catch (error) {
            this.hideModal();
            showNotification('Export error: ' + error.message, 'error');
        }
    },

    simulateProgress() {
        const progressBar = document.getElementById('export-progress-bar');
        const progressText = document.getElementById('export-progress-text');
        let progress = 0;

        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 95) progress = 95;

            if (progressBar) progressBar.style.width = progress + '%';
            if (progressText) {
                if (progress < 30) {
                    progressText.textContent = 'Processing video...';
                } else if (progress < 60) {
                    progressText.textContent = 'Encoding...';
                } else {
                    progressText.textContent = 'Finalizing...';
                }
            }

            if (progress >= 95) {
                clearInterval(interval);
            }
        }, 500);
    },

    showComplete(data) {
        const exportProgress = document.getElementById('export-progress');
        const exportComplete = document.getElementById('export-complete');
        const exportFilename = document.getElementById('export-filename');
        const exportSize = document.getElementById('export-size');
        const progressBar = document.getElementById('export-progress-bar');
        const progressText = document.getElementById('export-progress-text');

        if (progressBar) progressBar.style.width = '100%';
        if (progressText) progressText.textContent = 'Complete!';

        setTimeout(() => {
            if (exportProgress) exportProgress.classList.add('hidden');
            if (exportComplete) exportComplete.classList.remove('hidden');

            if (exportFilename) exportFilename.textContent = data.filename;
            if (exportSize) {
                const sizeMB = (data.size / 1024 / 1024).toFixed(2);
                exportSize.textContent = sizeMB + ' MB';
            }

            showNotification('Export complete!', 'success');
        }, 500);
    },

    downloadExport() {
        if (this.currentExportFilename) {
            const link = document.createElement('a');
            link.href = `../storage/exports/${this.currentExportFilename}`;
            link.download = this.currentExportFilename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showNotification('Download started!', 'success');
        }
    }
};
