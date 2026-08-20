@extends('layouts.admin')

@section('title', 'রেজিস্ট্রেশন এডিট — ' . $user->name)
@section('heading', 'রেজিস্ট্রেশন তথ্য সংশোধন ও ছবি ব্যবস্থাপনা')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">রেজিস্ট্রেশন</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.show', $user) }}" class="text-decoration-none">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">এডিট ও ছবি আপডেট</li>
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
<div style="max-width: 900px;" class="mx-auto mb-5">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-user-edit text-primary me-2"></i>আবেদনকারীর তথ্য ও ছবি সম্পাদনা (ID: #{{ $user->id }})
            </h5>
            <span class="badge {{ $user->reg_status === 'approved' ? 'bg-success' : ($user->reg_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill px-3 py-1.5">
                {{ strtoupper($user->reg_status) }}
            </span>
        </div>

        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <div class="fw-bold mb-1"><i class="fas fa-circle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.registrations.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @php 
                    $regData = is_array($user->reg_data) ? $user->reg_data : [];
                    $currAvatar = $user->avatar ? asset('storage/' . ltrim($user->avatar, '/')) : null;
                @endphp

                {{-- ========================================================= --}}
                {{-- 1. DYNAMIC AVATAR / PHOTO UPLOAD SECTION                  --}}
                {{-- ========================================================= --}}
                <div class="p-3 bg-light rounded-4 border mb-4">
                    <label class="form-label fw-bold text-dark mb-2">
                        <i class="fas fa-camera text-primary me-1"></i>লেখকের ছবি / আবেদনকারীর প্রোফাইল ফটো
                    </label>
                    
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3.5">
                        {{-- Avatar Preview Frame (1:1 Ratio) --}}
                        <div class="rounded-circle overflow-hidden shadow-xs border border-3 border-white position-relative bg-white flex-shrink-0" 
                             style="width: 80px; height: 80px; min-width: 80px; aspect-ratio: 1 / 1;" id="avatarPreviewBox">
                            @if($currAvatar)
                                <img src="{{ $currAvatar }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-3 fw-bold bg-primary-subtle">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- File Input & Guidelines --}}
                        <div class="flex-grow-1 w-100">
                            <input type="file" name="avatar" id="avatarInput" 
                                   class="form-control form-control-sm rounded-3 mb-1" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   onchange="previewAvatar(this, 'avatarPreviewBox')">
                            <div class="text-muted small" style="font-size: 0.76rem;">
                                <i class="fas fa-circle-info me-1 text-primary"></i>JPG, PNG বা WebP ফরম্যাট (সর্বোচ্চ ৪MB)। যেকোনো সাইজের ছবি স্বয়ংক্রিয়ভাবে পারফেক্ট ফ্রেমে ক্রপ হয়ে বসে যাবে।
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. BASIC ACCOUNT & ROLE INFORMATION                       --}}
                {{-- ========================================================= --}}
                <h6 class="fw-bold text-dark mb-3 pb-1 border-bottom">
                    <i class="fas fa-id-card text-primary me-1"></i> প্রাথমিক অ্যাকাউন্ট তথ্য
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">পুরো নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">ইমেইল এড্রেস <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">মোবাইল নম্বর (লগইন ইউজারনেম) <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $user->phone) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">অ্যাকাউন্ট ধরন <span class="text-danger">*</span></label>
                        <select name="role" class="form-select rounded-3" id="roleSelect" onchange="toggleRoleSections(this.value)">
                            <option value="author" @selected(old('role', $user->role) === 'author')>লেখক (Author)</option>
                            <option value="seller" @selected(old('role', $user->role) === 'seller')>সেলার (Seller)</option>
                            <option value="publisher" @selected(old('role', $user->role) === 'publisher')>প্রকাশক (Publisher)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">অনুমোদন স্ট্যাটাস <span class="text-danger">*</span></label>
                        <select name="reg_status" class="form-select rounded-3">
                            <option value="pending" @selected(old('reg_status', $user->reg_status) === 'pending')>⏳ অপেক্ষমান (Pending)</option>
                            <option value="approved" @selected(old('reg_status', $user->reg_status) === 'approved')>✅ অনুমোদিত (Approved)</option>
                            <option value="rejected" @selected(old('reg_status', $user->reg_status) === 'rejected')>❌ প্রত্যাখ্যাত (Rejected)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" value="1" @checked(old('is_active', $user->is_active))>
                            <label class="form-check-label small fw-semibold text-dark" for="isActiveCheck">
                                অ্যাকাউন্ট সক্রিয় রাখুন (অনুমোদিত হলে লগইন করতে পারবে)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 3. ROLE-SPECIFIC DETAILED APPLICATION FIELDS              --}}
                {{-- ========================================================= --}}
                <div id="authorFieldsSection" class="role-section">
                    <h6 class="fw-bold text-dark mb-3 pb-1 border-bottom">
                        <i class="fas fa-pen-fancy text-success me-1"></i> লেখক সম্পর্কিত তথ্য
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">লেখকের ছদ্মনাম / পেন-নেম</label>
                            <input type="text" name="pen_name" class="form-control rounded-3" value="{{ old('pen_name', $regData['pen_name'] ?? '') }}" placeholder="ঐচ্ছিক">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">সাহিত্য ঘরানা / বিষয়</label>
                            <input type="text" name="genre" class="form-control rounded-3" value="{{ old('genre', $regData['genre'] ?? '') }}" placeholder="যেমন: উপন্যাস, কবিতা, অনুবাদ, গবেষণা...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ব্যক্তিগত ওয়েবসাইট / পোর্টফোলিও</label>
                            <input type="url" name="website" class="form-control rounded-3" value="{{ old('website', $regData['website'] ?? '') }}" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">জাতীয় পরিচয়পত্র নম্বর (NID)</label>
                            <input type="text" name="nid" class="form-control rounded-3 font-monospace" value="{{ old('nid', $regData['nid'] ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">লেখকের পরিচিতি ও সংক্ষিপ্ত জীবনবৃত্তান্ত (Bio)</label>
                            <textarea name="bio" rows="4" class="form-control rounded-3" placeholder="লেখকের কর্মজীবন, সাহিত্যকর্ম ও পরিচিতি...">{{ old('bio', $regData['bio'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Seller / Publisher Fields --}}
                <div id="businessFieldsSection" class="role-section">
                    <h6 class="fw-bold text-dark mb-3 pb-1 border-bottom">
                        <i class="fas fa-building text-info me-1"></i> ব্যবসা ও প্রকাশনী সংক্রান্ত তথ্য
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">দোকান / ব্যবসার নাম (সেলারদের জন্য)</label>
                            <input type="text" name="shop_name" class="form-control rounded-3" value="{{ old('shop_name', $regData['shop_name'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">প্রকাশনীর নাম (প্রকাশকদের জন্য)</label>
                            <input type="text" name="publisher_name" class="form-control rounded-3" value="{{ old('publisher_name', $regData['publisher_name'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ট্রেড লাইসেন্স নম্বর</label>
                            <input type="text" name="trade_license" class="form-control rounded-3 font-monospace" value="{{ old('trade_license', $regData['trade_license'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">জাতীয় পরিচয়পত্র নম্বর (NID)</label>
                            <input type="text" name="nid" class="form-control rounded-3 font-monospace" value="{{ old('nid', $regData['nid'] ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ঠিকানা</label>
                            <textarea name="address" rows="2" class="form-control rounded-3">{{ old('address', $regData['address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-light border px-4 rounded-pill">বাতিল</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold" id="btnSubmit">
                        <i class="fas fa-save me-1"></i> তথ্য ও ছবি সংরক্ষণ করুন
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
            box.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
