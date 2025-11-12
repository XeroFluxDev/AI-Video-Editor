const AI = {
    suggestions: [],

    init() {
        const askBtn = document.getElementById('ask-ai-btn');
        if (askBtn) {
            askBtn.addEventListener('click', () => this.analyze());
        }

        const promptInput = document.getElementById('ai-prompt');
        if (promptInput) {
            promptInput.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'Enter') {
                    this.analyze();
                }
            });
        }
    },

    async analyze() {
        const prompt = document.getElementById('ai-prompt').value.trim();

        if (!prompt) {
            showNotification('Please enter a prompt', 'error');
            return;
        }

        if (!currentVideo || !currentVideo.filename) {
            showNotification('No video loaded', 'error');
            return;
        }

        this.showLoading(true);
        document.getElementById('ai-suggestions').classList.add('hidden');

        try {
            const response = await fetch('../api/ai/analyze.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: currentVideo.filename,
                    prompt: prompt,
                    template: 'analyze_video'
                })
            });

            const data = await response.json();
            this.showLoading(false);

            if (data.success) {
                this.suggestions = data.suggestions || [];
                this.displaySuggestions(data.cached);
                showNotification(
                    data.cached ? 'Loaded cached suggestions' : 'AI analysis complete!',
                    'success'
                );
            } else {
                showNotification(data.error || 'AI analysis failed', 'error');
            }
        } catch (error) {
            this.showLoading(false);
            showNotification('AI request error: ' + error.message, 'error');
        }
    },

    displaySuggestions(cached = false) {
        const container = document.getElementById('suggestions-list');
        const panel = document.getElementById('ai-suggestions');

        if (this.suggestions.length === 0) {
            container.innerHTML = '<p class="text-sm text-dark-text-secondary">No suggestions found</p>';
            panel.classList.remove('hidden');
            return;
        }

        container.innerHTML = '';

        this.suggestions.forEach((suggestion, index) => {
            const card = this.createSuggestionCard(suggestion, index);
            container.appendChild(card);
        });

        panel.classList.remove('hidden');
    },

    createSuggestionCard(suggestion, index) {
        const card = document.createElement('div');
        card.className = 'bg-dark-bg-tertiary border border-dark-border-primary rounded-lg p-4';

        const typeIcon = this.getTypeIcon(suggestion.type);
        const typeColor = this.getTypeColor(suggestion.type);

        card.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i class="${typeIcon} ${typeColor}"></i>
                    <span class="text-xs font-semibold text-dark-text-secondary uppercase">${suggestion.type}</span>
                </div>
                <span class="text-xs text-dark-text-tertiary">#${index + 1}</span>
            </div>
            <h5 class="text-sm font-semibold mb-2">${suggestion.action}</h5>
            <p class="text-xs text-dark-text-secondary mb-3">${suggestion.reason}</p>
            ${this.renderParams(suggestion.params)}
            <button
                onclick="AI.applySuggestion(${index})"
                class="px-3 py-1.5 bg-accent-primary hover:bg-accent-hover text-white text-sm rounded-lg transition-all w-full"
            >
                <i class="fas fa-check mr-2"></i>Apply This Suggestion
            </button>
        `;

        return card;
    },

    renderParams(params) {
        if (!params || Object.keys(params).length === 0) {
            return '';
        }

        let html = '<div class="text-xs text-dark-text-tertiary mb-2">';

        if (params.start !== undefined) {
            html += `<span class="inline-block mr-3"><i class="fas fa-clock mr-1"></i>Start: ${params.start}s</span>`;
        }
        if (params.end !== undefined) {
            html += `<span class="inline-block mr-3"><i class="fas fa-clock mr-1"></i>End: ${params.end}s</span>`;
        }
        if (params.duration !== undefined) {
            html += `<span class="inline-block mr-3"><i class="fas fa-hourglass mr-1"></i>Duration: ${params.duration}s</span>`;
        }

        html += '</div>';
        return html;
    },

    getTypeIcon(type) {
        const icons = {
            trim: 'fas fa-cut',
            cut: 'fas fa-scissors',
            effect: 'fas fa-magic',
            audio: 'fas fa-volume-up',
            subtitle: 'fas fa-closed-captioning',
            general: 'fas fa-lightbulb'
        };
        return icons[type] || icons.general;
    },

    getTypeColor(type) {
        const colors = {
            trim: 'text-accent-primary',
            cut: 'text-accent-warning',
            effect: 'text-purple-400',
            audio: 'text-green-400',
            subtitle: 'text-yellow-400',
            general: 'text-blue-400'
        };
        return colors[type] || colors.general;
    },

    async applySuggestion(index) {
        const suggestion = this.suggestions[index];

        if (!suggestion) {
            showNotification('Invalid suggestion', 'error');
            return;
        }

        switch (suggestion.type) {
            case 'trim':
                await this.applyTrim(suggestion);
                break;
            case 'cut':
                await this.applyCut(suggestion);
                break;
            default:
                showNotification(`Apply ${suggestion.type} (Coming in future phases)`, 'success');
        }
    },

    async applyTrim(suggestion) {
        const params = suggestion.params;

        if (!params.start || !params.end) {
            showNotification('Trim parameters missing', 'error');
            return;
        }

        document.getElementById('trim-start').value = params.start;
        document.getElementById('trim-end').value = params.end;

        showNotification('Trim values set! Click "Apply Trim" to process', 'success');

        const trimBtn = document.getElementById('trim-btn');
        trimBtn.classList.add('animate-pulse');
        setTimeout(() => {
            trimBtn.classList.remove('animate-pulse');
        }, 2000);
    },

    async applyCut(suggestion) {
        showNotification('Cut functionality will be enhanced in future updates', 'success');
    },

    showLoading(show) {
        const loading = document.getElementById('ai-loading');
        if (show) {
            loading.classList.remove('hidden');
        } else {
            loading.classList.add('hidden');
        }
    }
};
