@extends('layouts.app')

@section('title', ($campaign->type === 'scholarship' || $campaign->slug === 'jshikkhabritti' ? 'Application submitted — ' : 'ধন্যবাদ! নিবন্ধন সম্পন্ন হয়েছে — ') . $campaign->title)

@push('styles')
<style>
    .thankyou-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08), 0 4px 12px rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        position: relative;
    }
    .thankyou-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        padding: 38px 24px 30px;
        color: #ffffff;
        text-align: center;
        position: relative;
    }
    .thankyou-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.25), transparent 60%),
                    radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.2), transparent 50%);
        pointer-events: none;
    }
    .celebrate-icon-wrap {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        margin-bottom: 18px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.45);
        animation: pulseIcon 2s infinite ease-in-out;
    }
    @keyframes pulseIcon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }
    .thankyou-title {
        font-family: 'Kalpurush', 'Hind Siliguri', 'SolaimanLipi', sans-serif;
        font-weight: 800;
        font-size: 32px;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
        color: #f8fafc;
    }
    .thankyou-subtitle {
        font-size: 15px;
        color: #cbd5e1;
        max-width: 520px;
        margin: 0 auto;
        line-height: 1.55;
    }
    .info-summary-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        font-size: 14px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .info-label {
        color: #64748b;
        font-size: 13.5px;
    }
    .info-val {
        font-weight: 700;
        color: #0f172a;
    }
    .countdown-bar-wrap {
        background: #f1f5f9;
        border-radius: 30px;
        height: 6px;
        overflow: hidden;
        margin: 14px auto 0;
        max-width: 320px;
    }
    .countdown-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        width: 100%;
        transition: width 1s linear;
    }
    .btn-action-lg {
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 14.5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .btn-action-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
</style>
@endpush

@section('content')
@php
    $isSch = $campaign->type === 'scholarship' || $campaign->slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form']);
    // scholarship applicants need time to download and read the instructions before the redirect
    $redirectSecs = $isSch ? 120 : 6;
    $redirectTo = ($campaign->redirect_url && filter_var($campaign->redirect_url, FILTER_VALIDATE_URL)) ? $campaign->redirect_url : url('/');
    $customMessage = $isSch ? trim((string) $campaign->success_message) : '';
    if ($customMessage !== '' && preg_match('/\p{Bengali}/u', $customMessage)) { $customMessage = ''; }
@endphp
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            <div class="thankyou-card">
                
                {{-- Festive Header --}}
                <div class="thankyou-header">
                    <div class="celebrate-icon-wrap">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h1 class="thankyou-title">{{ $isSch ? 'Thank you!' : 'ধন্যবাদ!' }}</h1>
                    <p class="thankyou-subtitle">
                        @if($isSch)
                            Your application to <strong>{{ $campaign->title }}</strong> has been submitted.
                        @else
                            <strong>{{ $campaign->title }}</strong>-এ আপনার নিবন্ধন সম্পন্ন হয়েছে।
                        @endif
                    </p>
                </div>

                <div class="p-4 p-md-5">

                    {{-- Respectful Welcome Greeting Notice --}}
                    @if($isSch)
                        <div class="p-3.5 rounded-4 mb-4 border d-flex align-items-start gap-3" style="background: #f0fdf4; border-color: #bbf7d0 !important; color: #166534;">
                            <i class="fa-solid fa-file-signature fs-3 text-success flex-shrink-0"></i>
                            <div style="font-size: 14px; line-height: 1.6; font-weight: 600;">
                                @if($customMessage !== '')<div class="mb-1">{{ $customMessage }}</div>@endif
                                <div class="mb-1">Download your application form.</div>
                                <div>Get it signed by the head of your institute and your local councillor, and submit it on the day of the meeting.</div>
                            </div>
                        </div>
                    @else                    <div class="p-3.5 rounded-4 mb-4 border d-flex align-items-center gap-3" style="background: #f0fdf4; border-color: #bbf7d0 !important; color: #166534;">
                        <i class="fa-solid fa-envelope-open-text fs-3 text-success flex-shrink-0"></i>
                        <div style="font-size: 14px; line-height: 1.5; font-weight: 600;">
                            সাহিত্য উৎসব ও লিটিলম্যাগমেলায় আপনার অংশগ্রহণ আমাদের সম্মানিত করবে। নিচে আপনার আমন্ত্রণ কার্ড প্রস্তুত রয়েছে।
                        </div>
                    </div>
                    @endif

                    {{-- Summary Box --}}
                    @if($summary)
                        <div class="info-summary-box mb-4">
                            <div class="info-row">
                                <span class="info-label"><i class="fa-solid fa-hashtag me-1 text-muted"></i> {{ $isSch ? 'Number:' : 'রেজিস্ট্রেশন নম্বর:' }}</span>
                                <span class="info-val font-monospace text-primary fs-6">#{{ $summary['registration_number'] }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fa-solid fa-user-pen me-1 text-muted"></i> {{ $isSch ? 'Name:' : 'নাম:' }}</span>
                                <span class="info-val">{{ $summary['name'] }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fa-solid fa-phone me-1 text-muted"></i> {{ $isSch ? 'Mobile:' : 'মোবাইল:' }}</span>
                                <span class="info-val font-monospace">{{ $summary['phone'] }}</span>
                            </div>
                            @if(!empty($summary['category']))
                                <div class="info-row">
                                    <span class="info-label"><i class="fa-solid fa-feather me-1 text-muted"></i> ক্যাটাগরি:</span>
                                    <span class="badge bg-danger-subtle text-danger fw-bold">{{ $summary['category'] }}</span>
                                </div>
                            @endif
                            <div class="info-row">
                                <span class="info-label"><i class="fa-solid fa-clock me-1 text-muted"></i> {{ $isSch ? 'Date:' : 'সময়:' }}</span>
                                <span class="text-dark fw-semibold" style="font-size: 13px;">{{ $summary['created_at'] ?? now()->format('d M, Y - h:i A') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Card Download & Print Action Buttons --}}
                    @if(!empty($summary['registration_number']))
                        <div class="d-grid gap-2 d-sm-flex justify-content-center mb-4">
                            <a href="{{ $formLinks['print'] ?? route('event.registration.print', $summary['registration_number']) }}" target="_blank" class="btn btn-warning btn-action-lg shadow-sm text-dark">
                                <i class="fa-solid fa-id-card"></i> {{ $isSch ? 'Download' : 'আমন্ত্রণ কার্ড ডাউনলোড' }}
                            </a>
                            <a href="{{ $formLinks['pdf'] ?? route('event.registration.pdf', $summary['registration_number']) }}" class="btn btn-outline-danger btn-action-lg">
                                <i class="fa-solid fa-file-pdf"></i> {{ $isSch ? 'PDF' : 'কার্ড PDF' }}
                            </a>
                        </div>
                    @endif

                    <hr class="my-4" style="opacity: 0.12;">

                    {{-- Auto Redirect Countdown Section --}}
                    <div class="text-center p-3 rounded-4 bg-light border">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-1" style="font-size: 14px; font-weight: 600; color: #334155;">
                            <i class="fa-solid fa-arrows-rotate fa-spin text-success" id="countdownSpinner"></i>
                            <span>@if($isSch)You will be taken to the homepage <strong class="text-primary">www.ideaabd.com</strong> when the timer ends. Download your form first.@elseস্বয়ংক্রিয়ভাবে হোমপেজ <strong class="text-primary">www.ideaabd.com</strong> এ নিয়ে যাওয়া হচ্ছে...@endif</span>
                        </div>
                        <div class="fw-bold text-dark font-monospace mb-2" style="font-size: 16px;">
                            <span id="countdownSecs">{{ $redirectSecs }}</span> {{ $isSch ? 'seconds' : 'সেকেন্ড বাকি' }}
                        </div>

                        <div class="countdown-bar-wrap mb-3">
                            <div class="countdown-bar-fill" id="countdownBar"></div>
                        </div>

                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" id="btnPauseCountdown" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="toggleCountdown()" style="font-size: 12.5px;">
                                <i class="fa-solid fa-pause me-1"></i> {{ $isSch ? 'Stop' : 'রিডাইরেক্ট থামান' }}
                            </button>
                            <a href="{{ url('/') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 12.5px;">
                                <i class="fa-solid fa-house me-1"></i> {{ $isSch ? 'Home' : 'হোমপেজ' }}
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let timeLeft = {{ $redirectSecs }};
    const totalTime = {{ $redirectSecs }};
    const isSch = {{ $isSch ? 'true' : 'false' }};
    let isPaused = false;
    let timerInterval = null;

    const countdownDisplay = document.getElementById('countdownSecs');
    const countdownBar = document.getElementById('countdownBar');
    const btnPause = document.getElementById('btnPauseCountdown');
    const countdownSpinner = document.getElementById('countdownSpinner');

    function startTimer() {
        timerInterval = setInterval(() => {
            if (isPaused) return;

            timeLeft -= 1;
            if (countdownDisplay) {
                countdownDisplay.textContent = timeLeft;
            }
            if (countdownBar) {
                const percent = (timeLeft / totalTime) * 100;
                countdownBar.style.width = percent + '%';
            }

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                window.location.href = @json($redirectTo);
            }
        }, 1000);
    }

    function toggleCountdown() {
        isPaused = !isPaused;
        if (isPaused) {
            if (btnPause) {
                btnPause.innerHTML = '<i class="fa-solid fa-play me-1"></i> ' + (isSch ? 'Resume' : 'রিডাইরেক্ট শুরু করুন');
                btnPause.classList.remove('btn-outline-secondary');
                btnPause.classList.add('btn-success');
            }
            if (countdownSpinner) {
                countdownSpinner.classList.remove('fa-spin');
            }
        } else {
            if (btnPause) {
                btnPause.innerHTML = '<i class="fa-solid fa-pause me-1"></i> ' + (isSch ? 'Stop' : 'রিডাইরেক্ট থামান');
                btnPause.classList.remove('btn-success');
                btnPause.classList.add('btn-outline-secondary');
            }
            if (countdownSpinner) {
                countdownSpinner.classList.add('fa-spin');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        startTimer();
        // the visitor is downloading: never redirect away mid-download
        document.querySelectorAll('.btn-action-lg').forEach(a => a.addEventListener('click', () => {
            if (!isPaused) toggleCountdown();
        }));
    });
</script>
@endpush
@endsection
