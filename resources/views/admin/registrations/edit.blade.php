@extends('layouts.admin')

@section('title', 'রেজিস্ট্রেশন এডিট — ' . $user->name)
@section('heading', 'রেজিস্ট্রেশন তথ্য সংশোধন ও ছবি ব্যবস্থাপনা')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">রেজিস্ট্রেশন</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.show', $user) }}" class="text-decoration-none">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">সম্পাদনা</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> ফিরে যান
        </a>
        <a href="{{ route('admin.registrations.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">
            <i class="fas fa-list me-1"></i> সকল আবেদন
        </a>
    </div>
@endsection

@section('content')
<div style="max-width: 920px;" class="mx-auto mb-5">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light border-0 py-3.5 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-user-pen text-primary me-2"></i>আবেদনকারী ও লেখকের তথ্য সম্পাদনা
                </h5>
                <small class="text-muted">ইউজার আইডি: #{{ $user->id }} • সাবমিট হয়েছে: @bnDate($user->created_at)</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $user->reg_status === 'approved' ? 'bg-success' : ($user->reg_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill px-3 py-1.5 shadow-xs">
                    {{ strtoupper($user->reg_status) }}
                </span>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1.5">
                    {{ strtoupper($user->role) }}
                </span>
            </div>
        </div>

        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger rounded-4 mb-4 border-0 shadow-xs">
                    <div class="fw-bold mb-1 d-flex align-items-center gap-1.5">
                        <i class="fas fa-circle-exclamation text-danger"></i>
                        <span>অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</span>
                    </div>
                    <ul class="mb-0 ps-3 small mt-2">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.registrations.update', $user) }}" enctype="multipart/form-data" id="regEditForm">
                @csrf
                @method('PUT')

                @php 
                    $regData = is_array($user->reg_data) ? $user->reg_data : [];
                    $currAvatar = $user->avatar ? asset('storage/' . ltrim($user->avatar, '/')) : null;
                @endphp

                {{-- ========================================================= --}}
                {{-- 1. DYNAMIC AVATAR / PHOTO UPLOAD (1:1 FIXED CIRCLE)       --}}
                {{-- ========================================================= --}}
                <div class="p-3.5 bg-light rounded-4 border mb-4">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3.5">
                        
                        {{-- Avatar Live Preview Frame --}}
                        <div class="position-relative flex-shrink-0">
                            <div class="rounded-circle overflow-hidden shadow-sm border border-3 border-white position-relative bg-white" 
                                 style="width: 88px; height: 88px; min-width: 88px; min-height: 88px; aspect-ratio: 1 / 1;" id="avatarPreviewBox">
                                @if($currAvatar)
                                    <img src="{{ $currAvatar }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-2 fw-bold bg-primary-subtle">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- File Input & Guidelines --}}
                        <div class="flex-grow-1 w-100">
                            <label class="form-label fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                                <i class="fas fa-camera text-primary"></i>
                                <span>লেখকের ছবি / আবেদনকারীর প্রোফাইল ফটো</span>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" 
                                   class="form-control form-control-sm rounded-3 mb-1" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   onchange="previewAvatar(this, 'avatarPreviewBox')">
                            <div class="text-muted small" style="font-size: 0.76rem;">
                                <i class="fas fa-circle-info me-1 text-primary"></i>JPG, PNG বা WebP ফরম্যাট। আপলোডকৃত যেকোনো সাইজের ছবি স্বয়ংক্রিয়ভাবে পারফেক্ট ফ্রেমে ক্রপ হয়ে বসবে।
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. BASIC IDENTITY & ACCOUNT CREDENTIALS                   --}}
                {{-- ========================================================= --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-id-card-clip text-primary"></i>
                        <span>প্রাথমিক অ্যাকাউন্ট ও পরিচিতি তথ্য</span>
                    </h6>

                    <div class="row g-3">
                        {{-- লেখক নাম (Author Display Name) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                লেখক নাম <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" placeholder="যেমন: আল-আমিন ইসলাম / হুমায়ূন আহমেদ" required>
                            <div class="text-muted" style="font-size: 0.72rem;">সাইটের বই, প্রবন্ধ ও লেখক ডিরেক্টরিতে এই নামটি প্রদর্শিত হবে।</div>
                        </div>

                        {{-- ইমেইল এড্রেস --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                ইমেইল এড্রেস <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                        </div>

                        {{-- মোবাইল নম্বর --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                মোবাইল নম্বর (লগইন ইউজারনেম) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone" class="form-control rounded-3 font-monospace" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        {{-- অ্যাকাউন্ট ধরন --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">
                                অ্যাকাউন্ট ধরন <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select rounded-3" id="roleSelector" onchange="switchRoleSections(this.value)">
                                <option value="author" @selected(old('role', $user->role) === 'author')>লেখক (Author)</option>
                                <option value="seller" @selected(old('role', $user->role) === 'seller')>সেলার (Seller)</option>
                                <option value="publisher" @selected(old('role', $user->role) === 'publisher')>প্রকাশক (Publisher)</option>
                            </select>
                        </div>

                        {{-- অনুমোদন স্ট্যাটাস --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">
                                অনুমোদন স্ট্যাটাস <span class="text-danger">*</span>
                            </label>
                            <select name="reg_status" class="form-select rounded-3">
                                <option value="pending" @selected(old('reg_status', $user->reg_status) === 'pending')>⏳ অপেক্ষমান (Pending)</option>
                                <option value="approved" @selected(old('reg_status', $user->reg_status) === 'approved')>✅ অনুমোদিত (Approved)</option>
                                <option value="rejected" @selected(old('reg_status', $user->reg_status) === 'rejected')>❌ প্রত্যাখ্যাত (Rejected)</option>
                            </select>
                        </div>

                        {{-- অ্যাকাউন্ট সক্রিয় সুইচ --}}
                        <div class="col-12">
                            <div class="form-check form-switch p-2 bg-light rounded-3 border ps-5">
                                <input class="form-check-input ms-n4" type="checkbox" name="is_active" id="isActiveSwitch" value="1" @checked(old('is_active', $user->is_active))>
                                <label class="form-check-label small fw-semibold text-dark" for="isActiveSwitch">
                                    অ্যাকাউন্ট সক্রিয় রাখুন (সক্রিয় ও অনুমোদিত হলে ইউজার সাইটে লগইন ও পোস্ট করতে পারবে)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 3. AUTHOR DEDICATED FIELDS (লেখক সংক্রান্ত বিস্তারিত তথ্য) --}}
                {{-- ========================================================= --}}
                <div id="authorDetailsCard" class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-feather-pointed text-success"></i>
                        <span>লেখক ও সাহিত্য সংক্রান্ত বিস্তারিত তথ্য</span>
                    </h6>

                    <div class="row g-3">
                        {{-- প্রকৃত বা পুরো নাম --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                প্রকৃত বা পুরো নাম (Full Legal Name)
                            </label>
                            <input type="text" name="full_name" class="form-control rounded-3" 
                                   value="{{ old('full_name', $regData['full_name'] ?? '') }}" 
                                   placeholder="জাতীয় পরিচয়পত্র অনুযায়ী পুরো নাম">
                        </div>

                        {{-- লেখকের ছদ্মনাম / পেন-নেম (আলাদা ঘর) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                ছদ্মনাম / কলমনাম (Pen Name / Pseudonym)
                            </label>
                            <input type="text" name="pen_name" class="form-control rounded-3" 
                                   value="{{ old('pen_name', $regData['pen_name'] ?? '') }}" 
                                   placeholder="যদি কোনো ছদ্মনাম থাকে">
                        </div>

                        {{-- সাহিত্য ঘরানা / বিষয় --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                সাহিত্য ঘরানা / লেখার বিষয়
                            </label>
                            <input type="text" name="genre" class="form-control rounded-3" 
                                   value="{{ old('genre', $regData['genre'] ?? '') }}" 
                                   placeholder="যেমন: উপন্যাস, গল্প, কবিতা, প্রবন্ধ, গবেষণা...">
                        </div>

                        {{-- জাতীয় পরিচয়পত্র নম্বর (NID) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                জাতীয় পরিচয়পত্র নম্বর (NID)
                            </label>
                            <input type="text" name="nid" class="form-control rounded-3 font-monospace" 
                                   value="{{ old('nid', $regData['nid'] ?? '') }}" 
                                   placeholder="এনআইডি নম্বর">
                        </div>

                        {{-- লেখক পরিচিতি ও জীবনবৃত্তান্ত (Bio) --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    লেখক পরিচিতি ও বায়োগ্রাফি (Bio)
                                </label>
                                <span class="text-muted small" id="bioCounter" style="font-size: 0.72rem;">০ অক্ষর</span>
                            </div>
                            <textarea name="bio" id="authorBioInput" rows="4" class="form-control rounded-3" 
                                      placeholder="লেখকের জীবনবৃত্তান্ত, কর্মজীবন, সাহিত্যসাধনা ও প্রকাশিত বই সম্পর্কিত তথ্য..." 
                                      oninput="updateCharCount(this, 'bioCounter')">{{ old('bio', $regData['bio'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 4. SOCIAL MEDIA & WEB LINKS (সোস্যাল মিডিয়া ও ওয়েব লিংক)  --}}
                {{-- ========================================================= --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-share-nodes text-info"></i>
                        <span>সোশ্যাল মিডিয়া ও ওয়েবসাইট লিংক</span>
                    </h6>

                    <div class="row g-3">
                        {{-- ফেসবুক প্রোফাইল / পেইজ লিংক --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fab fa-facebook text-primary me-1"></i> ফেসবুক প্রোফাইল / পেইজ লিংক
                            </label>
                            <input type="text" name="facebook" class="form-control rounded-3" 
                                   value="{{ old('facebook', $regData['facebook'] ?? '') }}" 
                                   placeholder="https://facebook.com/your-page">
                        </div>

                        {{-- ব্যক্তিগত ওয়েবসাইট --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fas fa-globe text-success me-1"></i> ব্যক্তিগত ওয়েবসাইট / পোর্টফোলিও
                            </label>
                            <input type="text" name="website" class="form-control rounded-3" 
                                   value="{{ old('website', $regData['website'] ?? '') }}" 
                                   placeholder="https://yourwebsite.com">
                        </div>

                        {{-- টুইটার / এক্স লিংক --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fab fa-x-twitter text-dark me-1"></i> টুইটার / এক্স (X) প্রোফাইল লিংক
                            </label>
                            <input type="text" name="twitter" class="form-control rounded-3" 
                                   value="{{ old('twitter', $regData['twitter'] ?? '') }}" 
                                   placeholder="https://x.com/username">
                        </div>

                        {{-- ইউটিউব চ্যানেল --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fab fa-youtube text-danger me-1"></i> ইউটিউব চ্যানেল লিংক
                            </label>
                            <input type="text" name="youtube" class="form-control rounded-3" 
                                   value="{{ old('youtube', $regData['youtube'] ?? '') }}" 
                                   placeholder="https://youtube.com/@channel">
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 5. SELLER & PUBLISHER SPECIFIC DETAILS                    --}}
                {{-- ========================================================= --}}
                <div id="businessDetailsCard" class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-shop text-warning"></i>
                        <span>দোকান, ব্যবসা ও জোন (সেলার ও প্রকাশকদের জন্য)</span>
                    </h6>

                    <div class="row g-3">
                        {{-- দোকান / ব্যবসার নাম / জোন (সেলারদের জন্য) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                দোকান / ব্যবসার নাম (সেলারদের জন্য)
                            </label>
                            <input type="text" name="shop_name" class="form-control rounded-3" 
                                   value="{{ old('shop_name', $regData['shop_name'] ?? '') }}" 
                                   placeholder="বইয়ের দোকানের নাম">
                        </div>

                        {{-- জোন (সেলারদের জন্য) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                জোন / এলাকা (সেলারদের জন্য)
                            </label>
                            <input type="text" name="zone" class="form-control rounded-3" 
                                   value="{{ old('zone', $regData['zone'] ?? '') }}" 
                                   placeholder="যেমন: ঢাকা জোন, চট্টগ্রাম জোন...">
                        </div>

                        {{-- প্রকাশনীর নাম (প্রকাশকদের জন্য) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                প্রকাশনীর নাম (প্রকাশকদের জন্য)
                            </label>
                            <input type="text" name="publisher_name" class="form-control rounded-3" 
                                   value="{{ old('publisher_name', $regData['publisher_name'] ?? '') }}" 
                                   placeholder="প্রকাশনা সংস্থার নাম">
                        </div>

                        {{-- ট্রেড লাইসেন্স নম্বর --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                ট্রেড লাইসেন্স নম্বর
                            </label>
                            <input type="text" name="trade_license" class="form-control rounded-3 font-monospace" 
                                   value="{{ old('trade_license', $regData['trade_license'] ?? '') }}" 
                                   placeholder="লাইসেন্স নম্বর">
                        </div>

                        {{-- পূর্ণাঙ্গ ঠিকানা --}}
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">
                                পূর্ণাঙ্গ ব্যবসায়িক বা আবাসিক ঠিকানা
                            </label>
                            <textarea name="address" rows="2" class="form-control rounded-3" 
                                      placeholder="ঠিকানা, থানা, জেলা...">{{ old('address', $regData['address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 6. SUBMIT ACTIONS                                         --}}
                {{-- ========================================================= --}}
                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-light border px-4 rounded-pill">
                        বাতিল
                    </a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm" id="submitBtn">
                        <i class="fas fa-save me-1"></i> তথ্য ও ছবি হালনাগাদ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewAvatar(input, previewBoxId) {
    const box = document.getElementById(previewBoxId);
    if (!box) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            box.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateCharCount(el, counterId) {
    const counter = document.getElementById(counterId);
    if (counter) {
        counter.textContent = el.value.length + ' অক্ষর';
    }
}

// Initial bio counter count
document.addEventListener('DOMContentLoaded', function() {
    const bio = document.getElementById('authorBioInput');
    if (bio) {
        updateCharCount(bio, 'bioCounter');
    }
});
</script>

<style>
.object-fit-cover {
    object-fit: cover !important;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
@endsection
