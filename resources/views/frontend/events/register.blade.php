@extends('layouts.app')

@section('title', $campaign->title . ' — Registration')

@section('content')
@php
    $accentColor = $campaign->theme_color ?: '#0284c7';
    $fSettings = $campaign->form_settings['fields'] ?? [];
    $nameLabel = $fSettings['name']['label'] ?? 'পূর্ণ নাম';
    $phoneLabel = $fSettings['phone']['label'] ?? 'মোবাইল নম্বর';
    $emailEnabled = $fSettings['email']['enabled'] ?? true;
    $emailLabel = $fSettings['email']['label'] ?? 'ইমেইল ঠিকানা';
    $instEnabled = $fSettings['institution']['enabled'] ?? true;
    $instLabel = $fSettings['institution']['label'] ?? 'প্রতিষ্ঠান / বিশ্ববিদ্যালয় / পেশা';
    $locEnabled = $fSettings['location']['enabled'] ?? true;
    $locLabel = $fSettings['location']['label'] ?? 'জেলা ও থানা';
    $addrEnabled = $fSettings['address']['enabled'] ?? true;
    $addrLabel = $fSettings['address']['label'] ?? 'পূর্ণ ঠিকানা / ডেলিভারি ঠিকানা';
@endphp
<style>
    .campaign-accent-bar {
        border-top: 5px solid {{ $accentColor }} !important;
    }
    .btn-campaign-submit {
        background-color: {{ $accentColor }} !important;
        border-color: {{ $accentColor }} !important;
        color: #fff !important;
        transition: all 0.2s ease;
    }
    .btn-campaign-submit:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px {{ $accentColor }}40;
    }
    .text-campaign-accent {
        color: {{ $accentColor }} !important;
    }
    .badge-campaign-custom {
        background-color: {{ $accentColor }}18 !important;
        color: {{ $accentColor }} !important;
        border: 1px solid {{ $accentColor }}33 !important;
    }
</style>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            {{-- Main Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4 campaign-accent-bar" style="border: 1px solid #e2e8f0;">
                
                {{-- Banner image if available --}}
                @if($campaign->banner_image)
                    <div class="w-100 overflow-hidden bg-dark position-relative" style="max-height: 280px;">
                        <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="{{ $campaign->title }}" class="w-100 h-100 object-fit-cover" style="opacity: 0.95;">
                    </div>
                @endif

                {{-- Header --}}
                <div class="card-header bg-white border-0 pt-4 px-4 px-md-5 pb-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        @if($campaign->badge_text)
                            <span class="badge badge-campaign-custom px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 12px;">
                                {{ $campaign->badge_text }}
                            </span>
                        @endif
                        <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill text-capitalize" style="font-size: 11px;">
                            {{ $campaign->type }}
                        </span>
                        @if($campaign->ends_at)
                            <span class="text-muted small ms-auto" style="font-size: 12px;">
                                <i class="fa-regular fa-clock me-1"></i> শেষ সময়: {{ $campaign->ends_at->format('d M, Y') }}
                            </span>
                        @endif
                    </div>
                    <h1 class="h3 fw-bold text-dark mb-2" style="line-height: 1.3;">{{ $campaign->title }}</h1>
                    @if($campaign->short_description)
                        <p class="text-secondary small mb-0" style="font-size: 14.5px; line-height: 1.6;">
                            {{ $campaign->short_description }}
                        </p>
                    @endif
                </div>

                {{-- Full Description / Guidelines if provided --}}
                @if($campaign->description)
                    <div class="px-4 px-md-5 pb-3">
                        <div class="p-3.5 rounded-3 bg-light border" style="font-size: 13.5px; line-height: 1.6; color: #475569;">
                            {!! nl2br(e($campaign->description)) !!}
                        </div>
                    </div>
                @endif

                <div class="card-body p-4 p-md-5 pt-2">

                    @if(session('error'))
                        <div class="alert alert-danger rounded-3 p-3 mb-4 shadow-xs" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation fs-5 flex-shrink-0"></i>
                                <div>{{ session('error') }}</div>
                            </div>
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

                    <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" id="eventRegForm">
                        @csrf

                        {{-- Section: Personal Details --}}
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="font-size: 16px;">
                                <i class="fa-solid fa-user-pen text-campaign-accent"></i> আপনার ব্যক্তিগত তথ্য
                            </h5>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-dark">
                                        {{ $nameLabel }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control" placeholder="{{ $nameLabel }} লিখুন" value="{{ old('name', $user?->name) }}" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-dark">
                                        {{ $phoneLabel }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" name="phone" class="form-control font-monospace" placeholder="01XXXXXXXXX" value="{{ old('phone', $user?->phone) }}" required>
                                    <small class="text-muted" style="font-size: 11px;">এই নম্বরে রেজিস্ট্রেশন নিশ্চিতকরণ বার্তা যাবে।</small>
                                </div>

                                @if($emailEnabled)
                                    <div class="col-12 {{ $instEnabled ? 'col-md-6' : 'col-12' }}">
                                        <label class="form-label small fw-semibold text-dark">
                                            {{ $emailLabel }} <span class="text-muted fw-normal">(ঐচ্ছিক)</span>
                                        </label>
                                        <input type="email" name="email" class="form-control" placeholder="email@example.com" value="{{ old('email', $user?->email) }}">
                                    </div>
                                @endif

                                @if($instEnabled)
                                    <div class="col-12 {{ $emailEnabled ? 'col-md-6' : 'col-12' }}">
                                        <label class="form-label small fw-semibold text-dark">
                                            {{ $instLabel }}
                                        </label>
                                        <input type="text" name="institution_or_org" class="form-control" placeholder="{{ $instLabel }} লিখুন" value="{{ old('institution_or_org') }}">
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Section: Location Details (if enabled) --}}
                        @if($locEnabled || $addrEnabled)
                            <div class="mb-4">
                                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="font-size: 16px;">
                                    <i class="fa-solid fa-map-location-dot text-campaign-accent"></i> ঠিকানা ও অবস্থান
                                </h5>

                                <div class="row g-3">
                                    @if($locEnabled)
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold text-dark">জেলা (District)</label>
                                            <input type="text" name="district" class="form-control" placeholder="যেমন: রংপুর, ঢাকা, চট্টগ্রাম" value="{{ old('district') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold text-dark">থানা / উপজেলা (Thana / Upazila)</label>
                                            <input type="text" name="thana" class="form-control" placeholder="যেমন: কোতোয়ালী, মিঠাপুকুর" value="{{ old('thana') }}">
                                        </div>
                                    @endif

                                    @if($addrEnabled)
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-dark">{{ $addrLabel }}</label>
                                            <input type="text" name="address" class="form-control" placeholder="গ্রাম/রোড, এলাকা ইত্যাদি" value="{{ old('address') }}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Section: Dynamic Custom Form Fields (configured by Admin) --}}
                        @if(!empty($campaign->custom_fields) && is_array($campaign->custom_fields))
                            <div class="mb-4">
                                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="font-size: 16px;">
                                    <i class="fa-solid fa-list-check text-campaign-accent"></i> অতিরিক্ত তথ্য (Additional Details)
                                </h5>

                                <div class="row g-3">
                                    @foreach($campaign->custom_fields as $field)
                                        @php
                                            $fName = $field['name'] ?? ('field_' . $loop->iteration);
                                            $fLabel = $field['label'] ?? ucfirst(str_replace('_', ' ', $fName));
                                            $fType = $field['type'] ?? 'text';
                                            $fRequired = !empty($field['required']);
                                            $fOptions = array_map('trim', explode(',', $field['options'] ?? ''));
                                        @endphp
                                        <div class="col-12 {{ in_array($fType, ['textarea']) ? 'col-12' : 'col-md-6' }}">
                                            <label class="form-label small fw-semibold text-dark">
                                                {{ $fLabel }} @if($fRequired)<span class="text-danger">*</span>@endif
                                            </label>

                                            @if($fType === 'select')
                                                <select name="{{ $fName }}" class="form-select" {{ $fRequired ? 'required' : '' }}>
                                                    <option value="">নির্বাচন করুন (Select)</option>
                                                    @foreach($fOptions as $opt)
                                                        @if($opt !== '')
                                                            <option value="{{ $opt }}" {{ old($fName) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            @elseif($fType === 'textarea')
                                                <textarea name="{{ $fName }}" rows="3" class="form-control" placeholder="{{ $fLabel }}" {{ $fRequired ? 'required' : '' }}>{{ old($fName) }}</textarea>
                                            @else
                                                <input type="{{ $fType }}" name="{{ $fName }}" class="form-control" placeholder="{{ $fLabel }}" value="{{ old($fName) }}" {{ $fRequired ? 'required' : '' }}>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Section: Donation / Fee Payment if enabled --}}
                        @if($campaign->has_fee_or_donation)
                            <div class="mb-4 p-3.5 rounded-4 bg-light border border-warning-subtle">
                                <h5 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2" style="font-size: 16px;">
                                    <i class="fa-solid fa-hand-holding-dollar text-warning"></i> 
                                    {{ $campaign->type === 'donation' ? 'ডোনেশন / অনুদান প্রদান' : 'রেজিস্ট্রেশন ফি পরিশোধ' }}
                                </h5>

                                @if($campaign->is_donation_flexible)
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-dark">
                                            অনুদান পরিমাণ (৳) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">৳</span>
                                            <input type="number" step="0.01" name="amount_paid" class="form-control" placeholder="{{ number_format($campaign->min_donation) }}" value="{{ old('amount_paid', $campaign->min_donation) }}" min="{{ $campaign->min_donation }}" required>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">সর্বনিম্ন অনুদান: ৳{{ number_format($campaign->min_donation) }}</small>
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-dark">নির্ধারিত রেজিস্ট্রেশন ফি</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">৳</span>
                                            <input type="text" class="form-control fw-bold" value="{{ number_format($campaign->fee_amount, 2) }}" readonly>
                                            <input type="hidden" name="amount_paid" value="{{ $campaign->fee_amount }}">
                                        </div>
                                    </div>
                                @endif

                                @if($campaign->payment_instructions)
                                    <div class="alert alert-warning small py-2 px-3 rounded-3 mb-3 border-0 bg-warning-subtle text-dark">
                                        <i class="fa-solid fa-wallet me-1"></i> {!! nl2br(e($campaign->payment_instructions)) !!}
                                    </div>
                                @else
                                    <div class="alert alert-warning small py-2 px-3 rounded-3 mb-3 border-0 bg-warning-subtle text-dark">
                                        <i class="fa-solid fa-wallet me-1"></i> বিকাশ / নগদ / রকেটে সেন্ড মানি/পেমেন্ট করুন: <strong>{{ $campaign->contact_phone ?: '01558712810' }}</strong>
                                    </div>
                                @endif

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark">পেমেন্ট মেথড</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="bkash">বিকাশ (bKash)</option>
                                            <option value="nagad">নগদ (Nagad)</option>
                                            <option value="rocket">রকেট (Rocket)</option>
                                            <option value="bank">ব্যাংক ট্রান্সফার (Bank)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark">Transaction ID (TrxID)</label>
                                        <input type="text" name="transaction_id" class="form-control font-monospace" placeholder="TrxID দিন" value="{{ old('transaction_id') }}">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Auto Account info alert --}}
                        <div class="p-3 rounded-3 bg-light border d-flex align-items-center gap-2 mb-4" style="font-size: 12.5px; color: #475569;">
                            <i class="fa-solid fa-shield-halved text-success fs-5 flex-shrink-0"></i>
                            <div>
                                আপনার এই তথ্যের মাধ্যমে একটি কাস্টমার প্রোফাইল স্বয়ংক্রিয়ভাবে সংরক্ষিত থাকবে, যা দিয়ে আপনি পরবর্তীতে বই অর্ডার বা ট্র্যাকিং করতে পারবেন।
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-campaign-submit w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 fs-6">
                            <i class="fa-solid fa-check-circle"></i> রেজিস্ট্রেশন সম্পন্ন করুন
                        </button>

                    </form>

                </div>

            </div>

            {{-- Footer Support Help --}}
            <div class="text-center text-muted small">
                যেকোনো প্রয়োজনে সহায়তা পেতে কল করুন: 
                <a href="tel:{{ $campaign->contact_phone ?: '01558712810' }}" class="text-decoration-none fw-bold text-dark">
                    {{ $campaign->contact_phone ?: '01558712810' }}
                </a>
                @if($campaign->contact_email)
                    | <a href="mailto:{{ $campaign->contact_email }}" class="text-decoration-none text-muted">{{ $campaign->contact_email }}</a>
                @endif
                | <a href="{{ route('home') }}" class="text-decoration-none text-muted">ideaabd.com</a>
            </div>

        </div>
    </div>
</div>
@endsection
