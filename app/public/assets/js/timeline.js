const Timeline = {
    player: null,
    duration: 0,
    currentTime: 0,
    isDragging: false,

    init(videoPlayer) {
        this.player = videoPlayer;
        this.setupEvents();
    },

    setupEvents() {
        const timeline = document.getElementById('timeline');
        const playhead = document.getElementById('playhead');

        timeline.addEventListener('click', (e) => this.seek(e));

        playhead.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            e.stopPropagation();
        });

        document.addEventListener('mousemove', (e) => {
            if (this.isDragging) {
                this.drag(e);
            }
        });

        document.addEventListener('mouseup', () => {
            this.isDragging = false;
        });

        if (this.player) {
            this.player.on('timeupdate', () => this.update());
            this.player.on('loadedmetadata', () => {
                this.duration = this.player.duration();
                this.updateLabels();
            });
        }
    },

    seek(e) {
        const timeline = document.getElementById('timeline');
        const rect = timeline.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        const time = percent * this.duration;

        if (this.player) {
            this.player.currentTime(time);
        }
    },

    drag(e) {
        const timeline = document.getElementById('timeline');
        const rect = timeline.getBoundingClientRect();
        const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        const time = percent * this.duration;

        if (this.player) {
            this.player.currentTime(time);
        }
    },

    update() {
        if (!this.player) return;

        this.currentTime = this.player.currentTime();
        const percent = (this.currentTime / this.duration) * 100;

        const playhead = document.getElementById('playhead');
        playhead.style.left = percent + '%';

        document.getElementById('timeline-current').textContent = this.formatTime(this.currentTime);
    },

    updateLabels() {
        document.getElementById('timeline-start').textContent = '00:00';
        document.getElementById('timeline-end').textContent = this.formatTime(this.duration);
        document.getElementById('trim-end').value = this.duration.toFixed(1);
        document.getElementById('trim-end').max = this.duration;
        document.getElementById('trim-start').max = this.duration;
    },

    formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);

        if (h > 0) {
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    },

    getCurrentTime() {
        return this.currentTime;
    },

    getDuration() {
        return this.duration;
    }
};
