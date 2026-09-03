@extends('layouts.admin')

@section('title', 'এডমিন প্রোফাইল ও কাস্টমাইজেশন — আইডিয়া প্রকাশন')
@section('heading', 'এডমিন প্রোফাইল ও সেটিংস')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active" aria-current="page">প্রোফাইল কাস্টমাইজেশন</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        {{-- Left Column: Profile Summary & Avatar Card --}}
        <div class="col-12 col-lg-4">
            <div class="adm-card text-center p-4 shadow-sm border-0 position-relative mb-4">
                <div class="position-absolute top-0 end-0 p-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                        <i class="fas fa-circle-check me-1"></i>সক্রিয়
                    </span>
                </div>

                {{-- Avatar Image / Initial Badge --}}
                <div class="mb-3 position-relative d-inline-block">
                    @if($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                             class="rounded-circle shadow-md object-fit-cover border border-3 border-primary" 
                             style="width: 110px; height: 110px;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-md mx-auto" 
                             style="width: 110px; height: 110px; font-size: 2.8rem; font-weight: 700;">
                            {{ mb_substr(trim($user->name ?? 'A'), 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 18px; height: 18px;" title="Online"></span>
                </div>

                <h4 class="fw-bold mb-1 text-dark">{{ $user->name }}</h4>
                <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 mb-2 font-monospace">
                    <i class="fas fa-shield-halved me-1"></i>{{ ['admin' => 'সুপার এডমিন (Super Admin)', 'sub_admin' => 'সাব-এডমিন', 'seller' => 'সেলার'][$user->role] ?? ucfirst($user->role) }}
                </div>
                
                @if(!empty($user->reg_data['designation']))
                    <p class="text-muted small fw-semibold mb-2">{{ $user->reg_data['designation'] }}</p>
                @endif

                <div class="border-top pt-3 mt-3 text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-envelope me-1.5 text-primary"></i>ইমেইল:</span>
                        <span class="fw-semibold text-dark">{{ $user->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-phone me-1.5 text-success"></i>মোবাইল:</span>
                        <span class="fw-semibold text-dark">{{ $user->phone ?: '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-calendar-check me-1.5 text-info"></i>যুক্ত হয়েছেন:</span>
                        <span class="fw-semibold text-dark">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-fingerprint me-1.5 text-secondary"></i>ইউজার আইডি:</span>
                        <span class="fw-semibold font-monospace text-dark">#ADM-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                @if($user->avatar)
                    <form method="POST" action="{{ route('admin.profile.avatar.remove') }}" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill" onclick="return confirm('আপনি কি প্রোফাইল ছবি মুছে ফেলতে চান?');">
                            <i class="fas fa-trash-can me-1"></i> ছবি মুছে ফেলুন
                        </button>
                    </form>
                @endif
            </div>

            {{-- Quick System Shortcuts --}}
            <div class="adm-card p-3 shadow-sm border-0">
                <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-bolt text-warning me-2"></i>প্রয়োজনীয় লিংক</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-key text-primary me-2"></i>রোল ও পারমিশন ম্যাট্রিক্স</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('admin.users.security.index') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user-lock text-danger me-2"></i>লগইন সিকিউরিটি ও আইপি ব্লকলিস্ট</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('admin.backup.index') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-database text-info me-2"></i>ডাটাবেজ ব্যাকআপ ও রিস্টোর</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('admin.cache.manage') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-broom text-success me-2"></i>সিস্টেম ক্যাশ ক্লিনার</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Multi-Dimensional Tabs & Forms --}}
        <div class="col-12 col-lg-8">
            <div class="adm-card p-0 shadow-sm border-0 overflow-hidden">
                {{-- Navigation Tabs --}}
                <ul class="nav nav-tabs nav-fill bg-light px-3 pt-3 border-bottom" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2.5" id="tab-general" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-user-gear me-1.5 text-primary"></i>সাধারণ তথ্য
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-preferences" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                            <i class="fas fa-sliders me-1.5 text-success"></i>কাস্টমাইজেশন ও পছন্দ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-signature" data-bs-toggle="tab" data-bs-target="#signature" type="button" role="tab">
                            <i class="fas fa-signature me-1.5 text-info"></i>স্বাক্ষর ও সিল
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-security" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="fas fa-lock me-1.5 text-danger"></i>পাসওয়ার্ড ও সিকিউরিটি
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-logs" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                            <i class="fas fa-list-check me-1.5 text-warning"></i>লগইন হিস্ট্রি
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="profileTabsContent">
                    {{-- Tab 1: General Info & Avatar Upload --}}
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">পুরো নাম (ডিসপ্লে নাম হিসেবে প্রদর্শিত হবে) <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="যেমন: আপনার আসল নাম বা প্রতিষ্ঠানের নাম">
                                    <small class="text-muted d-block mt-1"><i class="fas fa-circle-info text-primary me-1"></i>এটি সাইট ও ড্যাশবোর্ডে আপনার প্রোফাইল নাম হিসেবে প্রদর্শিত হবে।</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">অফিসিয়াল ইমেইল (Email) <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control font-monospace" value="{{ old('email', $user->email) }}" required placeholder="adideabd@gmail.com">
                                    <small class="text-muted d-block mt-1"><i class="fas fa-shield-check text-success me-1"></i>লগইনের প্রধান আইডি হিসেবে ব্যবহৃত হবে।</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">মোবাইল নম্বর (Phone)</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="017XXXXXXXX">
                                    <small class="text-muted d-block mt-1"><i class="fas fa-mobile-screen text-info me-1"></i>লগইন করার সময় এই মোবাইল নম্বরটিও ব্যবহার করতে পারবেন।</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">পদবী / ডেসিগনেশন (Designation)</label>
                                    <input type="text" name="designation" class="form-control" value="{{ old('designation', $user->reg_data['designation'] ?? '') }}" placeholder="যেমন: প্রকাশক ও প্রধান নির্বাহী / সিস্টেম এডমিন">
                                    <small class="text-muted d-block mt-1">প্রোফাইলে পদবী হিসেবে প্রদর্শিত হবে।</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-dark">প্রোফাইল ছবি পরিবর্তন (Upload New Avatar)</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
                                    <small class="text-muted">অনুমোদিত ফরম্যাট: JPG, PNG, WebP, SVG (সর্বোচ্চ ৩ মেগাবাইট)</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-dark">সংক্ষিপ্ত বায়ো বা পরিচিতি (Bio / Summary)</label>
                                    <textarea name="bio" class="form-control" rows="3" placeholder="এডমিন সম্পর্কে সংক্ষিপ্ত বিবরণ...">{{ old('bio', $user->reg_data['bio'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                        <i class="fas fa-floppy-disk me-1.5"></i>প্রোফাইল সংরক্ষণ করুন
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Tab 2: Customization & Preferences --}}
                    <div class="tab-pane fade" id="preferences" role="tabpanel">
                        <form method="POST" action="{{ route('admin.profile.preferences') }}">
                            @csrf
                            
                            {{-- 1. UI Layout & View Customization --}}
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-desktop me-2 text-primary"></i>ড্যাশবোর্ড ও ডিসপ্লে প্রেফারেন্স</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">লগইন করার পর ডিফল্ট ল্যান্ডিং পেজ</label>
                                    <select name="landing_page" class="form-select">
                                        <option value="admin.dashboard" @selected(($preferences['landing_page'] ?? '') === 'admin.dashboard')>এডমিন ড্যাশবোর্ড (Dashboard)</option>
                                        <option value="admin.pos.index" @selected(($preferences['landing_page'] ?? '') === 'admin.pos.index')>বইমেলা স্টল পিওএস (Boi Mela POS)</option>
                                        <option value="admin.ecommerce-orders" @selected(($preferences['landing_page'] ?? '') === 'admin.ecommerce-orders')>বুক অর্ডার্স (Book Orders)</option>
                                        <option value="admin.books" @selected(($preferences['landing_page'] ?? '') === 'admin.books')>বই ক্যাটালগ (Books Catalog)</option>
                                        <option value="admin.accounting.index" @selected(($preferences['landing_page'] ?? '') === 'admin.accounting.index')>আইডিয়া অ্যাকাউন্টিং (Accounting)</option>
                                        <option value="subadmin.bills.index" @selected(($preferences['landing_page'] ?? '') === 'subadmin.bills.index')>সেলার বিলিং প্যানেল (Seller Bills)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">থিম মোড (Color Scheme)</label>
                                    <select name="theme" class="form-select">
                                        <option value="auto" @selected(($preferences['theme'] ?? '') === 'auto')>সিস্টেম অটো (Auto System Detect)</option>
                                        <option value="light" @selected(($preferences['theme'] ?? '') === 'light')>লাইট মোড (Light Mode)</option>
                                        <option value="dark" @selected(($preferences['theme'] ?? '') === 'dark')>ডার্ক মোড (Dark Mode)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-dark">টেবিলের ডিফল্ট রো সংখ্যা (Rows Per Page)</label>
                                    <select name="table_per_page" class="form-select">
                                        <option value="10" @selected(($preferences['table_per_page'] ?? 20) == 10)>১০ টি রো</option>
                                        <option value="20" @selected(($preferences['table_per_page'] ?? 20) == 20)>২০ টি রো (স্ট্যান্ডার্ড)</option>
                                        <option value="50" @selected(($preferences['table_per_page'] ?? 20) == 50)>৫০ টি রো</option>
                                        <option value="100" @selected(($preferences['table_per_page'] ?? 20) == 100)>১০০ টি রো</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-dark">সংখ্যা ও ফরম্যাট (Numeral Locale)</label>
                                    <select name="number_format" class="form-select">
                                        <option value="bengali" @selected(($preferences['number_format'] ?? 'bengali') === 'bengali')>বাংলা সংখ্যা (যেমন: ১২,৫০০)</option>
                                        <option value="english" @selected(($preferences['number_format'] ?? '') === 'english')>ইংরেজি সংখ্যা (যেমন: 12,500)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-dark">অডিও ফিডব্যাক ও সাউন্ড</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="sound_effects" id="soundEffects" value="1" @checked(!empty($preferences['sound_effects']))>
                                        <label class="form-check-label fw-semibold text-dark small" for="soundEffects">
                                            POS স্ক্যান ও অর্ডারে সাউন্ড
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- 2. Instant Alerts & External Messaging --}}
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-bell me-2 text-warning"></i>স্বয়ংক্রিয় নোটিফিকেশন ও মেসেজিং অ্যালার্ট</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">টেলিগ্রাম বট চ্যাট আইডি (Telegram Chat ID)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-primary"><i class="fab fa-telegram"></i></span>
                                        <input type="text" name="telegram_chat_id" class="form-control font-monospace" placeholder="যেমন: 123456789" value="{{ $preferences['telegram_chat_id'] ?? '' }}">
                                    </div>
                                    <small class="text-muted">সার্ভার ব্যাকআপ ও বড় অর্ডারের রিয়েল-টাইম টেলিগ্রাম পুশ অ্যালার্ট</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">জরুরি অ্যালার্টের হোয়াটসঅ্যাপ নম্বর</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fab fa-whatsapp"></i></span>
                                        <input type="text" name="whatsapp_alerts" class="form-control font-monospace" placeholder="017XXXXXXXX" value="{{ $preferences['whatsapp_alerts'] ?? '' }}">
                                    </div>
                                    <small class="text-muted">জরুরি পেমেন্ট বা সিকিউরিটি ইস্যু আসলে মেসেজ যাবে</small>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_orders" id="notifyOrders" value="1" @checked(!empty($preferences['notify_orders']))>
                                    <label class="form-check-label fw-semibold text-dark small" for="notifyOrders">
                                        নতুন বই অর্ডার আসলে তাৎক্ষণিক ইমেইল অ্যালার্ট পান
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_registrations" id="notifyReg" value="1" @checked(!empty($preferences['notify_registrations']))>
                                    <label class="form-check-label fw-semibold text-dark small" for="notifyReg">
                                        নতুন লেখক, প্রকাশক বা সেলারের রেজিস্ট্রেশন আবেদন আসলে অ্যালার্ট পান
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_tickets" id="notifyTickets" value="1" @checked(!empty($preferences['notify_tickets']))>
                                    <label class="form-check-label fw-semibold text-dark small" for="notifyTickets">
                                        জরুরি হেল্পডেস্ক সাপোর্ট টিকিট আসলে অ্যালার্ট পান
                                    </label>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- 3. IP Whitelist Security Binding --}}
                            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-network-wired me-2 text-danger"></i>আইপি রেস্ট্রিকশন ও হোয়াইটলিস্ট (IP Whitelist)</h6>
                            <p class="text-muted small mb-2">নির্দিষ্ট আইপি ছাড়া অন্য কোথাও থেকে এই এডমিন একাউন্টে লগইন ব্লক করতে কমা দিয়ে আইপি দিন (ঐচ্ছিক):</p>
                            <input type="text" name="ip_whitelist" class="form-control font-monospace mb-4" placeholder="যেমন: 103.145.12.5, 127.0.0.1 (ফাঁকা রাখলে যেকোনো আইপি থেকে লগইন করা যাবে)" value="{{ $preferences['ip_whitelist'] ?? '' }}">

                            <div class="text-end">
                                <button type="submit" class="btn btn-success px-4 shadow-sm">
                                    <i class="fas fa-check me-1.5"></i>প্রেফারেন্স সংরক্ষণ করুন
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab 3: Digital Signature & Official Seal --}}
                    <div class="tab-pane fade" id="signature" role="tabpanel">
                        <div class="row g-4 align-items-center">
                            <div class="col-12 col-md-5 text-center">
                                <div class="p-3 bg-light rounded-3 border">
                                    <p class="fw-bold small text-dark mb-2">বর্তমান ডিজিটাল স্বাক্ষর ও সিল প্রিভিউ</p>
                                    @if(!empty($user->reg_data['signature']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->reg_data['signature']))
                                        <div class="p-3 bg-white border rounded shadow-xs mb-2 d-inline-block">
                                            <img src="{{ asset('storage/' . $user->reg_data['signature']) }}" alt="Signature" style="max-height: 90px; max-width: 220px;">
                                        </div>
                                        <form method="POST" action="{{ route('admin.profile.signature.remove') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('আপনি কি ডিজিটাল স্বাক্ষর মুছে ফেলতে চান?');">
                                                <i class="fas fa-trash-can me-1"></i>স্বাক্ষর মুছে ফেলুন
                                            </button>
                                        </form>
                                    @else
                                        <div class="p-4 border border-dashed rounded bg-white text-muted">
                                            <i class="fas fa-file-signature fs-2 text-muted mb-2 d-block opacity-50"></i>
                                            কোনো স্বাক্ষর যুক্ত করা নেই।
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-signature text-info me-2"></i>ডিজিটাল স্বাক্ষর ও সিল আপলোড</h6>
                                <p class="text-muted small mb-3">
                                    এখানে আপনার স্বাক্ষর বা প্রাতিষ্ঠানিক সিল আপলোড করলে তা স্বয়ংক্রিয়ভাবে অফিসিয়াল ইনভয়েস, মানি রিসিট, চালান ও পেরোল স্যালারি ভাউচারে প্রিন্ট হবে।
                                </p>

                                <form method="POST" action="{{ route('admin.profile.signature') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="file" name="signature" class="form-control" accept="image/png,image/webp,image/svg+xml" required>
                                        <small class="text-muted">স্বচ্ছ ব্যাকগ্রাউন্ড (Transparent PNG/SVG) অনুমোদিত (সর্বোচ্চ ২ মেগাবাইট)</small>
                                    </div>
                                    <button type="submit" class="btn btn-info text-white px-4">
                                        <i class="fas fa-cloud-arrow-up me-1.5"></i>স্বাক্ষর আপলোড করুন
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 4: Security & Password --}}
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <form method="POST" action="{{ route('admin.profile.password') }}">
                            @csrf
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-key me-2 text-danger"></i>পাসওয়ার্ড পরিবর্তন</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-dark">বর্তমান পাসওয়ার্ড (Current Password) <span class="text-danger">*</span></label>
                                    <input type="password" name="current_password" class="form-control" required placeholder="আপনার বর্তমান পাসওয়ার্ড দিন">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">নতুন পাসওয়ার্ড (New Password) <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required placeholder="কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">নতুন পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" required placeholder="পাসওয়ার্ড পুনরায় লিখুন">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="fas fa-lock me-1.5"></i>পাসওয়ার্ড পরিবর্তন করুন
                                    </button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        {{-- Logout from Other Devices --}}
                        <div class="p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="fas fa-laptop-code me-2 text-primary"></i>অন্যান্য ডিভাইস ও ব্রাউজার থেকে লগআউট</h6>
                            <p class="text-muted small mb-3">আপনি যদি অন্য কোনো কম্পিউটার বা মোবাইলে লগইন করা থাকেন, তবে এখান থেকে সমস্ত সেশন বাতিল করতে পারেন।</p>
                            
                            <form method="POST" action="{{ route('admin.profile.logout-others') }}" class="d-flex flex-wrap gap-2">
                                @csrf
                                <input type="password" name="password" class="form-control form-control-sm" placeholder="নিশ্চিত করতে পাসওয়ার্ড দিন" required style="max-width: 250px;">
                                <button type="submit" class="btn btn-sm btn-outline-dark" onclick="return confirm('আপনি কি নিশ্চিত যে অন্য সমস্ত ডিভাইস থেকে লগআউট করতে চান?');">
                                    <i class="fas fa-right-from-bracket me-1"></i>লগআউট অল
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Tab 5: Login Audit Trail --}}
                    <div class="tab-pane fade" id="logs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-clock-rotate-left me-2 text-primary"></i>সাম্প্রতিক লগইন ও সিকিউরিটি লগ</h6>
                            <span class="badge bg-light text-muted border">শেষ ৮টি অ্যাক্টিভিটি</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>তারিখ ও সময়</th>
                                        <th>আইপি এড্রেস</th>
                                        <th>ডিভাইস / ব্রাউজার</th>
                                        <th class="text-center">স্ট্যাটাস</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($securityLogs as $log)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                            <td><span class="font-monospace text-primary">{{ $log->ip_address }}</span></td>
                                            <td class="text-truncate" style="max-width: 250px;" title="{{ $log->user_agent }}">
                                                {{ $log->user_agent ? Str::limit($log->user_agent, 40) : 'Chrome / Windows' }}
                                            </td>
                                            <td class="text-center">
                                                @if($log->status === 'success' || empty($log->is_blocked))
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">সফল লগইন</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">ব্যর্থ চেষ্টা</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fas fa-shield-halved fs-3 mb-2 d-block opacity-50"></i>
                                                কোনো সন্দেহজনক লগইন পাওয়া যায়নি।
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab deep linking based on URL Hash (#preferences, #security, #signature, #logs, #general)
    document.addEventListener('DOMContentLoaded', function () {
        let hash = window.location.hash;
        if (hash) {
            let triggerEl = document.querySelector(`#profileTabs button[data-bs-target="${hash}"]`);
            if (triggerEl) {
                let tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Update URL hash on tab switch
        document.querySelectorAll('#profileTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
            tabBtn.addEventListener('shown.bs.tab', function (e) {
                let targetId = e.target.getAttribute('data-bs-target');
                if (history.pushState) {
                    history.pushState(null, null, targetId);
                } else {
                    location.hash = targetId;
                }
            });
        });
    });
</script>
@endpush
@endsection
