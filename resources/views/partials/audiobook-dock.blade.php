{{-- Universal World-Class Bengali Audiobook Player Dock & Selection Tooltip --}}
<style>
    /* Global Audiobook Floating Dock */
    #audiobookFloatingDock {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(120%);
        z-index: 1060;
        background: rgba(15, 23, 42, 0.96);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        border-radius: 24px;
        padding: 10px 18px;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255,255,255,0.08);
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
        opacity: 0;
        pointer-events: none;
        max-width: 95vw;
        width: 580px;
    }
    #audiobookFloatingDock.active {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    #audiobookFloatingDock.minimized {
        width: auto;
        padding: 6px 14px;
        border-radius: 50rem;
    }
    #audiobookFloatingDock.minimized .audio-dock-expanded-content {
        display: none !important;
    }
    .audio-dock-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .audio-dock-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.08);
        color: #fff;
    }
    .audio-dock-btn.primary {
        background: #0284c7;
        width: 38px;
        height: 38px;
        font-size: 1.05rem;
        box-shadow: 0 2px 12px rgba(2, 132, 199, 0.5);
    }
    .audio-dock-btn.primary:hover {
        background: #0369a1;
        transform: scale(1.1);
    }
    .audio-dock-pill {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        border-radius: 50rem;
        padding: 3px 9px;
        font-size: 0.74rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .audio-dock-pill:hover {
        background: rgba(255, 255, 255, 0.24);
        color: #fff;
    }
    
    /* Interactive Progress Scrub Bar */
    .tts-scrub-container {
        width: 100%;
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        position: relative;
        cursor: pointer;
        overflow: hidden;
        transition: height 0.18s ease;
    }
    .tts-scrub-container:hover {
        height: 9px;
    }
    .tts-scrub-fill {
        height: 100%;
        background: linear-gradient(90deg, #38bdf8, #0284c7);
        width: 0%;
        border-radius: 3px;
        transition: width 0.15s linear;
    }

    /* Selection Floating Button */
    #selectionAudioTooltip {
        position: absolute;
        z-index: 1080;
        background: #0f172a;
        color: #ffffff;
        border-radius: 50rem;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        box-shadow: 0 6px 18px rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.2);
        cursor: pointer;
        display: none;
        align-items: center;
        gap: 6px;
        animation: fadeIn 0.18s ease;
    }
    #selectionAudioTooltip:hover {
        background: #0284c7;
        transform: translateY(-2px);
    }

    /* Live Synchronized Highlight */
    .tts-live-highlight {
        background-color: rgba(254, 240, 138, 0.45) !important;
        border-radius: 6px;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.35);
        transition: background-color 0.3s ease;
    }

    /* Quick Card Listen Button */
    .btn-quick-audio {
        background: rgba(2, 132, 199, 0.08);
        color: #0284c7;
        border: 1px solid rgba(2, 132, 199, 0.22);
        border-radius: 50rem;
        padding: 2px 9px;
        font-size: 0.74rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }
    .btn-quick-audio:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        transform: scale(1.04);
    }
</style>

<!-- Floating Selected Text Audio Tooltip -->
<div id="selectionAudioTooltip" onclick="IdeaAudiobook.speakSelectedText()" role="button" title="নির্বাচিত অংশটি শুনুন">
    <i class="fa-solid fa-headphones text-info"></i>
    <span>নির্বাচিত অংশ শুনুন</span>
</div>

<!-- Modern Floating Audiobook Control Dock -->
<div id="audiobookFloatingDock" class="no-print shadow-lg" role="region" aria-label="অডিওবুক কন্ট্রোল">
    <!-- Interactive Timeline Scrub Bar -->
    <div class="tts-scrub-container audio-dock-expanded-content" id="ttsScrubContainer" onclick="IdeaAudiobook.handleScrubClick(event)" title="যেকোনো বাক্যে সরাসরি জাম্প করতে ক্লিক করুন">
        <div class="tts-scrub-fill" id="ttsScrubFill"></div>
    </div>

    <!-- Main Controls Row -->
    <div class="d-flex align-items-center justify-content-between gap-2 w-100">
        <!-- Playback Navigation Buttons -->
        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
            <button type="button" class="audio-dock-btn" onclick="IdeaAudiobook.prev()" title="পূর্ববর্তী বাক্য (⏮) বা বাম তীর চিহ্ন">
                <i class="fa-solid fa-backward-step"></i>
            </button>
            <button type="button" class="audio-dock-btn primary" id="dockPlayPauseBtn" onclick="IdeaAudiobook.togglePlayPause()" title="প্লে / পজ (Spacebar)">
                <i class="fa-solid fa-pause" id="dockPlayPauseIcon"></i>
            </button>
            <button type="button" class="audio-dock-btn" onclick="IdeaAudiobook.next()" title="পরবর্তী বাক্য (⏭) বা ডান তীর চিহ্ন">
                <i class="fa-solid fa-forward-step"></i>
            </button>
            <button type="button" class="audio-dock-btn text-danger" onclick="IdeaAudiobook.stop(true)" title="পাঠ বন্ধ করুন (⏹) বা Esc">
                <i class="fa-solid fa-stop"></i>
            </button>
        </div>

        <!-- Progress Counter & Estimated Time -->
        <div class="d-flex flex-column justify-content-center px-1 audio-dock-expanded-content" style="min-width: 120px; flex: 1;">
            <div class="d-flex align-items-center justify-content-between text-white-50" style="font-size: 0.70rem;">
                <span id="dockTimeEstimate">অডিওবুক পাঠ</span>
                <span id="dockProgressPercent" class="fw-bold text-info">০%</span>
            </div>
            <div class="fw-bold text-white text-truncate" id="dockSentenceCounter" style="font-size: 0.80rem;">
                বাক্য ১ / ১
            </div>
        </div>

        <!-- Action Pills (Speed, Timer, Voice, Mute, Minimize, Close) -->
        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
            <!-- Speed Selector -->
            <div class="dropdown d-inline-block audio-dock-expanded-content">
                <button type="button" class="audio-dock-pill dropdown-toggle" data-bs-toggle="dropdown" id="dockSpeedBtn" title="পড়ার গতি">
                    <i class="fa-solid fa-gauge-high text-info"></i>
                    <span id="dockSpeedLabel">১.০x</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-0 small">
                    <li><h6 class="dropdown-header text-muted small" style="font-size: 11px;">পড়ার গতি</h6></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSpeed(0.8, '০.৮x')">০.৮x (ধীর গতি)</button></li>
                    <li><button class="dropdown-item small active" type="button" onclick="IdeaAudiobook.setSpeed(1.0, '১.০x')">১.০x (স্বাভাবিক)</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSpeed(1.25, '১.২৫x')">১.২৫x (মাঝারি দ্রুত)</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSpeed(1.5, '১.৫x')">১.৫x (দ্রুত)</button></li>
                </ul>
            </div>

            <!-- Sleep Timer Dropdown -->
            <div class="dropdown d-inline-block audio-dock-expanded-content">
                <button type="button" class="audio-dock-pill dropdown-toggle" data-bs-toggle="dropdown" id="dockTimerBtn" title="স্লিপ টাইমার">
                    <i class="fa-regular fa-clock text-warning"></i>
                    <span id="dockTimerLabel">টাইমার</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-0 small">
                    <li><h6 class="dropdown-header text-muted small" style="font-size: 11px;">স্লিপ টাইমার</h6></li>
                    <li><button class="dropdown-item small active" type="button" onclick="IdeaAudiobook.setSleepTimer(0, 'বন্ধ')">টাইমার বন্ধ</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSleepTimer(5, '৫ মিনিট')">৫ মিনিট পর বন্ধ</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSleepTimer(10, '১০ মিনিট')">১০ মিনিট পর বন্ধ</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSleepTimer(15, '১৫ মিনিট')">১৫ মিনিট পর বন্ধ</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setSleepTimer(30, '৩০ মিনিট')">৩০ মিনিট পর বন্ধ</button></li>
                </ul>
            </div>

            <!-- Voice & Pitch Dropdown -->
            <div class="dropdown d-inline-block audio-dock-expanded-content">
                <button type="button" class="audio-dock-btn" data-bs-toggle="dropdown" title="কণ্ঠস্বর ও স্বর পরিবর্তন">
                    <i class="fa-solid fa-sliders text-light"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-0 small" style="min-width: 220px;" id="ttsVoiceMenuList">
                    <li><h6 class="dropdown-header text-muted small" style="font-size: 11px;">বাংলা ভয়েস ও স্বর</h6></li>
                    <li><button class="dropdown-item small d-flex align-items-center justify-content-between" type="button" onclick="IdeaAudiobook.setVoice('bd')"><span>🇧🇩 বাংলা (বাংলাদেশ)</span> <i class="fa-solid fa-check text-info ms-2" id="vCheckBD"></i></button></li>
                    <li><button class="dropdown-item small d-flex align-items-center justify-content-between" type="button" onclick="IdeaAudiobook.setVoice('in')"><span>🇮🇳 বাংলা (ভারত)</span> <i class="fa-solid fa-check text-info ms-2 d-none" id="vCheckIN"></i></button></li>
                    <li><button class="dropdown-item small d-flex align-items-center justify-content-between" type="button" onclick="IdeaAudiobook.setVoice('default')"><span>🌐 ব্রাউজার ডিফল্ট</span> <i class="fa-solid fa-check text-info ms-2 d-none" id="vCheckDef"></i></button></li>
                    <li><hr class="dropdown-divider opacity-25"></li>
                    <li><h6 class="dropdown-header text-muted small" style="font-size: 11px;">কণ্ঠের স্বর (Pitch)</h6></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setPitch(0.9, 'গম্ভীর')">গম্ভীর স্বর (Deep)</button></li>
                    <li><button class="dropdown-item small active" type="button" onclick="IdeaAudiobook.setPitch(1.0, 'স্বাভাবিক')">স্বাভাবিক স্বর (Normal)</button></li>
                    <li><button class="dropdown-item small" type="button" onclick="IdeaAudiobook.setPitch(1.15, 'উচ্চ')">উচ্চ স্বর (High)</button></li>
                </ul>
            </div>

            <!-- Mute / Unmute Button -->
            <button type="button" class="audio-dock-btn audio-dock-expanded-content" id="dockMuteBtn" onclick="IdeaAudiobook.toggleMute()" title="মিউট / আনমিউট (M)">
                <i class="fa-solid fa-volume-high text-light" id="dockMuteIcon"></i>
            </button>

            <!-- Minimize / Expand Toggle -->
            <button type="button" class="audio-dock-btn" onclick="IdeaAudiobook.toggleMinimize()" title="মিনিমাইজ / এক্সপ্যান্ড">
                <i class="fa-solid fa-compress text-light" id="dockMinIcon"></i>
            </button>

            <!-- Close Dock -->
            <button type="button" class="btn-close btn-close-white ms-0.5" style="font-size: 0.65rem;" onclick="IdeaAudiobook.stop(true)" title="বন্ধ করুন (Esc)"></button>
        </div>
    </div>
</div>
