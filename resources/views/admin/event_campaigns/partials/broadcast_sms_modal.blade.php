{{-- Event Update Dynamic SMS Broadcast Modal Partial --}}
@php
    $defaultVenue = $campaign->form_settings['form_venue'] ?? 'রংপুর';
    $defaultDate = $campaign->form_settings['form_date'] ?? ($campaign->starts_at ? $campaign->starts_at->format('d M Y') : '২০ অক্টোবর ২০২৬');
    $sampleCardUrl = url('/card/RSU-2026-001');

    $tplScheduleUpdate = "প্রিয় {name}, {event_title}-এর সময়সূচি আপডেট: অনুষ্ঠান আগামী {date} তারিখে {venue}-এ অনুষ্ঠিত হবে। কার্ড লিংক: {card_url} — আইডিয়া প্রকাশন";
    $tplCardDownload = "প্রিয় {name}, {event_title}-এ আপনার নিবন্ধন অনুমোদিত হয়েছে। কার্ড ডাউনলোড লিংক: {card_url} (কার্ড নং: {reg_no}) — আইডিয়া প্রকাশন";
    $tplGeneralNotice = "জরুরি বিজ্ঞপ্তি: প্রিয় {name}, {event_title}-এ নির্ধারিত সময়ে উপস্থিত থাকার জন্য অনুরোধ করা হচ্ছে। বিস্তারিত: {card_url} — আইডিয়া প্রকাশন";
@endphp

<div class="modal fade" id="broadcastSmsModal" tabindex="-1" aria-labelledby="broadcastSmsModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="broadcastSmsForm" action="{{ route('admin.event-campaigns.broadcast-sms', $campaign->id) }}" method="POST" onsubmit="handleBroadcastSmsSubmit(event)">
                @csrf
                <input type="hidden" name="selected_ids" id="broadcastSelectedIdsInput" value="[]">

                {{-- Modal Header --}}
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #065f46 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-bullhorn text-warning fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="broadcastSmsModalLabel">একবারে ইভেন্ট আপডেট মেসেজ পাঠান</h5>
                            <small class="text-white-50">{{ $campaign->title }} &bull; বাল্ক এসএমএস ব্রডকাস্টার</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3 mb-3">
                        {{-- 1. Target Audience --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                                <span>১. প্রাপক নির্বাচন করুন (Target Audience)</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" id="liveTargetCountBadge">
                                    {{ $totalRegistrations }} জন প্রাপক
                                </span>
                            </label>
                            <select name="target_group" id="broadcastTargetGroup" class="form-select form-select-sm fw-medium shadow-xs" onchange="updateBroadcastTargetDisplay(this.value)">
                                <option value="all" selected>সকল অংশগ্রহণকারী (মোট {{ $totalRegistrations }} জন)</option>
                                <option value="confirmed">শুধু অনুমোদিত / কনফার্মড (মোট {{ $campaign->registrations()->whereIn('status', ['confirmed', 'selected', 'approved'])->count() }} জন)</option>
                                <option value="pending">শুধু পেন্ডিং রিভিউ (মোট {{ $pendingCount }} জন)</option>
                                <option value="custom_ids" id="optCustomSelected">টেবিলে টিক দেওয়া অংশগ্রহণকারী (সিলেক্টেড: 0 জন)</option>
                                @if(request('division') || request('district'))
                                    <option value="filtered">বর্তমান ফিল্টারের তালিকা (বিভাগ: {{ request('division', 'সব') }}, জেলা: {{ request('district', 'সব') }})</option>
                                @endif
                            </select>
                            <input type="hidden" name="division" value="{{ request('division') }}">
                            <input type="hidden" name="district" value="{{ request('district') }}">
                        </div>

                        {{-- 2. Preset Template Selector --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">২. ইভেন্ট আপডেট টেমপ্লেট</label>
                            <select class="form-select form-select-sm shadow-xs" id="broadcastTemplateSelect" onchange="applyBroadcastTemplate(this.value)">
                                <option value="">-- রেডি টেমপ্লেট বেছে নিন --</option>
                                <option value="{{ $tplScheduleUpdate }}" selected>১. ইভেন্ট সময়সূচি ও ভেন্যু আপডেট</option>
                                <option value="{{ $tplCardDownload }}">২. আমন্ত্রণ কার্ড ডাউনলোড ও লিংক</option>
                                <option value="{{ $tplGeneralNotice }}">৩. জরুরি ঘোষণা ও দিকনির্দেশনা</option>
                                <option value="">৪. কাস্টম বার্তা (খালি লিখুন)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Dynamic Tags (Click to Insert) --}}
                    <div class="mb-3 p-2.5 bg-white rounded-3 border shadow-xs">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1.5">
                            <span class="small fw-bold text-dark" style="font-size: 12px;">
                                <i class="fa-solid fa-code text-primary me-1"></i> ডাইনামিক ট্যাগ (ক্লিক করলেই মেসেজে যুক্ত হবে):
                            </span>
                            <span class="text-muted small" style="font-size: 11px;">প্রতিটি ইউজারের তথ্য স্বয়ংক্রিয়ভাবে বসবে</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5">
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertBroadcastTag('{name}')" title="অংশগ্রহণকারীর নাম">
                                + {name} (নাম)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertBroadcastTag('{reg_no}')" title="রেজিস্ট্রেশন নম্বর">
                                + {reg_no} (আইডি নং)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertBroadcastTag('{card_url}')" title="কার্ডের ডাউনলোড লিংক">
                                + {card_url} (কার্ড লিংক)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertBroadcastTag('{event_title}')" title="ইভেন্টের নাম">
                                + {event_title} (ইভেন্ট)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertBroadcastTag('{venue}')" title="অনুষ্ঠানস্থল">
                                + {venue} (ভেন্যু)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertBroadcastTag('{date}')" title="তারিখ">
                                + {date} (তারিখ)
                            </button>
                        </div>
                    </div>

                    {{-- Message Content Textarea --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">মেসেজ কন্টেন্ট <span class="text-danger">*</span></label>
                            <div class="text-muted font-monospace small" id="broadcastSmsCounter">
                                <span class="fw-bold text-primary" id="broadcastCharCount">0</span> অক্ষর &bull; 
                                <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5 fw-bold" id="broadcastMsgCountBadge">1 SMS</span>
                            </div>
                        </div>
                        <textarea name="message" id="broadcastMessageInput" rows="4" class="form-control font-monospace shadow-xs" style="font-size: 13.5px; line-height: 1.6;" placeholder="এখানে মেসেজ লিখুন বা ট্যাগ যুক্ত করুন..." required oninput="handleBroadcastMessageInput()">{{ $tplScheduleUpdate }}</textarea>
                    </div>

                    {{-- Live Sample Preview --}}
                    <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-muted" style="font-size: 11.5px;">
                                <i class="fa-solid fa-mobile-screen me-1 text-primary"></i> প্রাপক যেমন এসএমএস পাবেন (Live Preview):
                            </span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">নমুনা ডেটা</span>
                        </div>
                        <div class="p-2.5 bg-light rounded-2 font-monospace text-dark small border" id="broadcastLivePreviewBox" style="font-size: 12.5px; line-height: 1.6; word-break: break-word;">
                            —
                        </div>
                    </div>

                    {{-- Duplicate Phone Safeguard --}}
                    <div class="d-flex align-items-center justify-content-between p-2.5 bg-white rounded-3 border">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="avoid_duplicate_phone" id="avoidDupPhoneCheck" value="1" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="avoidDupPhoneCheck">
                                একই মোবাইল নম্বরে একাধিকবার পাঠানো বন্ধ রাখুন (Avoid Duplicate Numbers)
                            </label>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small px-2 py-1">প্রস্তাবিত</span>
                    </div>

                    {{-- Dispatch Progress Box (Hidden by default) --}}
                    <div id="broadcastProgressBox" class="mt-3 p-3 bg-white rounded-3 border shadow-xs" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-primary d-flex align-items-center gap-1.5">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                <span id="broadcastProgressText">বার্তা প্রেরিত হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...</span>
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer py-2.5 px-4 bg-white border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3.5 fw-semibold" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" id="btnSubmitBroadcast" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span id="btnSubmitBroadcastText">একবারে আপডেট পাঠান</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Constants for Live Preview Sample
    const SAMPLE_NAME = "শাকিল মাসুদ";
    const SAMPLE_REG_NO = "RSU-2026-001";
    const SAMPLE_CARD_URL = "{{ $sampleCardUrl }}";
    const SAMPLE_TITLE = "{{ addslashes($campaign->title) }}";
    const SAMPLE_VENUE = "{{ addslashes($defaultVenue) }}";
    const SAMPLE_DATE = "{{ addslashes($defaultDate) }}";

    function updateBroadcastTargetDisplay(val) {
        const badge = document.getElementById('liveTargetCountBadge');
        if (!badge) return;
        const total = {{ $totalRegistrations }};
        const confirmed = {{ $campaign->registrations()->whereIn('status', ['confirmed', 'selected', 'approved'])->count() }};
        const pending = {{ $pendingCount }};
        const selectedCheckboxes = document.querySelectorAll('.participant-select-check:checked').length;

        if (val === 'confirmed') {
            badge.textContent = confirmed + ' জন প্রাপক';
        } else if (val === 'pending') {
            badge.textContent = pending + ' জন প্রাপক';
        } else if (val === 'custom_ids') {
            badge.textContent = selectedCheckboxes + ' জন প্রাপক';
        } else {
            badge.textContent = total + ' জন প্রাপক';
        }
    }

    function applyBroadcastTemplate(val) {
        const textarea = document.getElementById('broadcastMessageInput');
        if (!textarea) return;
        textarea.value = val;
        handleBroadcastMessageInput();
    }

    function insertBroadcastTag(tag) {
        const textarea = document.getElementById('broadcastMessageInput');
        if (!textarea) return;

        const start = textarea.selectionStart || 0;
        const end = textarea.selectionEnd || 0;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
        handleBroadcastMessageInput();
    }

    function handleBroadcastMessageInput() {
        const textarea = document.getElementById('broadcastMessageInput');
        if (!textarea) return;

        const raw = textarea.value || '';
        const charCountEl = document.getElementById('broadcastCharCount');
        const badgeEl = document.getElementById('broadcastMsgCountBadge');
        const previewEl = document.getElementById('broadcastLivePreviewBox');

        // 1. Calculate length and SMS segments
        const charLen = raw.length;
        if (charCountEl) charCountEl.textContent = charLen;

        // Check if contains non-ASCII (Unicode/Bengali)
        const isUnicode = /[^\u0000-\u007F]/.test(raw);
        let smsCount = 1;

        if (isUnicode) {
            if (charLen <= 70) {
                smsCount = 1;
            } else {
                smsCount = Math.ceil(charLen / 67);
            }
            if (badgeEl) badgeEl.textContent = smsCount + ' SMS (বাংলা)';
        } else {
            if (charLen <= 160) {
                smsCount = 1;
            } else {
                smsCount = Math.ceil(charLen / 153);
            }
            if (badgeEl) badgeEl.textContent = smsCount + ' SMS (English)';
        }

        // 2. Live Preview
        if (previewEl) {
            let previewText = raw
                .split('{name}').join(SAMPLE_NAME)
                .split('{reg_no}').join(SAMPLE_REG_NO)
                .split('{card_url}').join(SAMPLE_CARD_URL)
                .split('{event_title}').join(SAMPLE_TITLE)
                .split('{venue}').join(SAMPLE_VENUE)
                .split('{date}').join(SAMPLE_DATE);

            previewEl.textContent = previewText || '(মেসেজ বক্স খালি)';
        }
    }

    function openBulkBroadcastSmsModal() {
        const selected = Array.from(document.querySelectorAll('.participant-select-check:checked')).map(cb => cb.value);
        const optCustom = document.getElementById('optCustomSelected');
        const selectBox = document.getElementById('broadcastTargetGroup');
        const hiddenIds = document.getElementById('broadcastSelectedIdsInput');

        if (hiddenIds) {
            hiddenIds.value = JSON.stringify(selected);
        }

        if (optCustom) {
            optCustom.textContent = `টেবিলে টিক দেওয়া অংশগ্রহণকারী (সিলেক্টেড: ${selected.length} জন)`;
        }

        if (selectBox) {
            if (selected.length > 0) {
                selectBox.value = 'custom_ids';
            } else {
                selectBox.value = 'all';
            }
            updateBroadcastTargetDisplay(selectBox.value);
        }

        const modalEl = document.getElementById('broadcastSmsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    async function handleBroadcastSmsSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('broadcastSmsForm');
        const btn = document.getElementById('btnSubmitBroadcast');
        const btnText = document.getElementById('btnSubmitBroadcastText');
        const progressBox = document.getElementById('broadcastProgressBox');

        // Check selected checkboxes if target is custom_ids
        const targetGroup = document.getElementById('broadcastTargetGroup')?.value;
        const hiddenIds = document.getElementById('broadcastSelectedIdsInput');
        if (targetGroup === 'custom_ids') {
            const selected = Array.from(document.querySelectorAll('.participant-select-check:checked')).map(cb => cb.value);
            if (selected.length === 0) {
                alert('অনুগ্রহ করে টেবিল থেকে অন্তত একজন অংশগ্রহণকারী নির্বাচন করুন।');
                return;
            }
            if (hiddenIds) hiddenIds.value = JSON.stringify(selected);
        }

        if (!confirm('আপনি কি নিশ্চিত যে নির্বাচিত অংশগ্রহণকারীদের কাছে এই ইভেন্ট আপডেট বার্তা পাঠাতে চান?')) {
            return;
        }

        if (btn) btn.disabled = true;
        if (btnText) btnText.textContent = 'প্রেরণ করা হচ্ছে...';
        if (progressBox) progressBox.style.display = 'block';

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message, false);
                } else {
                    alert(data.message);
                }
                const modalEl = document.getElementById('broadcastSmsModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
            } else {
                alert(data.message || 'মেসেজ প্রেরণে ব্যর্থ হয়েছে।');
            }
        } catch (err) {
            console.error('Broadcast SMS Error:', err);
            alert('সার্ভার যোগাযোগে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        } finally {
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = 'একবারে আপডেট পাঠান';
            if (progressBox) progressBox.style.display = 'none';
        }
    }

    // Initialize on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        handleBroadcastMessageInput();
    });
</script>
