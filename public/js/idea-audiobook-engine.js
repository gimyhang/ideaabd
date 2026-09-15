/**
 * Idea Publication - Universal World-Class Bengali Audiobook Engine
 * Supports Blog, E-Books, Webzines, Research Papers & Global Text Selection
 */
(function(window, document) {
    'use strict';

    const IdeaAudiobook = {
        isSpeaking: false,
        isPaused: false,
        isMuted: false,
        isMinimized: false,
        synth: ('speechSynthesis' in window) ? window.speechSynthesis : null,
        speechChunks: [],
        currentChunkIndex: 0,
        watchdogTimer: null,
        sleepTimerInterval: null,
        sleepRemainingSec: 0,
        activeUtterance: null,
        bengaliVoice: null,
        preferredVoiceType: 'bd', // 'bd', 'in', 'default'
        ttsRate: 1.0,
        ttsPitch: 1.0,
        currentHighlightedEl: null,
        activeStorageKey: null,

        // Bengali number conversion
        toBengaliDigits: function(num) {
            if (num === null || num === undefined) return '০';
            const banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
            return String(num).replace(/[0-9]/g, w => banglaDigits[+w]);
        },

        init: function() {
            if (!this.synth) return;
            this.initVoices();
            if (speechSynthesis.onvoiceschanged !== undefined) {
                speechSynthesis.onvoiceschanged = () => this.initVoices();
            }
            this.setupSelectionListener();
            this.setupKeyboardShortcuts();
        },

        initVoices: function() {
            if (!this.synth) return;
            try {
                const voices = this.synth.getVoices() || [];
                if (!voices.length) return;

                const bdVoice = voices.find(v => v.lang === 'bn-BD' || v.lang === 'bn_BD')
                    || voices.find(v => (v.lang && v.lang.toLowerCase().startsWith('bn-bd')) || (v.name && v.name.toLowerCase().includes('bangladesh')));
                
                const inVoice = voices.find(v => v.lang === 'bn-IN' || v.lang === 'bn_IN')
                    || voices.find(v => (v.lang && v.lang.toLowerCase().startsWith('bn-in')) || (v.name && v.name.toLowerCase().includes('india')));
                
                const generalBnVoice = voices.find(v => v.lang && v.lang.toLowerCase().startsWith('bn'))
                    || voices.find(v => v.name && (
                        v.name.toLowerCase().includes('bangla') || 
                        v.name.toLowerCase().includes('bengali') ||
                        v.name.toLowerCase().includes('tapti') ||
                        v.name.toLowerCase().includes('mithun') ||
                        v.name.toLowerCase().includes('bashkar')
                    ));

                if (this.preferredVoiceType === 'bd' && (bdVoice || generalBnVoice)) {
                    this.bengaliVoice = bdVoice || generalBnVoice;
                } else if (this.preferredVoiceType === 'in' && (inVoice || generalBnVoice)) {
                    this.bengaliVoice = inVoice || generalBnVoice;
                } else {
                    this.bengaliVoice = bdVoice || inVoice || generalBnVoice || null;
                }
            } catch (err) {
                console.warn('IdeaAudiobook voice init error:', err);
            }
        },

        cleanAndNormalizeText: function(text) {
            if (!text) return '';
            let cleaned = text;

            // Remove zero-width & control chars
            cleaned = cleaned.replace(/[\u200B-\u200D\uFEFF\u00A0\u200E\u200F\u00AD\u202A-\u202E]/g, ' ');

            // Expand common Bengali abbreviations to natural speakable words
            cleaned = cleaned
                .replace(/(?:^|\s)ড\.(?=\s|[অ-হ])/g, ' ডক্টর ')
                .replace(/(?:^|\s)ডা\.(?=\s|[অ-হ])/g, ' ডাক্তার ')
                .replace(/(?:^|\s)প্রো\.(?=\s|[অ-হ])/g, ' প্রফেসর ')
                .replace(/(?:^|\s)প্রফে\.(?=\s|[অ-হ])/g, ' প্রফেসর ')
                .replace(/(?:^|\s)পৃ\.(?=\s|[০-৯\d])/g, ' পৃষ্ঠা ')
                .replace(/(?:^|\s)ইত্যা\.(?=\s|$|[।!?])/g, ' ইত্যাদি ')
                .replace(/(?:^|\s)নং(?=\s|[০-৯\d])/g, ' নম্বর ')
                .replace(/(?:^|\s)মো\.(?=\s|[অ-হ])/g, ' মোহাম্মদ ')
                .replace(/(?:^|\s)মি\.(?=\s|[অ-হ])/g, ' মিস্টার ')
                .replace(/(?:^|\s)খ্রি\.(?=\s|[০-৯\d])/g, ' খ্রিস্টাব্দ ')
                .replace(/%/g, ' শতাংশ ')
                .replace(/&/g, ' এবং ')
                .replace(/\+/g, ' যোগ ')
                .replace(/=/g, ' সমান ')
                .replace(/\//g, ' বা ');

            // Remove reference brackets [১], [1]
            cleaned = cleaned.replace(/\[\s*[০-৯\d]+\s*\]/g, ' ');
            
            // Period to Bengali dāṛi
            cleaned = cleaned.replace(/([অ-হ\u0980-\u09FF])\s*\.\s+/g, '$1। ');

            // Double dash to pause comma
            cleaned = cleaned.replace(/[—–-]{2,}/g, ', ').replace(/[\"\'\`]/g, ' ');

            // Whitespace
            cleaned = cleaned.replace(/[\r\n\t]+/g, ' ').replace(/\s{2,}/g, ' ').trim();

            return cleaned;
        },

        splitIntoSmartSentences: function(text, maxChunkLen = 95) {
            if (!text) return [];
            const cleaned = this.cleanAndNormalizeText(text);
            if (!cleaned) return [];

            const rawPieces = cleaned.split(/([।!?؛;\n\r]+)/);
            const sentences = [];
            let buffer = '';

            for (let i = 0; i < rawPieces.length; i++) {
                const piece = (rawPieces[i] || '').trim();
                if (!piece) continue;

                if (/^[।!?؛;\n\r]+$/.test(piece)) {
                    if (buffer) {
                        sentences.push((buffer + ' ' + piece).trim());
                        buffer = '';
                    }
                } else {
                    if (buffer) {
                        if ((buffer + ' ' + piece).length <= maxChunkLen) {
                            buffer += ' ' + piece;
                        } else {
                            sentences.push(buffer);
                            buffer = piece;
                        }
                    } else {
                        buffer = piece;
                    }
                }
            }
            if (buffer) sentences.push(buffer);

            const finalChunks = [];
            for (const s of sentences) {
                if (s.length <= maxChunkLen) {
                    finalChunks.push(s);
                } else {
                    const subParts = s.split(/([,]+)/);
                    let subBuf = '';
                    for (let k = 0; k < subParts.length; k++) {
                        const subP = (subParts[k] || '').trim();
                        if (!subP) continue;
                        if (subP === ',') {
                            if (subBuf) {
                                finalChunks.push((subBuf + ',').trim());
                                subBuf = '';
                            }
                        } else if ((subBuf + ' ' + subP).length <= maxChunkLen) {
                            subBuf = subBuf ? (subBuf + ' ' + subP) : subP;
                        } else {
                            if (subBuf) finalChunks.push(subBuf);
                            if (subP.length > maxChunkLen) {
                                const words = subP.split(' ');
                                let wBuf = '';
                                for (const w of words) {
                                    if ((wBuf + ' ' + w).length <= maxChunkLen) {
                                        wBuf = wBuf ? (wBuf + ' ' + w) : w;
                                    } else {
                                        if (wBuf) finalChunks.push(wBuf);
                                        wBuf = w;
                                    }
                                }
                                if (wBuf) finalChunks.push(wBuf);
                                subBuf = '';
                            } else {
                                subBuf = subP;
                            }
                        }
                    }
                    if (subBuf) finalChunks.push(subBuf);
                }
            }

            return finalChunks.filter(c => c && c.trim().length > 0);
        },

        // Universal DOM content extractor
        extractChunksFromDOM: function(containerSelector = '#articleBody', titleSelector = '.lit-title') {
            const chunks = [];
            const titleEl = document.querySelector(titleSelector);
            if (titleEl && titleEl.textContent.trim()) {
                const titleSentences = this.splitIntoSmartSentences(titleEl.textContent.trim() + '। ');
                titleSentences.forEach(s => chunks.push({ text: s, el: titleEl }));
            }

            const container = document.querySelector(containerSelector);
            if (container) {
                const blockElements = container.querySelectorAll('p, blockquote, li, h1, h2, h3, h4, h5, h6');
                blockElements.forEach((node) => {
                    if (node.closest('.no-print') || node.classList.contains('no-print') || node.classList.contains('lit-ornament')) return;
                    
                    const txt = node.innerText || node.textContent;
                    if (!txt || !txt.trim()) return;

                    // Attach hover listen button
                    if (!node.querySelector('.tts-para-speak-btn') && !['LI'].includes(node.tagName)) {
                        const speakBtn = document.createElement('button');
                        speakBtn.type = 'button';
                        speakBtn.className = 'tts-para-speak-btn no-print';
                        speakBtn.title = 'এই অনুচ্ছেদ থেকে পাঠ শুনুন';
                        speakBtn.innerHTML = '<i class="fa-solid fa-play" style="font-size: 10px;"></i>';
                        speakBtn.onclick = (e) => {
                            e.stopPropagation();
                            this.listenToElement(node);
                        };
                        node.style.position = 'relative';
                        node.appendChild(speakBtn);
                    }

                    const nodeSentences = this.splitIntoSmartSentences(txt);
                    nodeSentences.forEach(s => {
                        chunks.push({ text: s, el: node });
                    });
                });
            }

            return chunks;
        },

        clearWatchdog: function() {
            if (this.watchdogTimer) {
                clearTimeout(this.watchdogTimer);
                this.watchdogTimer = null;
            }
        },

        highlightActiveElement: function(chunkObj) {
            if (this.currentHighlightedEl) {
                this.currentHighlightedEl.classList.remove('tts-live-highlight');
                this.currentHighlightedEl = null;
            }
            if (!chunkObj || !chunkObj.el) return;

            const el = chunkObj.el;
            el.classList.add('tts-live-highlight');
            this.currentHighlightedEl = el;

            const rect = el.getBoundingClientRect();
            if (rect.top < 90 || rect.bottom > (window.innerHeight - 100)) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        updateUI: function() {
            const btn = document.getElementById('ttsToggleBtn');
            const label = document.getElementById('ttsBtnLabel');
            const icon = document.getElementById('ttsIcon');
            const wave = document.getElementById('ttsWaveAnimation');
            const dock = document.getElementById('audiobookFloatingDock');
            const dockPlayPauseIcon = document.getElementById('dockPlayPauseIcon');
            const dockSentenceCounter = document.getElementById('dockSentenceCounter');
            const dockProgressPercent = document.getElementById('dockProgressPercent');
            const scrubFill = document.getElementById('ttsScrubFill');
            const timeEstimate = document.getElementById('dockTimeEstimate');

            if (this.isSpeaking) {
                if (dock) dock.classList.add('active');
                if (btn) {
                    btn.classList.add('is-playing');
                    btn.classList.toggle('is-paused', this.isPaused);
                }
                if (label) label.textContent = this.isPaused ? 'পাঠ পজ আছে' : 'পাঠ থামান';
                if (icon) icon.className = this.isPaused ? 'fa-solid fa-circle-play fs-5 text-white' : 'fa-solid fa-circle-pause fs-5 text-white';
                if (wave) {
                    if (this.isPaused) wave.classList.add('d-none');
                    else wave.classList.remove('d-none');
                }
                if (dockPlayPauseIcon) {
                    dockPlayPauseIcon.className = this.isPaused ? 'fa-solid fa-play' : 'fa-solid fa-pause';
                }

                const total = this.speechChunks.length || 1;
                const current = Math.min(this.currentChunkIndex + 1, total);
                const percent = Math.min(100, Math.round((current / total) * 100));

                if (dockSentenceCounter) {
                    dockSentenceCounter.textContent = `বাক্য ${this.toBengaliDigits(current)} / ${this.toBengaliDigits(total)}`;
                }
                if (dockProgressPercent) {
                    dockProgressPercent.textContent = `${this.toBengaliDigits(percent)}%`;
                }
                if (scrubFill) {
                    scrubFill.style.width = `${percent}%`;
                }
                if (timeEstimate) {
                    const remainingChunks = total - current;
                    const estMin = Math.ceil((remainingChunks * 4.2) / (60 * this.ttsRate));
                    timeEstimate.textContent = estMin > 0 ? `বাকি আনুমানিক ${this.toBengaliDigits(estMin)} মি.` : 'শেষ বাক্য';
                }
            } else {
                if (dock) dock.classList.remove('active');
                if (btn) {
                    btn.classList.remove('is-playing', 'is-paused');
                }
                if (label) label.textContent = 'পাঠ শুনুন';
                if (icon) icon.className = 'fa-solid fa-circle-play fs-5';
                if (wave) wave.classList.add('d-none');
                if (this.currentHighlightedEl) {
                    this.currentHighlightedEl.classList.remove('tts-live-highlight');
                    this.currentHighlightedEl = null;
                }
                if (scrubFill) {
                    scrubFill.style.width = '0%';
                }
            }
        },

        showToast: function(message, iconClass = 'fa-solid fa-circle-check text-success') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, iconClass);
                return;
            }
            const container = document.getElementById('generalToastContainer');
            const msgEl = document.getElementById('toastMessage');
            const iconEl = document.getElementById('toastIcon');
            if (container && msgEl && iconEl) {
                msgEl.textContent = message;
                iconEl.className = iconClass + ' fs-5';
                container.classList.remove('d-none');
                container.style.opacity = '1';
                setTimeout(() => {
                    container.style.opacity = '0';
                    setTimeout(() => container.classList.add('d-none'), 300);
                }, 3500);
            }
        },

        playNextChunk: function() {
            this.clearWatchdog();

            if (!this.isSpeaking || !this.synth) return;
            if (this.isPaused) return;

            if (this.currentChunkIndex >= this.speechChunks.length) {
                if (this.activeStorageKey) {
                    try { localStorage.removeItem(this.activeStorageKey); } catch (e) {}
                }
                this.stop(false);
                this.showToast('সম্পূর্ণ লেখার পাঠ সম্পন্ন হয়েছে।', 'fa-solid fa-circle-check text-success');
                return;
            }

            const chunkObj = this.speechChunks[this.currentChunkIndex];
            const chunkText = chunkObj ? chunkObj.text : '';

            if (!chunkText || !chunkText.trim()) {
                this.currentChunkIndex++;
                this.playNextChunk();
                return;
            }

            if (this.activeStorageKey) {
                try { localStorage.setItem(this.activeStorageKey, this.currentChunkIndex); } catch (e) {}
            }

            this.updateUI();
            this.highlightActiveElement(chunkObj);

            try { this.synth.cancel(); } catch (e) {}

            this.activeUtterance = new SpeechSynthesisUtterance(chunkText);
            window._activeTTSUtterance = this.activeUtterance;

            if (!this.bengaliVoice) {
                this.initVoices();
            }

            if (this.bengaliVoice) {
                this.activeUtterance.voice = this.bengaliVoice;
                this.activeUtterance.lang = this.bengaliVoice.lang || 'bn-BD';
            } else {
                this.activeUtterance.lang = 'bn-BD';
            }

            this.activeUtterance.rate = this.ttsRate;
            this.activeUtterance.pitch = this.ttsPitch;
            this.activeUtterance.volume = this.isMuted ? 0 : 1.0;

            this.activeUtterance.onend = () => {
                this.clearWatchdog();
                if (this.isSpeaking && !this.isPaused) {
                    this.currentChunkIndex++;
                    this.playNextChunk();
                }
            };

            this.activeUtterance.onerror = (e) => {
                this.clearWatchdog();
                console.warn('IdeaAudiobook chunk issue:', e);
                if (this.isSpeaking && !this.isPaused) {
                    this.currentChunkIndex++;
                    this.playNextChunk();
                }
            };

            // Watchdog timer to ensure it never stops after 2 lines
            const wordsCount = chunkText.split(/\s+/).length;
            const expectedDurationMs = Math.max(4500, (wordsCount * 750 * (1 / this.ttsRate)) + 3500);
            this.watchdogTimer = setTimeout(() => {
                if (this.isSpeaking && !this.isPaused) {
                    console.info('Advancing TTS via watchdog timer.');
                    this.currentChunkIndex++;
                    this.playNextChunk();
                }
            }, expectedDurationMs);

            try {
                this.synth.speak(this.activeUtterance);
            } catch (err) {
                console.error('Speech synthesis error:', err);
                this.currentChunkIndex++;
                this.playNextChunk();
            }
        },

        startArticle: function(containerSelector = '#articleBody', titleSelector = '.lit-title', storageKey = null) {
            if (!('speechSynthesis' in window) || !this.synth) {
                this.showToast('আপনার ব্রাউজারে অডিও স্পিচ সাপোর্ট পাওয়া যায়নি।', 'fa-solid fa-triangle-exclamation text-warning');
                return;
            }

            if (this.isSpeaking) {
                this.stop(true);
                return;
            }

            this.initVoices();
            this.activeStorageKey = storageKey;
            this.speechChunks = this.extractChunksFromDOM(containerSelector, titleSelector);

            if (this.speechChunks.length === 0) {
                this.showToast('পড়ার মতো পর্যাপ্ত লেখা পাওয়া যায়নি।', 'fa-solid fa-triangle-exclamation text-warning');
                return;
            }

            let startIndex = 0;
            if (this.activeStorageKey) {
                const saved = parseInt(localStorage.getItem(this.activeStorageKey));
                if (!isNaN(saved) && saved > 2 && saved < this.speechChunks.length - 2) {
                    startIndex = saved;
                    this.showToast(`পূর্বে পঠিত ${this.toBengaliDigits(Math.round((startIndex / this.speechChunks.length) * 100))}% থেকে চালু হচ্ছে...`, 'fa-solid fa-clock-rotate-left text-info');
                } else {
                    this.showToast('অডিওবুক পাঠ শুরু হয়েছে...', 'fa-solid fa-volume-high text-primary');
                }
            } else {
                this.showToast('অডিওবুক পাঠ শুরু হয়েছে...', 'fa-solid fa-volume-high text-primary');
            }

            this.currentChunkIndex = startIndex;
            this.isSpeaking = true;
            this.isPaused = false;

            this.updateUI();

            try { this.synth.cancel(); } catch (e) {}
            setTimeout(() => {
                if (this.isSpeaking) {
                    this.playNextChunk();
                }
            }, 80);
        },

        // Quick listen for cards/excerpts anywhere
        speakExcerpt: function(rawText, title = '') {
            if (!('speechSynthesis' in window) || !this.synth) {
                this.showToast('আপনার ব্রাউজারে অডিও স্পিচ সাপোর্ট পাওয়া যায়নি।', 'fa-solid fa-triangle-exclamation text-warning');
                return;
            }

            this.initVoices();
            let fullText = (title ? (title + '। ') : '') + rawText;
            const sentences = this.splitIntoSmartSentences(fullText, 95);

            if (!sentences.length) {
                this.showToast('পড়ার মতো পর্যাপ্ত লেখা পাওয়া যায়নি।', 'fa-solid fa-triangle-exclamation text-warning');
                return;
            }

            this.speechChunks = sentences.map(s => ({ text: s, el: null }));
            this.activeStorageKey = null;
            this.currentChunkIndex = 0;
            this.isSpeaking = true;
            this.isPaused = false;

            this.updateUI();
            this.showToast(title ? `"${title}" এর সারসংক্ষেপ পাঠ শুরু হয়েছে...` : 'সারসংক্ষেপ পাঠ শুরু হয়েছে...', 'fa-solid fa-headphones text-info');

            try { this.synth.cancel(); } catch (e) {}
            setTimeout(() => {
                if (this.isSpeaking) {
                    this.playNextChunk();
                }
            }, 80);
        },

        listenToElement: function(element) {
            if (!this.synth) return;
            this.speechChunks = this.extractChunksFromDOM();
            if (!this.speechChunks.length) return;

            let targetIdx = this.speechChunks.findIndex(c => c.el === element);
            if (targetIdx === -1) targetIdx = 0;

            this.currentChunkIndex = targetIdx;
            this.isSpeaking = true;
            this.isPaused = false;
            this.updateUI();
            this.showToast('অনুচ্ছেদ থেকে পাঠ শুরু হচ্ছে...', 'fa-solid fa-volume-high text-primary');
            this.playNextChunk();
        },

        speakSelectedText: function() {
            const selection = window.getSelection();
            const selectedText = selection ? selection.toString().trim() : '';
            const tooltip = document.getElementById('selectionAudioTooltip');
            if (tooltip) tooltip.style.display = 'none';

            if (!selectedText || !this.synth) return;

            this.speechChunks = [{ text: this.cleanAndNormalizeText(selectedText), el: null }];
            this.activeStorageKey = null;
            this.currentChunkIndex = 0;
            this.isSpeaking = true;
            this.isPaused = false;
            this.updateUI();
            this.showToast('বাছাইকৃত অংশ পড়া হচ্ছে...', 'fa-solid fa-headphones text-info');
            this.playNextChunk();
        },

        togglePlayPause: function() {
            if (!this.isSpeaking || !this.synth) return;

            if (this.isPaused) {
                this.isPaused = false;
                this.updateUI();
                this.showToast('পাঠ আবার শুরু হচ্ছে...', 'fa-solid fa-play text-primary');
                this.playNextChunk();
            } else {
                this.isPaused = true;
                this.clearWatchdog();
                try { this.synth.cancel(); } catch (e) {}
                this.updateUI();
                this.showToast('পাঠ সাময়িক স্থগিত (Pause) করা হয়েছে।', 'fa-solid fa-pause text-warning');
            }
        },

        prev: function() {
            if (!this.isSpeaking) return;
            this.currentChunkIndex = Math.max(0, this.currentChunkIndex - 1);
            this.isPaused = false;
            this.playNextChunk();
        },

        next: function() {
            if (!this.isSpeaking) return;
            if (this.currentChunkIndex + 1 < this.speechChunks.length) {
                this.currentChunkIndex++;
                this.isPaused = false;
                this.playNextChunk();
            } else {
                this.stop(true);
            }
        },

        stop: function(showNotice = true) {
            this.clearWatchdog();
            this.clearSleepTimer();
            this.isSpeaking = false;
            this.isPaused = false;
            this.speechChunks = [];
            this.currentChunkIndex = 0;
            this.activeUtterance = null;
            window._activeTTSUtterance = null;

            if (this.synth) {
                try { this.synth.cancel(); } catch (e) {}
            }

            this.updateUI();

            if (showNotice) {
                this.showToast('অডিওবুক পাঠ বন্ধ করা হয়েছে।', 'fa-solid fa-circle-stop text-secondary');
            }
        },

        handleScrubClick: function(e) {
            if (!this.isSpeaking || this.speechChunks.length === 0) return;
            const scrubContainer = document.getElementById('ttsScrubContainer');
            if (!scrubContainer) return;

            const rect = scrubContainer.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const ratio = Math.max(0, Math.min(1, clickX / rect.width));
            const targetIndex = Math.floor(ratio * this.speechChunks.length);

            this.currentChunkIndex = Math.min(targetIndex, this.speechChunks.length - 1);
            this.isPaused = false;
            this.playNextChunk();
            this.showToast(`জাম্প করা হয়েছে: ${this.toBengaliDigits(Math.round(ratio * 100))}%`, 'fa-solid fa-forward-step text-info');
        },

        setSpeed: function(rate, label) {
            this.ttsRate = parseFloat(rate) || 1.0;
            const speedLabel = document.getElementById('dockSpeedLabel');
            if (speedLabel) speedLabel.textContent = label;

            const dock = document.getElementById('audiobookFloatingDock');
            if (dock) {
                dock.querySelectorAll('.dropdown-item').forEach(item => {
                    if (item.textContent.includes(label)) item.classList.add('active');
                    else if (item.textContent.includes('x')) item.classList.remove('active');
                });
            }

            this.showToast(`পড়ার গতি পরিবর্তন: ${label}`, 'fa-solid fa-gauge-high text-info');

            if (this.isSpeaking && !this.isPaused) {
                this.playNextChunk();
            }
        },

        setPitch: function(pitchVal, label) {
            this.ttsPitch = parseFloat(pitchVal) || 1.0;
            this.showToast(`কণ্ঠের স্বর: ${label}`, 'fa-solid fa-sliders text-info');
            if (this.isSpeaking && !this.isPaused) {
                this.playNextChunk();
            }
        },

        setVoice: function(type) {
            this.preferredVoiceType = type;
            this.initVoices();

            const vCheckBD = document.getElementById('vCheckBD');
            const vCheckIN = document.getElementById('vCheckIN');
            const vCheckDef = document.getElementById('vCheckDef');

            if (vCheckBD) vCheckBD.classList.toggle('d-none', type !== 'bd');
            if (vCheckIN) vCheckIN.classList.toggle('d-none', type !== 'in');
            if (vCheckDef) vCheckDef.classList.toggle('d-none', type !== 'default');

            const voiceName = (this.bengaliVoice ? this.bengaliVoice.name : 'ডিফল্ট');
            this.showToast(`ভয়েস পরিবর্তন: ${voiceName}`, 'fa-solid fa-microphone text-info');

            if (this.isSpeaking && !this.isPaused) {
                this.playNextChunk();
            }
        },

        toggleMute: function() {
            this.isMuted = !this.isMuted;
            const icon = document.getElementById('dockMuteIcon');
            if (icon) {
                icon.className = this.isMuted ? 'fa-solid fa-volume-xmark text-danger' : 'fa-solid fa-volume-high text-light';
            }
            if (this.activeUtterance) {
                this.activeUtterance.volume = this.isMuted ? 0 : 1.0;
            }
            this.showToast(this.isMuted ? 'শব্দ মিউট করা হয়েছে (Muted)' : 'শব্দ আনমিউট করা হয়েছে', 'fa-solid fa-volume-high text-info');
        },

        toggleMinimize: function() {
            this.isMinimized = !this.isMinimized;
            const dock = document.getElementById('audiobookFloatingDock');
            const icon = document.getElementById('dockMinIcon');
            if (dock) dock.classList.toggle('minimized', this.isMinimized);
            if (icon) icon.className = this.isMinimized ? 'fa-solid fa-expand text-light' : 'fa-solid fa-compress text-light';
        },

        clearSleepTimer: function() {
            if (this.sleepTimerInterval) {
                clearInterval(this.sleepTimerInterval);
                this.sleepTimerInterval = null;
            }
            this.sleepRemainingSec = 0;
            const label = document.getElementById('dockTimerLabel');
            if (label) label.textContent = 'টাইমার';
        },

        setSleepTimer: function(minutes, label) {
            this.clearSleepTimer();
            if (minutes <= 0) {
                this.showToast('স্লিপ টাইমার বন্ধ করা হয়েছে।', 'fa-regular fa-clock text-secondary');
                return;
            }

            this.sleepRemainingSec = minutes * 60;
            const labelEl = document.getElementById('dockTimerLabel');
            if (labelEl) labelEl.textContent = label;

            this.showToast(`স্লিপ টাইমার চালু: ${label}`, 'fa-regular fa-clock text-warning');

            this.sleepTimerInterval = setInterval(() => {
                this.sleepRemainingSec--;
                if (this.sleepRemainingSec <= 0) {
                    this.clearSleepTimer();
                    this.stop(true);
                    this.showToast('স্লিপ টাইমারের সময় শেষ হওয়ায় পাঠ বন্ধ হয়েছে।', 'fa-solid fa-moon text-primary');
                } else if (labelEl && this.sleepRemainingSec % 60 === 0) {
                    labelEl.textContent = `${this.toBengaliDigits(Math.ceil(this.sleepRemainingSec / 60))} মি.`;
                }
            }, 1000);
        },

        setupSelectionListener: function() {
            document.addEventListener('selectionchange', () => {
                const selection = window.getSelection();
                const tooltip = document.getElementById('selectionAudioTooltip');
                if (!tooltip) return;

                if (selection && selection.rangeCount > 0 && !selection.isCollapsed) {
                    const range = selection.getRangeAt(0);
                    const text = selection.toString().trim();
                    if (text.length >= 4) {
                        const rect = range.getBoundingClientRect();
                        tooltip.style.left = `${Math.max(10, rect.left + (rect.width / 2) - 60 + window.scrollX)}px`;
                        tooltip.style.top = `${rect.top + window.scrollY - 40}px`;
                        tooltip.style.display = 'inline-flex';
                        return;
                    }
                }
                tooltip.style.display = 'none';
            });
        },

        setupKeyboardShortcuts: function() {
            document.addEventListener('keydown', (e) => {
                const activeTag = document.activeElement ? document.activeElement.tagName : '';
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag) || (document.activeElement && document.activeElement.isContentEditable)) {
                    return;
                }

                if (this.isSpeaking) {
                    if (e.code === 'Space') {
                        e.preventDefault();
                        this.togglePlayPause();
                    } else if (e.code === 'ArrowLeft') {
                        e.preventDefault();
                        this.prev();
                    } else if (e.code === 'ArrowRight') {
                        e.preventDefault();
                        this.next();
                    } else if (e.key === 'm' || e.key === 'M') {
                        e.preventDefault();
                        this.toggleMute();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        this.stop(true);
                    }
                }
            });

            window.addEventListener('beforeunload', () => {
                if (this.isSpeaking && this.synth) {
                    try { this.synth.cancel(); } catch (e) {}
                }
            });
        }
    };

    // Auto initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => IdeaAudiobook.init());
    } else {
        IdeaAudiobook.init();
    }

    window.IdeaAudiobook = IdeaAudiobook;

    // Backward-compatible global bindings for blade templates
    window.toggleArticleAudio = function(container = '#articleBody', title = '.lit-title', storageKey = null) {
        IdeaAudiobook.startArticle(container, title, storageKey);
    };
    window.togglePlayPauseTTS = function() { IdeaAudiobook.togglePlayPause(); };
    window.prevTTSSentence = function() { IdeaAudiobook.prev(); };
    window.nextTTSSentence = function() { IdeaAudiobook.next(); };
    window.stopArticleAudio = function(notice = true) { IdeaAudiobook.stop(notice); };
    window.setTTSSpeed = function(rate, label) { IdeaAudiobook.setSpeed(rate, label); };
    window.setTTSPitch = function(val, label) { IdeaAudiobook.setPitch(val, label); };
    window.setTTSVoicePreferred = function(type) { IdeaAudiobook.setVoice(type); };
    window.toggleTTSMute = function() { IdeaAudiobook.toggleMute(); };
    window.toggleDockMinimize = function() { IdeaAudiobook.toggleMinimize(); };
    window.setTTSSleepTimer = function(mins, label) { IdeaAudiobook.setSleepTimer(mins, label); };
    window.handleScrubClick = function(e) { IdeaAudiobook.handleScrubClick(e); };
    window.speakSelectedText = function() { IdeaAudiobook.speakSelectedText(); };
    window.speakCardExcerpt = function(text, title) { IdeaAudiobook.speakExcerpt(text, title); };

})(window, document);
