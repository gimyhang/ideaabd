@extends('layouts.admin')

@section('title', 'Edit Registration — ' . $user->name)
@section('heading', 'Edit Registration & Photo Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">Registrations</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.show', $user) }}" class="text-decoration-none">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('actions')
    <div class="d-flex gap-2 align-items-center flex-wrap">
        @if($user->role === 'author' || $user->reg_type === 'author')
            @php
                $authorRec = $user->getAuthorRecord();
            @endphp
            <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-bold shadow-xs" id="btnHeaderSyncAuthor" onclick="syncThisAuthor()">
                <i class="fas fa-arrows-rotate me-1"></i> Sync to Directory
            </button>
            @if($authorRec && $authorRec->slug)
                <a href="{{ route('authors.show', $authorRec->slug) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> Directory View
                </a>
            @endif
        @endif
        <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('admin.registrations.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">
            <i class="fas fa-list me-1"></i> All Requests
        </a>
    </div>
@endsection

@section('content')
<div style="max-width: 920px;" class="mx-auto mb-5">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light border-0 py-3.5 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-user-pen text-primary me-2"></i> Edit Applicant & Profile Information
                </h5>
                <small class="text-muted">User ID: #{{ $user->id }} • Submitted: {{ $user->created_at ? $user->created_at->format('d M, Y') : 'N/A' }}</small>
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
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger rounded-4 mb-4 border-0 shadow-xs">
                    <div class="fw-bold mb-1 d-flex align-items-center gap-1.5">
                        <i class="fas fa-circle-exclamation text-danger"></i>
                        <span>Please fix the following errors:</span>
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
                    $author = $user->getAuthorRecord();
                    $regData = is_array($user->reg_data) ? $user->reg_data : [];
                    $rawAvatar = $user->avatar ?: ($author?->avatar ?: ($regData['avatar'] ?? null));
                    $currAvatar = null;
                    if ($rawAvatar) {
                        $currAvatar = str_starts_with($rawAvatar, 'http') ? $rawAvatar : asset('storage/' . ltrim($rawAvatar, '/'));
                    }
                    $currBio = old('bio', $regData['bio'] ?? ($author?->bio ?? ''));
                    $currPenName = old('pen_name', $regData['pen_name'] ?? ($author && $author->name !== $user->name ? $author->name : ''));
                    $currGenre = old('genre', is_array($regData['genre'] ?? null) ? implode(', ', $regData['genre']) : ($regData['genre'] ?? ($author?->genre ?? '')));
                    $currPhone = old('phone', $user->phone ?: ($author?->phone ?? ''));
                    $currFatherName = old('father_name', $regData['father_name'] ?? '');
                    $currMotherName = old('mother_name', $regData['mother_name'] ?? '');
                    $currNid = old('nid', $regData['nid'] ?? ($regData['nid_or_passport'] ?? ''));
                    $currAddress = old('present_address', $regData['present_address'] ?? ($regData['address'] ?? ''));
                    $currPayout = old('payout_number', $regData['payout_number'] ?? ($author?->payout_account_details ?? ''));
                    $currWebsite = old('website', $regData['website'] ?? ($author?->website ?? ''));
                @endphp

                {{-- ========================================================= --}}
                {{-- 1. PHOTO / AVATAR WITH TOUCH PHOTO STUDIO CROPPING        --}}
                {{-- ========================================================= --}}
                <div class="p-3.5 bg-light rounded-4 border mb-4">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3.5">
                        
                        {{-- Avatar Live Preview Frame with Click-to-Studio --}}
                        <div class="position-relative flex-shrink-0 cursor-pointer" onclick="openAdminPhotoStudio()" title="ছবি পরিবর্তন বা এডিট করতে ক্লিক করুন" style="cursor: pointer;">
                            <div class="rounded-circle overflow-hidden shadow-sm border border-3 border-white position-relative bg-white" 
                                 style="width: 100px; height: 100px; min-width: 100px; min-height: 100px; aspect-ratio: 1 / 1;" id="avatarPreviewBox">
                                @if($currAvatar)
                                    <img src="{{ $currAvatar }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover" id="currentAvatarDisplayImg">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-2 fw-bold bg-primary-subtle" id="avatarInitialPlaceholder">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <span class="position-absolute bottom-0 end-0 bg-warning text-dark rounded-circle p-1.5 shadow-xs border border-2 border-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="fas fa-camera small" style="font-size: 11px;"></i>
                            </span>
                        </div>

                        {{-- Action Buttons & Hidden Inputs --}}
                        <div class="flex-grow-1 w-100">
                            <label class="form-label fw-bold text-dark mb-1.5 d-flex align-items-center justify-content-between">
                                <span class="d-flex align-items-center gap-1.5">
                                    <i class="fas fa-camera text-primary"></i>
                                    <span>User Photo / Avatar</span>
                                </span>
                                <span class="badge bg-white text-success border small" id="avatarSelectedStatus" style="display: none;">
                                    <i class="fas fa-check-circle me-1"></i>নতুন ছবি রেডি
                                </span>
                            </label>
                            
                            {{-- Buttons for Studio & Direct File Pick --}}
                            <div class="d-flex flex-wrap gap-2 mb-1.5">
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-xs" onclick="openAdminPhotoStudio()">
                                    <i class="fas fa-crop-simple me-1"></i> ফটো স্টুডিও ও ক্রপার
                                </button>
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold mb-0" style="cursor: pointer;">
                                    <i class="fas fa-camera me-1"></i> মোবাইল ক্যামেরা
                                    <input type="file" accept="image/*" capture="user" class="d-none" onchange="handleDirectFilePick(this)">
                                </label>
                                <label class="btn btn-light border btn-sm rounded-pill px-3 py-1.5 fw-semibold mb-0" style="cursor: pointer;">
                                    <i class="fas fa-images me-1"></i> ফাইল বাছাই
                                    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/jpg,image/webp,image/heic,image/heif" class="d-none" onchange="handleDirectFilePick(this)">
                                </label>
                            </div>

                            {{-- Hidden Cropped Avatar Data --}}
                            <input type="hidden" name="avatar_cropped" id="regAvatarCroppedInput">

                            <div class="text-muted small" style="font-size: 0.76rem;">
                                মোবাইল বা ক্যামেরা থেকে তোলা ছবি স্বয়ংক্রিয়ভাবে কম্প্রেস ও অপ্টিমাইজ হয়ে সেভ হবে। (JPG, PNG, WebP)
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. ACCOUNT & CREDENTIALS                                  --}}
                {{-- ========================================================= --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-id-card-clip text-primary"></i>
                        <span>Account</span>
                    </h6>

                    <div class="row g-3">
                        {{-- Row 1: Author Name (Bangla) & Author Name (English) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Author Name (bangla) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name_bn" class="form-control rounded-3" 
                                   value="{{ old('name_bn', $regData['name_bn'] ?? ($regData['name_bangla'] ?? (preg_match('/[\x{0980}-\x{09FF}]/u', $user->name) ? $user->name : ''))) }}" 
                                   placeholder="বাংলায় লেখক নাম (যেমন: সাকিল মাসুদ)" required>
                            <div class="form-text small text-muted" style="font-size: 11px;">(এই নামটা ব্লগ ও বইয়ে শো করবে)</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Author Name (english) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control rounded-3" 
                                   value="{{ old('name', $regData['name_en'] ?? ($regData['name_english'] ?? (!preg_match('/[\x{0980}-\x{09FF}]/u', $user->name) ? $user->name : ''))) }}" 
                                   placeholder="Author Name in English (e.g. Sakil Masud)" required>
                        </div>

                        {{-- Row 2: Full Name & Pen Name --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Full Name:
                            </label>
                            <input type="text" name="full_name" class="form-control rounded-3" value="{{ old('full_name', $regData['full_name'] ?? '') }}" placeholder="Full Name (Official / NID)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Pen Name:
                            </label>
                            <input type="text" name="pen_name" class="form-control rounded-3" 
                                   value="{{ old('pen_name', $regData['pen_name'] ?? '') }}" 
                                   placeholder="Pseudonym / Literary Alias">
                        </div>

                        {{-- Row 3: Email & Phone --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Email <span class="text-danger">*</span>:
                            </label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Mobile No (uid) <span class="text-danger">*</span>:
                            </label>
                            <input type="text" name="phone" class="form-control rounded-3 font-monospace" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        {{-- Row 4: Role & Status --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select rounded-3" id="roleSelector">
                                <option value="author" @selected(old('role', $user->role) === 'author')>Author</option>
                                <option value="seller" @selected(old('role', $user->role) === 'seller')>Seller</option>
                                <option value="publisher" @selected(old('role', $user->role) === 'publisher')>Publisher</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="reg_status" class="form-select rounded-3">
                                <option value="pending" @selected(old('reg_status', $user->reg_status) === 'pending')>Pending</option>
                                <option value="approved" @selected(old('reg_status', $user->reg_status) === 'approved')>Approved</option>
                                <option value="rejected" @selected(old('reg_status', $user->reg_status) === 'rejected')>Rejected</option>
                            </select>
                        </div>

                        {{-- Active Switch --}}
                        <div class="col-12">
                            <div class="form-check form-switch p-2 bg-light rounded-3 border ps-5">
                                <input class="form-check-input ms-n4" type="checkbox" name="is_active" id="isActiveSwitch" value="1" @checked(old('is_active', $user->is_active))>
                                <label class="form-check-label small fw-semibold text-dark" for="isActiveSwitch">
                                    Active (Account can sign in and manage profile)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 3. INFORMATION & PROFILE DETAILS                          --}}
                {{-- ========================================================= --}}
                <div id="authorDetailsCard" class="mb-4" style="{{ old('role', $user->role) === 'author' ? '' : 'display:none;' }}">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-file-lines text-success"></i>
                        <span>Information</span>
                    </h6>

                    <div class="row g-3">

                        {{-- NID No --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Nid No:
                            </label>
                            <input type="text" name="nid" class="form-control rounded-3 font-monospace" 
                                   value="{{ $currNid }}" 
                                   placeholder="National ID / Passport Number">
                        </div>

                        {{-- NID upload --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
                                <span>Nid upload:</span>
                                @if(!empty($regData['nid_file']))
                                    <a href="{{ asset('storage/' . ltrim($regData['nid_file'], '/')) }}" target="_blank" class="badge bg-primary-subtle text-primary border text-decoration-none">
                                        <i class="fas fa-file-arrow-down me-1"></i> View Current Document
                                    </a>
                                @endif
                            </label>
                            <input type="file" name="nid_file" class="form-control rounded-3" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf">
                        </div>

                        {{-- Writing Topics --}}
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
                                <span>Writing Topics:</span>
                            </label>
                            <input type="text" name="genre" id="adminRegGenreInput" class="form-control rounded-3 mb-1.5" 
                                   value="{{ $currGenre }}" 
                                   placeholder="Fiction, Poetry, Essays, Research...">
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach(['Fiction', 'Poetry', 'Essays', 'Research', 'Novel', 'Non-Fiction', 'Translation', 'Sci-Fi', 'কথাসাহিত্য', 'কবিতা', 'ছড়া', 'প্রবন্ধ', 'গবেষণা', 'ভ্রমণগদ্য', 'অনুবাদ', 'সায়েন্সফিকশন'] as $g)
                                    <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-0.5 shadow-2xs text-secondary small" 
                                            style="font-size: 11px;" onclick="toggleAdminGenre('{{ $g }}')">
                                        <i class="fa-solid fa-plus me-0.5 text-success"></i> {{ $g }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Profession --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Profession
                            </label>
                            <input type="text" name="profession" class="form-control rounded-3" 
                                   value="{{ old('profession', $regData['profession'] ?? ($regData['designation'] ?? '')) }}" 
                                   placeholder="Profession / Designation">
                        </div>

                        {{-- DOB --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                DOB
                            </label>
                            <input type="date" name="dob" class="form-control rounded-3" 
                                   value="{{ old('dob', $regData['dob'] ?? ($regData['birth_date'] ?? '')) }}">
                        </div>

                        {{-- Payout --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Payout
                            </label>
                            <input type="text" name="payout_number" class="form-control rounded-3 font-monospace" 
                                   value="{{ $currPayout }}" 
                                   placeholder="Bkash / Nagad Number">
                        </div>

                        {{-- Address --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Address
                            </label>
                            <input type="text" name="present_address" class="form-control rounded-3" 
                                   value="{{ $currAddress }}" 
                                   placeholder="Present Address">
                        </div>

                        {{-- PermanentAddress --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                PermanentAddress
                            </label>
                            <input type="text" name="permanent_address" class="form-control rounded-3" 
                                   value="{{ old('permanent_address', $regData['permanent_address'] ?? '') }}" 
                                   placeholder="Permanent Address">
                        </div>

                        {{-- Bio --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    Bio
                                </label>
                                <span class="text-muted small fw-semibold" id="bioCounter" style="font-size: 0.75rem;">0 Words • 0 Characters</span>
                            </div>
                            <textarea name="bio" id="authorBioInput" rows="14" class="form-control rounded-3 p-3 font-sans" 
                                      style="min-height: 320px; font-size: 0.95rem; line-height: 1.75;"
                                      placeholder="Author biography, literary achievements, publications, awards and background..." 
                                      oninput="updateCharCount(this, 'bioCounter')">{{ $currBio }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 4. SOCIAL MEDIA & LINKS                                   --}}
                {{-- ========================================================= --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-share-nodes text-info"></i>
                        <span>Social</span>
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Website / Portfolio URL
                            </label>
                            <input type="url" name="website" class="form-control rounded-3" 
                                   value="{{ $currWebsite }}" 
                                   placeholder="https://authorwebsite.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Facebook Profile
                            </label>
                            <input type="url" name="facebook" class="form-control rounded-3" 
                                   value="{{ old('facebook', $regData['facebook'] ?? '') }}" 
                                   placeholder="https://facebook.com/username">
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 5. PUBLISHER / SELLER SPECIFIC DETAILS                     --}}
                {{-- ========================================================= --}}
                <div id="businessDetailsCard" class="mb-4" style="{{ in_array(old('role', $user->role), ['publisher', 'seller']) ? '' : 'display:none;' }}">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-store text-warning"></i>
                        <span>Business & Commercial Details</span>
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6" id="shopNameWrap">
                            <label class="form-label small fw-bold text-dark">
                                Stall / Bookshop Name (Sellers)
                            </label>
                            <input type="text" name="shop_name" class="form-control rounded-3" 
                                   value="{{ old('shop_name', $regData['shop_name'] ?? '') }}" 
                                   placeholder="Bookshop or Stall Name">
                        </div>

                        <div class="col-md-6" id="zoneWrap">
                            <label class="form-label small fw-bold text-dark">
                                Stall Number / Stall Zone
                            </label>
                            <input type="text" name="zone" class="form-control rounded-3" 
                                   value="{{ old('zone', $regData['zone'] ?? '') }}" 
                                   placeholder="Zone A / Stall 123">
                        </div>

                        <div class="col-md-6" id="publisherNameWrap">
                            <label class="form-label small fw-bold text-dark">
                                Publishing House Name (Publishers)
                            </label>
                            <input type="text" name="publisher_name" class="form-control rounded-3" 
                                   value="{{ old('publisher_name', $regData['publisher_name'] ?? '') }}" 
                                   placeholder="Publishing Agency Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Trade License Number
                            </label>
                            <input type="text" name="trade_license" class="form-control rounded-3 font-monospace" 
                                   value="{{ old('trade_license', $regData['trade_license'] ?? '') }}" 
                                   placeholder="Trade License Number">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">
                                Business or Residential Address
                            </label>
                            <textarea name="address" rows="2" class="form-control rounded-3" 
                                      placeholder="Address, Street, City...">{{ old('address', $regData['address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 6. SUBMIT ACTIONS                                         --}}
                {{-- ========================================================= --}}
                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-light border px-4 rounded-pill">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Save Changes & Photo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- ADMIN TOUCH & MOBILE-FRIENDLY PHOTO STUDIO MODAL                            --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="adminAvatarStudioModal" tabindex="-1" aria-labelledby="adminAvatarStudioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3.5 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="adminAvatarStudioModalLabel">
                    <i class="fas fa-camera text-primary"></i>
                    <span>ছবি এডিটর ও ফটো স্টুডিও</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                {{-- Interactive Crop Canvas Container --}}
                {{-- Interactive Crop Canvas Container --}}
                <div class="text-center mb-3">
                    <div class="position-relative mx-auto rounded-4 overflow-hidden border border-2 border-primary shadow-xs bg-light" 
                         style="width: 240px; height: 240px; cursor: grab; touch-action: none;" id="adminCanvasWrapper"
                         ondragover="event.preventDefault(); this.classList.add('border-warning');"
                         ondragleave="this.classList.remove('border-warning');"
                         ondrop="handleAdminPhotoDrop(event)">
                        <canvas id="adminCropCanvas" width="240" height="240" style="display:block; width:240px; height:240px;"></canvas>
                        
                        {{-- Clean, Non-blocking SVG Circular Mask Guide --}}
                        <svg class="position-absolute top-0 start-0 w-100 h-100 pe-none" viewBox="0 0 240 240" style="pointer-events: none; z-index: 5;">
                            <defs>
                                <mask id="adminCropCircleMask">
                                    <rect width="240" height="240" fill="white"/>
                                    <circle cx="120" cy="120" r="115" fill="black"/>
                                </mask>
                            </defs>
                            <rect width="240" height="240" fill="rgba(15, 23, 42, 0.50)" mask="url(#adminCropCircleMask)"/>
                            <circle cx="120" cy="120" r="115" fill="none" stroke="rgba(255, 255, 255, 0.85)" stroke-width="2" stroke-dasharray="6,4"/>
                        </svg>

                        {{-- Initial placeholder when no image uploaded --}}
                        <div id="adminCanvasPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 flex-column align-items-center justify-content-center bg-light text-muted p-3 pointer-events-none text-center" style="display: flex; z-index: 6;">
                            <i class="fas fa-cloud-arrow-up text-primary fs-1 mb-2"></i>
                            <span class="fw-bold text-dark small mb-1">ছবি নির্বাচন বা ড্রপ করুন</span>
                            <span class="text-muted" style="font-size: 11px;">মোবাইল ক্যামেরা ও গ্যালারি সাপোর্টেড</span>
                        </div>
                    </div>
                    <div class="text-muted small mt-1.5" style="font-size: 11.5px;">
                        <i class="fas fa-hand-pointer text-secondary me-1"></i>মাউস বা আঙুল দিয়ে টেনে ছবির পজিশন ঠিক করুন
                    </div>
                </div>

                {{-- File Picker Options inside modal --}}
                <div class="mb-3">
                    <div class="d-flex gap-2">
                        <label class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill fw-semibold py-1.5" style="cursor: pointer;">
                            <i class="fas fa-images me-1"></i> গ্যালারি থেকে সিলেক্ট করুন
                            <input type="file" id="adminModalAvatarInput" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp,image/heic,image/heif" 
                                   class="d-none"
                                   onclick="this.value=null;"
                                   onchange="loadAdminStudioImage(this)">
                        </label>
                        <label class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold py-1.5 px-3" style="cursor: pointer;" title="ক্যামেরা থেকে ছবি তুলুন">
                            <i class="fas fa-camera me-1"></i> ক্যামেরা
                            <input type="file" accept="image/*" capture="user" class="d-none" onclick="this.value=null;" onchange="loadAdminStudioImage(this)">
                        </label>
                    </div>
                </div>

                {{-- Interactive Controls: Zoom Slider, Rotate, Reset --}}
                <div id="adminCropControls" class="p-3 bg-light rounded-3 border mb-3 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 11.5px;">
                        <span class="text-muted fw-semibold"><i class="fas fa-magnifying-glass-plus text-primary me-1"></i>জুম ইন/আউট:</span>
                        <span class="badge bg-white text-dark border font-monospace" id="adminZoomValBadge">100%</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-white border rounded-circle p-1" style="width:28px;height:28px;" onclick="adjustAdminZoom(-0.1)" title="Zoom Out"><i class="fa-solid fa-minus" style="font-size:10px;"></i></button>
                        <input type="range" class="form-range flex-grow-1" id="adminZoomSlider" min="0.01" max="5.0" step="0.01" value="1" oninput="onAdminZoomChange(this.value)">
                        <button type="button" class="btn btn-sm btn-white border rounded-circle p-1" style="width:28px;height:28px;" onclick="adjustAdminZoom(0.1)" title="Zoom In"><i class="fa-solid fa-plus" style="font-size:10px;"></i></button>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 text-dark small" onclick="rotateAdminImage(90)">
                            <i class="fas fa-rotate-right me-1 text-primary"></i> ৯০° ঘোরান
                        </button>
                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 text-dark small" onclick="resetAdminCrop()">
                            <i class="fas fa-arrows-to-circle me-1 text-secondary"></i> রিসেট
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-3.5 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="adminApplyPhotoBtn" onclick="applyCroppedPhotoToForm()" disabled>
                    <i class="fas fa-check me-1"></i> ছবি সেট করুন
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/* =========================================================================
   ADMIN TOUCH PHOTO STUDIO CROPPER & TOUCH ENGINE
   ========================================================================= */
let studioCanvas = document.getElementById('adminCropCanvas');
let studioCtx = studioCanvas ? studioCanvas.getContext('2d') : null;
let studioCurrentImg = null;
let studioImgX = 120;
let studioImgY = 120;
let studioScale = 1;
let studioRotation = 0;
let studioIsDragging = false;
let studioStartX, studioStartY;
let studioCroppedDataUrl = null;

function openAdminPhotoStudio() {
    const modalEl = document.getElementById('adminAvatarStudioModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => {
            if (!studioCanvas) studioCanvas = document.getElementById('adminCropCanvas');
            if (studioCanvas && !studioCtx) studioCtx = studioCanvas.getContext('2d');
            if (studioCurrentImg) renderAdminCanvas();
        }, 200);
    }
}

function handleDirectFilePick(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // 1. Immediately show live thumbnail preview in avatarPreviewBox
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewBox = document.getElementById('avatarPreviewBox');
            if (previewBox) {
                previewBox.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover">`;
            }
            const statusBadge = document.getElementById('avatarSelectedStatus');
            if (statusBadge) {
                statusBadge.style.display = 'inline-block';
            }
        };
        reader.readAsDataURL(file);

        // 2. Open studio modal for fine crop
        openAdminPhotoStudio();
        loadAdminStudioImage(input);
    }
}

function handleAdminPhotoDrop(e) {
    e.preventDefault();
    const wrapper = document.getElementById('adminCanvasWrapper');
    if (wrapper) wrapper.classList.remove('border-warning');
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        const file = e.dataTransfer.files[0];
        if (!file.type.match('image.*')) {
            alert('অনুগ্রহ করে শুধুমাত্র ইমেজ ফাইল (JPG, PNG, WebP) ড্রপ করুন।');
            return;
        }
        const fakeInput = { files: [file] };
        handleDirectFilePick(fakeInput);
    }
}

function loadAdminStudioImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const dataUri = e.target.result;
            studioCurrentImg = new Image();
            studioCurrentImg.onload = function() {
                studioCanvas = document.getElementById('adminCropCanvas');
                if (studioCanvas) {
                    studioCanvas.width = 240;
                    studioCanvas.height = 240;
                    studioCtx = studioCanvas.getContext('2d');
                }

                const placeholder = document.getElementById('adminCanvasPlaceholder');
                if (placeholder) {
                    placeholder.classList.add('d-none');
                    placeholder.style.display = 'none';
                }
                
                const controls = document.getElementById('adminCropControls');
                if (controls) {
                    controls.classList.remove('d-none');
                    controls.style.display = 'block';
                }
                
                const applyBtn = document.getElementById('adminApplyPhotoBtn');
                if (applyBtn) applyBtn.disabled = false;
                
                const canvasW = 240;
                const canvasH = 240;
                const scaleW = canvasW / studioCurrentImg.width;
                const scaleH = canvasH / studioCurrentImg.height;
                studioScale = Math.max(scaleW, scaleH);
                
                const slider = document.getElementById('adminZoomSlider');
                if (slider) {
                    const minScale = Math.min(scaleW, scaleH) * 0.4;
                    const maxScale = Math.max(scaleW, scaleH) * 4.0;
                    slider.min = Math.max(0.001, minScale).toFixed(4);
                    slider.max = Math.max(minScale + 0.1, maxScale).toFixed(4);
                    slider.step = ((parseFloat(slider.max) - parseFloat(slider.min)) / 100).toFixed(4);
                    slider.value = studioScale;
                }
                const badge = document.getElementById('adminZoomValBadge');
                if (badge) badge.textContent = '100%';
                
                studioImgX = canvasW / 2;
                studioImgY = canvasH / 2;
                studioRotation = 0;
                
                renderAdminCanvas();
                exportAdminCroppedAvatar();
            };
            studioCurrentImg.src = dataUri;
        };
        reader.readAsDataURL(file);
    }
}

function renderAdminCanvas() {
    if (!studioCanvas) studioCanvas = document.getElementById('adminCropCanvas');
    if (studioCanvas && !studioCtx) studioCtx = studioCanvas.getContext('2d');
    if (!studioCurrentImg || !studioCtx) return;
    
    studioCtx.clearRect(0, 0, studioCanvas.width, studioCanvas.height);
    studioCtx.fillStyle = '#f8fafc';
    studioCtx.fillRect(0, 0, studioCanvas.width, studioCanvas.height);
    
    studioCtx.save();
    studioCtx.imageSmoothingEnabled = true;
    studioCtx.imageSmoothingQuality = 'high';
    
    studioCtx.translate(studioImgX, studioImgY);
    studioCtx.rotate((studioRotation * Math.PI) / 180);
    studioCtx.scale(studioScale, studioScale);
    
    studioCtx.drawImage(studioCurrentImg, -studioCurrentImg.width / 2, -studioCurrentImg.height / 2);
    studioCtx.restore();
}

function exportAdminCroppedAvatar() {
    if (!studioCurrentImg) return;
    const highRes = document.createElement('canvas');
    highRes.width = 500;
    highRes.height = 500;
    const hrCtx = highRes.getContext('2d');
    
    hrCtx.fillStyle = '#ffffff';
    hrCtx.fillRect(0, 0, 500, 500);
    
    const canvasW = studioCanvas ? studioCanvas.width : 240;
    const ratio = 500 / canvasW;
    hrCtx.save();
    hrCtx.imageSmoothingEnabled = true;
    hrCtx.imageSmoothingQuality = 'high';
    
    hrCtx.translate(studioImgX * ratio, studioImgY * ratio);
    hrCtx.rotate((studioRotation * Math.PI) / 180);
    hrCtx.scale(studioScale * ratio, studioScale * ratio);
    hrCtx.drawImage(studioCurrentImg, -studioCurrentImg.width / 2, -studioCurrentImg.height / 2);
    hrCtx.restore();
    
    studioCroppedDataUrl = highRes.toDataURL('image/jpeg', 0.90);
}

function onAdminZoomChange(val) {
    studioScale = parseFloat(val);
    const slider = document.getElementById('adminZoomSlider');
    const min = parseFloat(slider.min) || 0.01;
    const max = parseFloat(slider.max) || 5.0;
    const pct = Math.round(((studioScale - min) / (max - min)) * 100);
    const badge = document.getElementById('adminZoomValBadge');
    if (badge) badge.textContent = `${pct}%`;
    renderAdminCanvas();
}

function adjustAdminZoom(delta) {
    const slider = document.getElementById('adminZoomSlider');
    if (!slider) return;
    const range = parseFloat(slider.max) - parseFloat(slider.min);
    let newVal = parseFloat(slider.value) + (delta * (range / 10));
    newVal = Math.max(parseFloat(slider.min), Math.min(parseFloat(slider.max), newVal));
    slider.value = newVal;
    onAdminZoomChange(newVal);
}

function rotateAdminImage(deg) {
    studioRotation = (studioRotation + deg) % 360;
    renderAdminCanvas();
}

function resetAdminCrop() {
    if (!studioCurrentImg) return;
    const canvasW = studioCanvas ? studioCanvas.width : 240;
    const canvasH = studioCanvas ? studioCanvas.height : 240;
    studioImgX = canvasW / 2;
    studioImgY = canvasH / 2;
    const scaleW = canvasW / studioCurrentImg.width;
    const scaleH = canvasH / studioCurrentImg.height;
    studioScale = Math.max(scaleW, scaleH);
    studioRotation = 0;
    document.getElementById('adminZoomSlider').value = studioScale;
    document.getElementById('adminZoomValBadge').textContent = `${Math.round(studioScale * 100)}%`;
    renderAdminCanvas();
}

// Touch & Mouse Drag Handlers
const canvasWrapper = document.getElementById('adminCanvasWrapper');

function getAdminPos(e) {
    const rect = studioCanvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

if (canvasWrapper) {
    const startAdminDrag = (e) => {
        if (!studioCurrentImg) return;
        studioIsDragging = true;
        canvasWrapper.style.cursor = 'grabbing';
        const pos = getAdminPos(e);
        studioStartX = pos.x - studioImgX;
        studioStartY = pos.y - studioImgY;
    };

    const onAdminDrag = (e) => {
        if (!studioIsDragging || !studioCurrentImg) return;
        if (e.cancelable) e.preventDefault();
        const pos = getAdminPos(e);
        studioImgX = pos.x - studioStartX;
        studioImgY = pos.y - studioStartY;
        renderAdminCanvas();
    };

    const stopAdminDrag = () => {
        if (studioIsDragging) {
            studioIsDragging = false;
            canvasWrapper.style.cursor = 'grab';
            exportAdminCroppedAvatar();
        }
    };

    canvasWrapper.addEventListener('mousedown', startAdminDrag);
    window.addEventListener('mousemove', onAdminDrag);
    window.addEventListener('mouseup', stopAdminDrag);

    canvasWrapper.addEventListener('touchstart', startAdminDrag, { passive: false });
    window.addEventListener('touchmove', onAdminDrag, { passive: false });
    window.addEventListener('touchend', stopAdminDrag);
}

function applyCroppedPhotoToForm() {
    exportAdminCroppedAvatar();
    if (studioCroppedDataUrl) {
        document.getElementById('regAvatarCroppedInput').value = studioCroppedDataUrl;
        
        const previewBox = document.getElementById('avatarPreviewBox');
        if (previewBox) {
            previewBox.innerHTML = `<img src="${studioCroppedDataUrl}" class="w-100 h-100 object-fit-cover">`;
        }
        
        const statusBadge = document.getElementById('avatarSelectedStatus');
        if (statusBadge) {
            statusBadge.style.display = 'inline-block';
        }

        const modalEl = document.getElementById('adminAvatarStudioModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

function updateCharCount(el, counterId) {
    const counter = document.getElementById(counterId);
    if (counter) {
        const text = el.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        const chars = el.value.length;
        counter.textContent = `${words} Words • ${chars} Characters`;
    }
}

function toggleAdminGenre(genreName) {
    const input = document.getElementById('adminRegGenreInput');
    if (!input) return;
    let items = input.value.split(',').map(s => s.trim()).filter(Boolean);
    if (items.includes(genreName)) {
        items = items.filter(s => s !== genreName);
    } else {
        items.push(genreName);
    }
    input.value = items.join(', ');
}

// Role selector change handler
function syncRoleForms() {
    const roleSelector = document.getElementById('roleSelector');
    if (!roleSelector) return;
    const role = roleSelector.value;
    const authorCard = document.getElementById('authorDetailsCard');
    const businessCard = document.getElementById('businessDetailsCard');
    const shopWrap = document.getElementById('shopNameWrap');
    const zoneWrap = document.getElementById('zoneWrap');
    const pubWrap = document.getElementById('publisherNameWrap');

    if (role === 'author') {
        if (authorCard) authorCard.style.display = 'block';
        if (businessCard) businessCard.style.display = 'none';
    } else if (role === 'publisher') {
        if (authorCard) authorCard.style.display = 'none';
        if (businessCard) businessCard.style.display = 'block';
        if (pubWrap) pubWrap.style.display = 'block';
        if (shopWrap) shopWrap.style.display = 'none';
        if (zoneWrap) zoneWrap.style.display = 'none';
    } else if (role === 'seller') {
        if (authorCard) authorCard.style.display = 'none';
        if (businessCard) businessCard.style.display = 'block';
        if (shopWrap) shopWrap.style.display = 'block';
        if (zoneWrap) zoneWrap.style.display = 'block';
        if (pubWrap) pubWrap.style.display = 'none';
    } else {
        if (authorCard) authorCard.style.display = 'none';
        if (businessCard) businessCard.style.display = 'none';
    }
}

function syncThisAuthor() {
    const btn = document.getElementById('btnHeaderSyncAuthor');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> সিঙ্ক হচ্ছে...';
    }

    fetch('{{ route("admin.registrations.sync-author", $user) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
        if (data.success) {
            alert(data.message || 'লেখক প্রোফাইল সফলভাবে লেখক ডিরেক্টরিতে সিঙ্ক হয়েছে!');
            window.location.reload();
        } else {
            alert(data.message || 'সিঙ্ক করতে ত্রুটি হয়েছে।');
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
        alert('সার্ভার সংযোগ সমস্যা। আবার চেষ্টা করুন।');
    });
}

// Initial bio counter count & role sync
document.addEventListener('DOMContentLoaded', function() {
    const bio = document.getElementById('authorBioInput');
    if (bio) {
        updateCharCount(bio, 'bioCounter');
    }
    const roleSelector = document.getElementById('roleSelector');
    if (roleSelector) {
        roleSelector.addEventListener('change', syncRoleForms);
        syncRoleForms();
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
.cursor-pointer {
    cursor: pointer !important;
}
</style>
@endsection
