@extends('layouts.app')

@section('title', $campaign->title . ' — Registration')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-campaign.css') }}">
@endpush

@section('content')
@php
    $accentColor = $campaign->theme_color ?: '#0f3a68';
    $fSettings = $campaign->form_settings['fields'] ?? [];
    $nameLabel = $fSettings['name']['label'] ?? 'পূর্ণ নাম (Full Name)';
    $phoneLabel = $fSettings['phone']['label'] ?? 'মোবাইল নম্বর (Mobile No)';
    $emailEnabled = $fSettings['email']['enabled'] ?? true;
    $emailLabel = $fSettings['email']['label'] ?? 'ইমেইল ঠিকানা (Email)';
    $instEnabled = $fSettings['institution']['enabled'] ?? true;
    $instLabel = $fSettings['institution']['label'] ?? 'প্রতিষ্ঠান / বিশ্ববিদ্যালয় / পেশা';
    $locEnabled = $fSettings['location']['enabled'] ?? true;
    $locLabel = $fSettings['location']['label'] ?? 'জেলা ও থানা (District & Upazila)';
    $addrEnabled = $fSettings['address']['enabled'] ?? true;
    $addrLabel = $fSettings['address']['label'] ?? 'পূর্ণ ঠিকানা (Full Address)';
    
    $totalRegistrations = $campaign->registrations()->count();
    $percentBooked = $campaign->max_participants ? min(100, round(($totalRegistrations / $campaign->max_participants) * 100)) : 0;
@endphp

<div class="container py-4 py-md-5">
    <div class="event-page-wrapper">

        {{-- Main Event Card --}}
        <div class="card event-main-card mb-4" style="border-top: 5px solid {{ $accentColor }} !important;">

            {{-- Event Banner Image --}}
            @if($campaign->banner_image)
                <div class="event-banner-container">
                    <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="{{ $campaign->title }}" class="event-banner-img">
                </div>
            @endif

            {{-- Header Box --}}
            <div class="event-header-box">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <div class="d-flex align-items-center gap-2">
                        @if($campaign->badge_text)
                            <span class="event-badge-pill" style="background: {{ $accentColor }}18; color: {{ $accentColor }}; border: 1px solid {{ $accentColor }}33;">
                                <i class="fa-solid fa-star"></i> {{ $campaign->badge_text }}
                            </span>
                        @endif
                        <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill text-capitalize" style="font-size: 11px;">
                            {{ $campaign->type }}
                        </span>
                    </div>

                    @if($campaign->ends_at)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill" style="font-size: 11.5px;">
                            <i class="fa-regular fa-clock me-1"></i> শেষ সময়: {{ $campaign->ends_at->format('d M, Y - h:i A') }}
                        </span>
                    @endif
                </div>

                <h1 class="h3 fw-bold text-dark mb-2" style="line-height: 1.35; color: {{ $accentColor }} !important;">
                    {{ $campaign->title }}
                </h1>

                @if($campaign->short_description)
                    <p class="text-secondary small mb-0" style="font-size: 14.5px; line-height: 1.6;">
                        {{ $campaign->short_description }}
                    </p>
                @endif

                {{-- Capacity Progress Meter (if max_participants set) --}}
                @if($campaign->max_participants)
                    <div class="event-capacity-bar-wrap">
                        <div class="d-flex justify-content-between text-muted small mb-1" style="font-size: 11.5px;">
                            <span><i class="fa-solid fa-users me-1 text-primary"></i> রেজিস্ট্রেশন সম্পন্ন: <strong>{{ $totalRegistrations }}</strong> জন</span>
                            <span>সর্বোচ্চ আসন: <strong>{{ $campaign->max_participants }}</strong> ({{ $percentBooked }}%)</span>
                        </div>
                        <div class="event-capacity-bar">
                            <div class="event-capacity-fill" style="width: {{ $percentBooked }}%;"></div>
                        </div>
                    </div>
                @endif

                {{-- Live Countdown Timer (if ends_at is present) --}}
                @if($campaign->ends_at && $campaign->ends_at->isFuture())
                    <div class="event-countdown-box">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-stopwatch fs-4 text-info"></i>
                            <div>
                                <div class="fw-bold small text-white">রেজিস্ট্রেশন বন্ধ হতে বাকি</div>
                                <div class="text-muted" style="font-size: 10.5px;">দ্রুত আপনার আসন নিশ্চিত করুন</div>
                            </div>
                        </div>
                        <div class="countdown-unit-wrap" id="eventCountdownTimer">
                            <div class="countdown-unit">
                                <div class="countdown-num" id="cntDays">00</div>
                                <div class="countdown-lbl">Days</div>
                            </div>
                            <div class="countdown-unit">
                                <div class="countdown-num" id="cntHours">00</div>
                                <div class="countdown-lbl">Hours</div>
                            </div>
                            <div class="countdown-unit">
                                <div class="countdown-num" id="cntMins">00</div>
                                <div class="countdown-lbl">Mins</div>
                            </div>
                            <div class="countdown-unit">
                                <div class="countdown-num" id="cntSecs">00</div>
                                <div class="countdown-lbl">Secs</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Full Guidelines / Description --}}
            @if($campaign->description)
                <div class="px-4 px-md-5 pt-3">
                    <div class="p-3.5 rounded-3 bg-light border" style="font-size: 13.5px; line-height: 1.65; color: #334155;">
                        {!! nl2br(e($campaign->description)) !!}
                    </div>
                </div>
            @endif

            {{-- Registration Form Body --}}
            <div class="card-body p-4 p-md-5 pt-3">

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 p-3 mb-4 shadow-xs" role="alert">
                        <i class="fa-solid fa-circle-exclamation fs-5 me-2 align-middle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger rounded-3 p-3 mb-4">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সমাধান করুন:</div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="eventRegForm">
                    @csrf

                    {{-- Hidden Base64 Optimized Photo string --}}
                    <input type="hidden" name="optimized_photo_data" id="eventOptimizedPhoto">

                    {{-- 1. Personal Information --}}
                    <div class="event-form-section">
                        <div class="event-section-title">
                            <i class="fa-solid fa-user-pen"></i> ১. আপনার ব্যক্তিগত তথ্য (Personal Information)
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="event-form-label">
                                    {{ $nameLabel }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="event-input" placeholder="আপনার পূর্ণ নাম লিখুন" value="{{ old('name', $user?->name) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="event-form-label">
                                    {{ $phoneLabel }} <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="tel" name="phone" id="eventPhoneInput" class="event-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('phone', $user?->phone) }}" required oninput="formatBdPhone(this)">
                                    <span class="phone-operator-badge"></span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">নিশ্চিতকরণ এসএমএস এই নম্বরে যাবে।</small>
                            </div>

                            @if($emailEnabled)
                                <div class="col-12 {{ $instEnabled ? 'col-md-6' : 'col-12' }}">
                                    <label class="event-form-label">
                                        {{ $emailLabel }} <span class="text-muted fw-normal">(ঐচ্ছিক)</span>
                                    </label>
                                    <input type="email" name="email" class="event-input" placeholder="email@example.com" value="{{ old('email', $user?->email) }}">
                                </div>
                            @endif

                            @if($instEnabled)
                                <div class="col-12 {{ $emailEnabled ? 'col-md-6' : 'col-12' }}">
                                    <label class="event-form-label">
                                        {{ $instLabel }}
                                    </label>
                                    <input type="text" name="institution_or_org" class="event-input" placeholder="যেমন: ঢাকা বিশ্ববিদ্যালয়, দিনাজপুর কলেজ..." value="{{ old('institution_or_org') }}">
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Location & Address --}}
                    @if($locEnabled || $addrEnabled)
                        <div class="event-form-section">
                            <div class="event-section-title">
                                <i class="fa-solid fa-map-location-dot"></i> ২. ঠিকানা ও অবস্থান (Location & Address)
                            </div>

                            <div class="row g-3">
                                @if($locEnabled)
                                    <div class="col-12 col-md-6">
                                        <label class="event-form-label">জেলা (District) <span class="text-danger">*</span></label>
                                        <input type="text" name="district" class="event-input" placeholder="জেলা নির্বাচন/টাইপ করুন" value="{{ old('district') }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="event-form-label">থানা / উপজেলা (Upazila/Thana) <span class="text-danger">*</span></label>
                                        <input type="text" name="thana" class="event-input" placeholder="থানা/উপজেলা টাইপ করুন" value="{{ old('thana') }}" required>
                                    </div>
                                @endif

                                @if($addrEnabled)
                                    <div class="col-12">
                                        <label class="event-form-label">{{ $addrLabel }}</label>
                                        <input type="text" name="address" class="event-input" placeholder="গ্রাম/রোড, এলাকা ইত্যাদি" value="{{ old('address') }}">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 3. Dynamic Custom Form Fields (configured by Admin) --}}
                    @if(!empty($campaign->custom_fields) && is_array($campaign->custom_fields))
                        <div class="event-form-section">
                            <div class="event-section-title">
                                <i class="fa-solid fa-list-check"></i> ৩. অতিরিক্ত তথ্য (Additional Details)
                            </div>

                            <div class="row g-3">
                                @foreach($campaign->custom_fields as $field)
                                    @php
                                        $fName = $field['name'] ?? ('field_' . $loop->iteration);
                                        $fLabel = $field['label'] ?? ucfirst(str_replace('_', ' ', $fName));
                                        $fType = $field['type'] ?? 'text';
                                        $fRequired = !empty($field['required']);
                                        $fOptions = array_map('trim', explode(',', $field['options'] ?? ''));
                                    @endphp
                                    <div class="col-12 {{ in_array($fType, ['textarea', 'file', 'image']) ? 'col-12' : 'col-md-6' }}">
                                        <label class="event-form-label">
                                            {{ $fLabel }} @if($fRequired)<span class="text-danger">*</span>@endif
                                        </label>

                                        @if($fType === 'select')
                                            <select name="{{ $fName }}" class="event-input form-select" {{ $fRequired ? 'required' : '' }}>
                                                <option value="">নির্বাচন করুন (Select)</option>
                                                @foreach($fOptions as $opt)
                                                    @if($opt !== '')
                                                        <option value="{{ $opt }}" {{ old($fName) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @elseif($fType === 'textarea')
                                            <textarea name="{{ $fName }}" rows="3" class="event-input" placeholder="{{ $fLabel }}" {{ $fRequired ? 'required' : '' }}>{{ old($fName) }}</textarea>
                                        @elseif(in_array($fType, ['image', 'photo', 'file']))
                                            <div class="event-photo-dropzone" onclick="document.getElementById('customFile_{{ $fName }}').click()">
                                                <img id="prevImg_{{ $fName }}" class="event-photo-preview" alt="Preview">
                                                <div id="prompt_{{ $fName }}">
                                                    <i class="fa-solid fa-cloud-arrow-up fs-3 text-secondary mb-1"></i>
                                                    <div class="fw-semibold small text-dark">{{ $fLabel }} আপলোড করুন</div>
                                                    <div class="text-muted" style="font-size: 10.5px;">JPG, PNG, WebP (অটো অপটিমাইজড)</div>
                                                </div>
                                                <span id="badge_{{ $fName }}" class="badge bg-success mt-1" style="display: none; font-size: 10.5px;">✓ Auto Optimized</span>
                                            </div>
                                            <input type="file" id="customFile_{{ $fName }}" name="{{ $fName }}" accept="image/*,application/pdf" class="d-none" onchange="optimizeEventPhoto(this, 'eventOptimizedPhoto', 'prevImg_{{ $fName }}', 'prompt_{{ $fName }}', 'badge_{{ $fName }}')">
                                        @else
                                            <input type="{{ $fType }}" name="{{ $fName }}" class="event-input" placeholder="{{ $fLabel }}" value="{{ old($fName) }}" {{ $fRequired ? 'required' : '' }}>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 4. Payment / Donation Section (if enabled) --}}
                    @if($campaign->has_fee_or_donation)
                        <div class="event-form-section p-3.5 p-md-4 rounded-4 bg-light border border-warning-subtle">
                            <div class="event-section-title border-0 pb-1 mb-2">
                                <i class="fa-solid fa-hand-holding-dollar text-warning"></i>
                                <span>{{ $campaign->type === 'donation' ? '৪. অনুদান পরিমাণ ও পেমেন্ট (Donation & Payment)' : '৪. রেজিস্ট্রেশন ফি ও পেমেন্ট (Registration Fee)' }}</span>
                            </div>

                            @if($campaign->is_donation_flexible)
                                <div class="mb-3">
                                    <label class="event-form-label">
                                        অনুদান পরিমাণ (৳) <span class="text-danger">*</span>
                                    </label>
                                    <div class="donation-chips-wrap">
                                        <div class="donation-chip" onclick="selectDonationPreset(100, this)">৳১০০</div>
                                        <div class="donation-chip" onclick="selectDonationPreset(250, this)">৳২৫০</div>
                                        <div class="donation-chip active" onclick="selectDonationPreset(500, this)">৳৫০০</div>
                                        <div class="donation-chip" onclick="selectDonationPreset(1000, this)">৳১,০০০</div>
                                        <div class="donation-chip" onclick="selectDonationPreset(2000, this)">৳২,০০০</div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">৳</span>
                                        <input type="number" step="1" name="amount_paid" id="donationAmountInput" class="event-input font-monospace fs-5 fw-bold" placeholder="{{ number_format($campaign->min_donation) }}" value="{{ old('amount_paid', $campaign->min_donation ?: 500) }}" min="{{ $campaign->min_donation }}" required>
                                    </div>
                                    <small class="text-muted" style="font-size: 11px;">সর্বনিম্ন অনুদান: ৳{{ number_format($campaign->min_donation) }}</small>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="event-form-label">নির্ধারিত রেজিস্ট্রেশন ফি</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">৳</span>
                                        <input type="text" class="event-input font-monospace fs-5 fw-bold bg-white" value="{{ number_format($campaign->fee_amount, 2) }}" readonly>
                                        <input type="hidden" name="amount_paid" value="{{ $campaign->fee_amount }}">
                                    </div>
                                </div>
                            @endif

                            {{-- Payment Method Selection Grid --}}
                            <label class="event-form-label mb-2">পেমেন্ট মেথড নির্বাচন করুন</label>
                            <input type="hidden" name="payment_method" id="paymentMethodInput" value="{{ old('payment_method', 'bkash') }}">
                            
                            <div class="payment-methods-grid">
                                <div class="payment-method-card bkash {{ old('payment_method', 'bkash') === 'bkash' ? 'active' : '' }}" data-method="bkash" onclick="selectPaymentMethod('bkash')">
                                    <div class="fw-bold" style="color: #d12053;">bKash</div>
                                    <small class="text-muted" style="font-size: 10.5px;">বিকাশ সেন্ড মানি</small>
                                </div>
                                <div class="payment-method-card nagad {{ old('payment_method') === 'nagad' ? 'active' : '' }}" data-method="nagad" onclick="selectPaymentMethod('nagad')">
                                    <div class="fw-bold" style="color: #f97316;">Nagad</div>
                                    <small class="text-muted" style="font-size: 10.5px;">নগদ সেন্ড মানি</small>
                                </div>
                                <div class="payment-method-card rocket {{ old('payment_method') === 'rocket' ? 'active' : '' }}" data-method="rocket" onclick="selectPaymentMethod('rocket')">
                                    <div class="fw-bold" style="color: #8b5cf6;">Rocket</div>
                                    <small class="text-muted" style="font-size: 10.5px;">রকেট সেন্ড মানি</small>
                                </div>
                                <div class="payment-method-card {{ old('payment_method') === 'bank' ? 'active' : '' }}" data-method="bank" onclick="selectPaymentMethod('bank')">
                                    <div class="fw-bold text-dark">Bank</div>
                                    <small class="text-muted" style="font-size: 10.5px;">ব্যাংক ট্রান্সফার</small>
                                </div>
                            </div>

                            @php
                                $payNumber = $campaign->contact_phone ?: '01558712810';
                            @endphp

                            {{-- Payment Instruction Box with Copy Button --}}
                            <div class="p-3 rounded-3 bg-white border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="small fw-semibold text-dark">
                                        <i class="fa-solid fa-mobile-screen-button text-primary me-1"></i> একাউন্ট নম্বর: 
                                        <strong class="font-monospace text-primary fs-6">{{ $payNumber }}</strong>
                                        <span class="badge bg-light text-secondary border ms-1">Personal</span>
                                    </span>
                                    <button type="button" class="copy-btn" onclick="copyTextToClipboard('{{ $payNumber }}', this)">
                                        <i class="fa-regular fa-copy"></i> Copy
                                    </button>
                                </div>
                                <div class="small text-muted" style="font-size: 12px; line-height: 1.5;">
                                    {!! $campaign->payment_instructions ? nl2br(e($campaign->payment_instructions)) : 'বিকাশ / নগদ / রকেট অ্যাপ থেকে উপরের নম্বরে Send Money করে প্রাপ্ত Transaction ID (TrxID) নিচে প্রদান করুন।' !!}
                                </div>
                            </div>

                            {{-- TrxID Input --}}
                            <div>
                                <label class="event-form-label">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                <input type="text" name="transaction_id" class="event-input font-monospace text-uppercase" placeholder="e.g. 9J4K8L7M2N" value="{{ old('transaction_id') }}" required>
                                <small class="text-muted" style="font-size: 11px;">টাকা পাঠানোর পর প্রাপ্ত ট্রানজেকশন কোড লিখুন।</small>
                            </div>
                        </div>
                    @endif

                    {{-- Event Organizer Collaboration Notice --}}
                    <div class="p-3 rounded-3 border d-flex align-items-center gap-3 mb-4 shadow-xs" style="background: #fffbeb; border-color: #fde68a !important; font-size: 13px; color: #92400e;">
                        <i class="fa-solid fa-feather-pointed fs-4 text-warning flex-shrink-0"></i>
                        <div style="line-height: 1.55;">
                            <strong>বিশেষ বিজ্ঞপ্তি:</strong> ইভেন্ট আয়োজক “ফিরেদেখা” আইডিয়া প্রকাশন ইউআরএল ব্যবহারের অনুমতি দিয়েছেন সংগঠনকে সহযোগিতা করা ও লেখকগণের সুবিধার্থে
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="eventSubmitBtn" class="btn-event-submit-main">
                        <i class="fa-solid fa-circle-check"></i> রেজিস্ট্রেশন সম্পন্ন করুন
                    </button>

                </form>

            </div>

        </div>

        {{-- Footer Support Help --}}
        <div class="text-center text-muted small">
            যেকোনো সহায়তায় কল করুন: 
            <a href="tel:{{ $campaign->contact_phone ?: '01558712810' }}" class="text-decoration-none fw-bold text-dark">
                {{ $campaign->contact_phone ?: '01558712810' }}
            </a>
            @if($campaign->contact_email)
                | <a href="mailto:{{ $campaign->contact_email }}" class="text-decoration-none text-muted">{{ $campaign->contact_email }}</a>
            @endif
            | <a href="{{ route('home') }}" class="text-decoration-none text-muted">www.ideaabd.com</a>
        </div>

    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/event-campaign.js') }}"></script>
    @if($campaign->ends_at && $campaign->ends_at->isFuture())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initCountdown("{{ $campaign->ends_at->toIso8601String() }}");
            });
        </script>
    @endif
@endpush
@endsection
