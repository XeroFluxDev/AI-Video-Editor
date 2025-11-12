let player = null;
let currentVideo = null;

document.addEventListener('DOMContentLoaded', () => {
    initUpload();
    initThemeToggle();
    AI.init();
});

function initUpload() {
    const uploadArea = document.getElementById('upload-area');
    const videoInput = document.getElementById('video-input');
    const uploadBtn = document.getElementById('upload-btn');

    uploadBtn.addEventListener('click', () => videoInput.click());
    videoInput.addEventListener('change', handleFileSelect);

    uploadArea.addEventListener('click', () => videoInput.click());
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('drag-over');
    });
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });
}

function handleFileSelect(e) {
    if (e.target.files.length) {
        handleFile(e.target.files[0]);
    }
}

async function handleFile(file) {
    const formData = new FormData();
    formData.append('video', file);

    showNotification('Uploading video...', 'success');

    try {
        const response = await fetch('../api/video/upload.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            currentVideo = data;
            showNotification('Upload complete!', 'success');
            loadVideo(data);
        } else {
            showNotification(data.error || 'Upload failed', 'error');
        }
    } catch (error) {
        showNotification('Upload error: ' + error.message, 'error');
    }
}

function loadVideo(videoData) {
    document.getElementById('upload-section').classList.add('hidden');
    document.getElementById('editor-section').classList.remove('hidden');

    const videoPath = `../storage/uploads/${videoData.filename}`;

    if (player) {
        player.dispose();
    }

    player = videojs('video-player', {
        controls: true,
        autoplay: false,
        preload: 'auto',
        fluid: true,
        sources: [{
            src: videoPath,
            type: videoData.type
        }]
    });

    document.getElementById('info-filename').textContent = videoData.filename;
    document.getElementById('info-size').textContent = (videoData.size / 1024 / 1024).toFixed(2) + ' MB';
    document.getElementById('info-type').textContent = videoData.type;

    player.ready(() => {
        Timeline.init(player);
        initControls();
        initEditingTools();
    });
}

function initControls() {
    document.getElementById('play-btn').addEventListener('click', () => {
        if (player) player.play();
    });

    document.getElementById('pause-btn').addEventListener('click', () => {
        if (player) player.pause();
    });
}

function initThemeToggle() {
    const toggle = document.getElementById('theme-toggle');
    toggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        const icon = toggle.querySelector('i');
        icon.className = document.documentElement.classList.contains('dark')
            ? 'fas fa-moon'
            : 'fas fa-sun';
    });
}

function initEditingTools() {
    document.getElementById('trim-btn').addEventListener('click', handleTrim);
    document.getElementById('mark-in-btn').addEventListener('click', () => {
        const time = Timeline.getCurrentTime();
        document.getElementById('trim-start').value = time.toFixed(1);
        showNotification(`Mark In set at ${Timeline.formatTime(time)}`, 'success');
    });
    document.getElementById('mark-out-btn').addEventListener('click', () => {
        const time = Timeline.getCurrentTime();
        document.getElementById('trim-end').value = time.toFixed(1);
        showNotification(`Mark Out set at ${Timeline.formatTime(time)}`, 'success');
    });
}

async function handleTrim() {
    const start = parseFloat(document.getElementById('trim-start').value);
    const end = parseFloat(document.getElementById('trim-end').value);
    const duration = end - start;

    if (start < 0 || end <= start || end > Timeline.getDuration()) {
        showNotification('Invalid trim values', 'error');
        return;
    }

    showProcessing(true);
    showNotification('Processing trim...', 'success');

    try {
        const response = await fetch('../api/video/process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                operation: 'trim',
                filename: currentVideo.filename,
                start: start,
                duration: duration
            })
        });

        const data = await response.json();
        showProcessing(false);

        if (data.success) {
            showNotification('Trim complete!', 'success');
            currentVideo.filename = data.filename;
            loadVideo({ ...currentVideo, filename: data.filename, type: 'video/mp4' });
        } else {
            showNotification(data.error || 'Trim failed', 'error');
        }
    } catch (error) {
        showProcessing(false);
        showNotification('Trim error: ' + error.message, 'error');
    }
}

function showProcessing(show) {
    const status = document.getElementById('processing-status');
    if (show) {
        status.classList.remove('hidden');
    } else {
        status.classList.add('hidden');
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} text-xl"></i>
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-dark-text-tertiary hover:text-dark-text-primary">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
