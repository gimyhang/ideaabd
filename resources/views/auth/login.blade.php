@extends('layouts.app')
@section('title', 'লগইন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header border-0 py-4 text-center text-white position-relative" 
             style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);">
            <div class="bg-white rounded-circle p-2 d-inline-flex shadow-sm mb-2" style="width: 68px; height: 68px;">
                <img src="{{ \App\Support\SiteSetting::loginLogoUrl() }}" class="w-100 h-100 object-fit-contain" alt="{{ \App\Support\SiteSetting::siteName() }}">
            </div>
            <h3 class="fw-bold mb-1 text-white">আপনার অ্যাকাউন্টে লগইন করুন</h3>
            <small class="text-white-50" style="font-size: 0.85rem;">আইডিয়া প্রকাশন ডিজিটাল প্ল্যাটফর্ম</small>
        </div>

        <div class="card-body p-4 p-md-4.5">
            {{-- Error Alerts --}}
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger">
                    <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> লগইন ব্যর্থ হয়েছে:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                @csrf

                {{-- Invisible Honeypot Anti-Bot Security Field --}}
                <div style="display:none !important; visibility:hidden; position:absolute; left:-9999px;" aria-hidden="true">
                    <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                    <input type="checkbox" name="b_check_field" tabindex="-1" autocomplete="off">
                </div>

                {{-- Identity / Username / Email / Phone --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark mb-1" style="font-size: 15px;">
                        <i class="fa-solid fa-user text-primary me-1"></i> Email, Phone or Username <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 fs-5"><i class="fa-solid fa-user-shield text-muted"></i></span>
                        <input type="text" name="email" id="loginEmailInput" class="form-control form-control-lg rounded-end-3" 
                               value="{{ old('email') }}" placeholder="Enter email, phone or username..." 
                               required autofocus autocomplete="username" 
                               style="font-size: 15px; height: 48px;"
                               autocorrect="off" autocapitalize="none" spellcheck="false">
                    </div>
                </div>

                {{-- Password with Show/Hide Toggle Eye --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-bold text-dark mb-0" style="font-size: 15px;">
                            <i class="fa-solid fa-lock text-primary me-1"></i> Password <span class="text-danger">*</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-decoration-none text-primary fw-semibold" style="font-size: 13px;">
                            <i class="fa-solid fa-key me-0.5"></i> Forgot Password?
                        </a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 fs-5"><i class="fa-solid fa-key text-muted"></i></span>
                        <input type="password" name="password" id="loginPasswordInput" class="form-control form-control-lg" 
                               placeholder="Enter your password..." required autocomplete="current-password"
                               style="font-size: 15px; height: 48px;">
                        <button type="button" class="btn btn-outline-secondary rounded-end-3" id="toggleLoginPasswordBtn" 
                                onclick="togglePasswordVisibility('loginPasswordInput', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                            <i class="fa-regular fa-eye fs-5"></i>
                        </button>
                    </div>
                </div>

                {{-- Interactive Visual Sign & Image Challenge (Triggered for 3+ Failed Attempts / Security Issues) --}}
                @php
                    $isVisualChallengeActive = !empty($requiresVisualChallenge) || (isset($ipStatus['requires_visual_challenge']) && $ipStatus['requires_visual_challenge']) || $errors->has('visual_challenge');
                @endphp

                <div id="visualChallengeContainer" class="{{ $isVisualChallengeActive ? '' : 'd-none' }} mb-4">
                    <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%); border: 1.5px solid #cbd5e1 !important;">
                        <div class="card-header border-0 py-3 px-3.5 bg-white d-flex align-items-center justify-content-between border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white shadow-2xs">
                                    <i class="fa-solid fa-shield-halved me-1"></i> সিকিউরিটি ভেরিফিকেশন
                                </span>
                                <span class="fw-bold text-dark" style="font-size: 13.5px;">মানুষ প্রমাণ (Human Check)</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" 
                                    id="refreshVisualChallengeBtn" onclick="fetchNewVisualChallenge(this)" title="নতুন ছবি লোড করুন" style="font-size: 11.5px;">
                                <i class="fa-solid fa-rotate me-1" id="refreshVisualIcon"></i> রিলোড
                            </button>
                        </div>

                        <div class="card-body p-3.5">
                            {{-- Instruction Banner --}}
                            <div class="p-2.5 rounded-3 mb-3 text-center d-flex align-items-center justify-content-center gap-2" 
                                 id="visualChallengeHeaderBadge"
                                 style="background: {{ $visualChallenge['target_color'] ?? '#0284c7' }}15; border: 1.5px dashed {{ $visualChallenge['target_color'] ?? '#0284c7' }};">
                                <i class="fa-solid {{ $visualChallenge['target_icon'] ?? 'fa-eye' }} fs-5" 
                                   id="visualTargetIcon" 
                                   style="color: {{ $visualChallenge['target_color'] ?? '#0284c7' }};"></i>
                                <div class="text-start">
                                    <div class="fw-bold text-dark" style="font-size: 13.5px;" id="visualInstructionText">
                                        নিচের ছবিগুলো থেকে সব <strong>{{ $visualChallenge['target_title'] ?? 'নির্দিষ্ট সাইন' }}</strong> চিহ্নিত করুন:
                                    </div>
                                    <small class="text-muted" style="font-size: 11.5px;" id="visualInstructionSub">
                                        {{ $visualChallenge['target_desc'] ?? 'সঠিক ৩টি ছবিতে ক্লিক করে মানুষ প্রমাণ করুন' }}
                                    </small>
                                </div>
                            </div>

                            {{-- 3x3 Visual Tiles Grid --}}
                            <div class="row g-2 text-center" id="visualTilesGrid">
                                @if(!empty($visualChallenge['tiles']))
                                    @foreach($visualChallenge['tiles'] as $tile)
                                        <div class="col-4">
                                            <div class="visual-tile position-relative p-2.5 rounded-3 bg-white border cursor-pointer transition-all shadow-2xs h-100 d-flex flex-column align-items-center justify-content-center"
                                                 data-index="{{ $tile['index'] }}"
                                                 onclick="toggleVisualTile(this)"
                                                 style="min-height: 84px; border-color: #cbd5e1; user-select: none;">
                                                <div class="tile-check-badge position-absolute top-0 end-0 m-1 rounded-circle bg-success text-white d-none align-items-center justify-content-center shadow-2xs" 
                                                     style="width: 20px; height: 20px; font-size: 11px;">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                                <div class="tile-icon text-secondary mb-1" style="font-size: 26px;">
                                                    <i class="fa-solid {{ $tile['icon'] }}"></i>
                                                </div>
                                                <div class="tile-label fw-semibold text-dark text-truncate w-100" style="font-size: 11px;">
                                                    {{ $tile['label'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            {{-- Challenge Feedback Status --}}
                            <div id="visualChallengeFeedback" class="mt-2.5 text-center small fw-semibold d-none"></div>

                            {{-- Hidden JSON array of selected indices --}}
                            <input type="hidden" name="visual_selected_indices" id="visualSelectedIndicesInput" value="[]">

                            <div class="d-flex align-items-center justify-content-between gap-2 mt-3 pt-2 border-top">
                                <span class="text-muted" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-circle-info text-primary me-0.5"></i> ৩টি সঠিক ছবি নির্বাচন করুন
                                </span>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" id="verifyVisualBtn" onclick="verifyVisualSelection(this)">
                                    <i class="fa-solid fa-shield-check me-1"></i> যাচাই করুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Human Bot Math Security Challenge (Fallback for Standard Login) --}}
                @php
                    $b1 = $botNum1 ?? random_int(3, 8);
                    $b2 = $botNum2 ?? random_int(1, 6);
                @endphp
                <div class="rounded-4 border mb-4 shadow-2xs p-3 p-sm-3.5 {{ $isVisualChallengeActive ? 'd-none' : '' }}" id="botChallengeBox" 
                     style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border-color: #cbd5e1 !important;">
                    <div class="d-flex align-items-center justify-content-between gap-2 gap-sm-3 flex-wrap flex-sm-nowrap">
                        {{-- Left Column: Row 1 = Equation, Row 2 = যোগফল লিখুন --}}
                        <div class="d-flex flex-column justify-content-center ps-1 ps-sm-2">
                            <div class="text-primary font-monospace fw-bold text-nowrap" id="botEquationText" 
                                 style="font-size: clamp(18px, 4.5vw, 22px); letter-spacing: 0.8px; line-height: 1.2;">
                                {{ $b1 }} + {{ $b2 }} = ?
                            </div>
                            <div class="text-secondary fw-semibold text-nowrap mt-0.5" style="font-size: clamp(11.5px, 3vw, 13px); color: #475569 !important;">
                                যোগফল লিখুন
                            </div>
                        </div>

                        {{-- Right Column: Merged Answer Input & Refresh Button --}}
                        <div class="d-flex align-items-center gap-1.5 gap-sm-2 ms-auto pe-1">
                            <input type="number" name="bot_answer" id="botAnswerInput" 
                                   class="form-control form-control-lg text-center fw-bold font-monospace bg-white border shadow-2xs px-1 px-sm-2" 
                                   style="width: 110px; min-width: 90px; max-width: 130px; font-size: clamp(16px, 4vw, 19px); height: 46px; border-radius: 10px;" 
                                   placeholder="যোগফল" {{ $isVisualChallengeActive ? '' : 'required' }} min="0" max="40" autocomplete="off">
                            <button type="button" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                                    id="refreshBotBtn" onclick="refreshBotChallenge(this)" title="নতুন সংখ্যা পেতে ক্লিক করুন" 
                                    style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-rotate" id="refreshBotIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1" @checked(old('remember'))>
                        <label for="remember" class="form-check-label small text-muted cursor-pointer" style="font-size: 13px;">Remember Me</label>
                    </div>
                    <span class="text-muted" style="font-size: 12px;">
                        <i class="fa-solid fa-shield-check text-success me-0.5"></i> 256-bit SSL Secure
                    </span>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-pill shadow-sm fs-5" id="loginSubmitBtn">
                    <i class="fa-solid fa-right-to-bracket me-1.5"></i> Login / লগইন করুন
                </button>
            </form>

            {{-- Prominent Registration / Sign Up Callout --}}
            <div class="mt-4 pt-3 border-top">
                <div class="p-3 bg-light rounded-4 border shadow-2xs">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <span class="fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size: clamp(14px, 3.8vw, 17px);">
                            <i class="fa-solid fa-user-plus text-primary"></i>
                            <span>আপনার একাউন্ট না থাকলে সাইন আপ করুন:</span>
                        </span>
                        <a href="{{ route('register.choose') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none px-3 py-1.5 rounded-pill fw-bold" style="font-size: 13px;">
                            সবগুলো <i class="fa-solid fa-angle-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="row g-2 text-center">
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'author') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-success mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-feather-pointed"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">লেখক</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">লেখা প্রকাশ</div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'buyer') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-warning mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-bag-shopping"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">পাঠক/বায়ার</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">বই ক্রয়</div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'publisher') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-danger mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-building"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">প্রকাশক</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">প্রকাশনী</div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'seller') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-primary mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-store"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">সেলার</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">বই বিক্রি</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.visual-tile {
    cursor: pointer;
    border-radius: 12px !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.visual-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border-color: #94a3b8 !important;
}
.visual-tile.selected {
    background: #eff6ff !important;
    border-color: #0284c7 !important;
    border-width: 2px !important;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25) !important;
}
.visual-tile.selected .tile-icon {
    color: #0284c7 !important;
}
.visual-tile.selected .tile-check-badge {
    display: flex !important;
}
.visual-tile.verified-success {
    background: #f0fdf4 !important;
    border-color: #16a34a !important;
}
</style>

<script>
let selectedVisualIndices = [];
let isVisualVerified = false;

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

function toggleVisualTile(tileEl) {
    const index = parseInt(tileEl.getAttribute('data-index'), 10);
    const hiddenInput = document.getElementById('visualSelectedIndicesInput');
    const feedback = document.getElementById('visualChallengeFeedback');

    if (tileEl.classList.contains('selected')) {
        tileEl.classList.remove('selected');
        selectedVisualIndices = selectedVisualIndices.filter(i => i !== index);
    } else {
        tileEl.classList.add('selected');
        if (!selectedVisualIndices.includes(index)) {
            selectedVisualIndices.push(index);
        }
    }

    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(selectedVisualIndices);
    }
    if (feedback) {
        feedback.classList.add('d-none');
    }
}

async function verifyVisualSelection(btn) {
    const hiddenInput = document.getElementById('visualSelectedIndicesInput');
    const feedback = document.getElementById('visualChallengeFeedback');
    const verifyBtn = btn || document.getElementById('verifyVisualBtn');

    if (selectedVisualIndices.length === 0) {
        if (feedback) {
            feedback.className = 'mt-2.5 text-center small fw-semibold text-danger';
            feedback.textContent = 'অনুগ্রহ করে অন্তত ৩টি সঠিক ছবিতে ক্লিক করুন।';
            feedback.classList.remove('d-none');
        }
        return;
    }

    if (verifyBtn) {
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> যাচাই হচ্ছে...';
    }

    try {
        const res = await fetch('{{ route("login.verify-visual-challenge") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                selected: selectedVisualIndices
            })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            isVisualVerified = true;
            if (feedback) {
                feedback.className = 'mt-2.5 text-center small fw-bold text-success';
                feedback.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + data.message;
                feedback.classList.remove('d-none');
            }
            if (verifyBtn) {
                verifyBtn.className = 'btn btn-sm btn-success rounded-pill px-3 fw-bold';
                verifyBtn.innerHTML = '<i class="fa-solid fa-check-double me-1"></i> মানুষ যাচাই সম্পন্ন';
            }
            // Highlight selected tiles with green
            document.querySelectorAll('.visual-tile.selected').forEach(t => t.classList.add('verified-success'));
        } else {
            isVisualVerified = false;
            if (feedback) {
                feedback.className = 'mt-2.5 text-center small fw-semibold text-danger';
                feedback.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (data.message || 'যাচাইকরণ সঠিক হয়নি।');
                feedback.classList.remove('d-none');
            }
            if (data.new_challenge) {
                renderVisualChallenge(data.new_challenge);
            }
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fa-solid fa-shield-check me-1"></i> পুনরায় যাচাই করুন';
            }
        }
    } catch (e) {
        console.error('Visual verification error', e);
        if (feedback) {
            feedback.className = 'mt-2.5 text-center small fw-semibold text-danger';
            feedback.textContent = 'সার্ভার সমস্যা হয়েছে। আবার চেষ্টা করুন।';
            feedback.classList.remove('d-none');
        }
        if (verifyBtn) {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = '<i class="fa-solid fa-shield-check me-1"></i> যাচাই করুন';
        }
    }
}

async function fetchNewVisualChallenge(btn) {
    const icon = document.getElementById('refreshVisualIcon');
    if (icon) icon.classList.add('fa-spin');
    if (btn) btn.disabled = true;

    try {
        const res = await fetch('{{ route("login.visual-challenge") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data && data.success && data.challenge) {
            renderVisualChallenge(data.challenge);
        }
    } catch (e) {
        console.error('Failed to load visual challenge', e);
    } finally {
        if (icon) icon.classList.remove('fa-spin');
        if (btn) btn.disabled = false;
    }
}

function renderVisualChallenge(c) {
    selectedVisualIndices = [];
    isVisualVerified = false;
    const hiddenInput = document.getElementById('visualSelectedIndicesInput');
    if (hiddenInput) hiddenInput.value = '[]';

    const headerBadge = document.getElementById('visualChallengeHeaderBadge');
    const targetIcon = document.getElementById('visualTargetIcon');
    const instructionText = document.getElementById('visualInstructionText');
    const instructionSub = document.getElementById('visualInstructionSub');
    const grid = document.getElementById('visualTilesGrid');
    const feedback = document.getElementById('visualChallengeFeedback');
    const verifyBtn = document.getElementById('verifyVisualBtn');

    if (headerBadge) {
        headerBadge.style.background = (c.target_color || '#0284c7') + '15';
        headerBadge.style.borderColor = (c.target_color || '#0284c7');
    }
    if (targetIcon) {
        targetIcon.className = 'fa-solid ' + (c.target_icon || 'fa-eye') + ' fs-5';
        targetIcon.style.color = (c.target_color || '#0284c7');
    }
    if (instructionText) {
        instructionText.innerHTML = 'নিচের ছবিগুলো থেকে সব <strong>' + c.target_title + '</strong> চিহ্নিত করুন:';
    }
    if (instructionSub) {
        instructionSub.textContent = c.target_desc || 'সঠিক ৩টি ছবিতে ক্লিক করে মানুষ প্রমাণ করুন';
    }
    if (feedback) {
        feedback.classList.add('d-none');
        feedback.textContent = '';
    }
    if (verifyBtn) {
        verifyBtn.disabled = false;
        verifyBtn.className = 'btn btn-sm btn-primary rounded-pill px-3 fw-bold';
        verifyBtn.innerHTML = '<i class="fa-solid fa-shield-check me-1"></i> যাচাই করুন';
    }

    if (grid && c.tiles) {
        grid.innerHTML = '';
        c.tiles.forEach(tile => {
            const col = document.createElement('div');
            col.className = 'col-4';
            col.innerHTML = `
                <div class="visual-tile position-relative p-2.5 rounded-3 bg-white border cursor-pointer transition-all shadow-2xs h-100 d-flex flex-column align-items-center justify-content-center"
                     data-index="${tile.index}"
                     onclick="toggleVisualTile(this)"
                     style="min-height: 84px; border-color: #cbd5e1; user-select: none;">
                    <div class="tile-check-badge position-absolute top-0 end-0 m-1 rounded-circle bg-success text-white d-none align-items-center justify-content-center shadow-2xs" 
                         style="width: 20px; height: 20px; font-size: 11px;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="tile-icon text-secondary mb-1" style="font-size: 26px;">
                        <i class="fa-solid ${tile.icon}"></i>
                    </div>
                    <div class="tile-label fw-semibold text-dark text-truncate w-100" style="font-size: 11px;">
                        ${tile.label}
                    </div>
                </div>
            `;
            grid.appendChild(col);
        });
    }
}

async function refreshBotChallenge(btn) {
    const icon = document.getElementById('refreshBotIcon');
    const eqText = document.getElementById('botEquationText');
    const ansInput = document.getElementById('botAnswerInput');
    const challengeBox = document.getElementById('botChallengeBox');

    if (icon) icon.classList.add('fa-spin');
    if (btn) btn.disabled = true;

    try {
        const res = await fetch('{{ route("login.refresh-bot") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data && data.success) {
            if (eqText) eqText.textContent = data.equation;
            if (ansInput) {
                ansInput.value = '';
                ansInput.focus();
            }
            if (challengeBox) {
                challengeBox.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => challengeBox.classList.remove('animate__animated', 'animate__pulse'), 1000);
            }
        }
    } catch (e) {
        console.error('Failed to refresh bot challenge', e);
    } finally {
        if (icon) icon.classList.remove('fa-spin');
        if (btn) btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginSubmitBtn');
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            const visualContainer = document.getElementById('visualChallengeContainer');
            if (visualContainer && !visualContainer.classList.contains('d-none')) {
                const hiddenInput = document.getElementById('visualSelectedIndicesInput');
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(selectedVisualIndices);
                }
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Checking & Logging in...';
        });
    }
});
</script>
@endsection
