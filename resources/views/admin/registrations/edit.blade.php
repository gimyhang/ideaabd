@extends('layouts.admin')

@section('title', 'Edit Registration — ' . $user->name)
@section('heading', 'Edit Registration & Photo Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">Registrations</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.show', $user) }}" class="text-decoration-none">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
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
                <small class="text-muted">User ID: #{{ $user->id }} • Submitted: {{ $user->created_at->format('d M, Y') }}</small>
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
                                <span>Author / Applicant Profile Photo</span>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" 
                                   class="form-control form-control-sm rounded-3 mb-1" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   onchange="previewAvatar(this, 'avatarPreviewBox')">
                            <div class="text-muted small" style="font-size: 0.76rem;">
                                <i class="fas fa-circle-info me-1 text-primary"></i> JPG, PNG or WebP format. Uploaded image will be centered in a 1:1 circle preview.
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
                        <span>Basic Account & Identity Credentials</span>
                    </h6>

                    <div class="row g-3">
                        {{-- Display Name --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Author / Display Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" placeholder="e.g. Humayun Ahmed" required>
                            <div class="text-muted" style="font-size: 0.72rem;">This name will appear on published books, articles and author directory.</div>
                        </div>

                        {{-- Email Address --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                        </div>

                        {{-- Phone Number --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Phone Number (Login Username) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone" class="form-control rounded-3 font-monospace" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        {{-- Role --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">
                                Account Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select rounded-3" id="roleSelector">
                                <option value="author" @selected(old('role', $user->role) === 'author')>Author</option>
                                <option value="seller" @selected(old('role', $user->role) === 'seller')>Seller</option>
                                <option value="publisher" @selected(old('role', $user->role) === 'publisher')>Publisher</option>
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">
                                Verification Status <span class="text-danger">*</span>
                            </label>
                            <select name="reg_status" class="form-select rounded-3">
                                <option value="pending" @selected(old('reg_status', $user->reg_status) === 'pending')>⏳ Pending</option>
                                <option value="approved" @selected(old('reg_status', $user->reg_status) === 'approved')>✅ Approved</option>
                                <option value="rejected" @selected(old('reg_status', $user->reg_status) === 'rejected')>❌ Rejected</option>
                            </select>
                        </div>

                        {{-- Active Switch --}}
                        <div class="col-12">
                            <div class="form-check form-switch p-2 bg-light rounded-3 border ps-5">
                                <input class="form-check-input ms-n4" type="checkbox" name="is_active" id="isActiveSwitch" value="1" @checked(old('is_active', $user->is_active))>
                                <label class="form-check-label small fw-semibold text-dark" for="isActiveSwitch">
                                    Keep Account Active (Active & approved users can log in and manage content)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 3. AUTHOR DEDICATED FIELDS (Author Details)               --}}
                {{-- ========================================================= --}}
                <div id="authorDetailsCard" class="mb-4" style="{{ old('role', $user->role) === 'author' ? '' : 'display:none;' }}">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-feather-pointed text-success"></i>
                        <span>Author & Literary Specialty Information</span>
                    </h6>

                    <div class="row g-3">
                        {{-- Full Legal Name --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Full Legal Name
                            </label>
                            <input type="text" name="full_name" class="form-control rounded-3" 
                                   value="{{ old('full_name', $regData['full_name'] ?? '') }}" 
                                   placeholder="Full legal name as on National ID">
                        </div>

                        {{-- Pen Name --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Pen Name / Pseudonym
                            </label>
                            <input type="text" name="pen_name" class="form-control rounded-3" 
                                   value="{{ old('pen_name', $regData['pen_name'] ?? '') }}" 
                                   placeholder="If author uses a pen name">
                        </div>

                        {{-- Genre --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
                                <span>Genre / Writing Topics</span>
                                <span class="text-muted small" style="font-size: 11px;">(কথাসাহিত্য, কবিতা, ছড়া ইত্যাদি)</span>
                            </label>
                            <input type="text" name="genre" id="adminRegGenreInput" class="form-control rounded-3 mb-1.5" 
                                   value="{{ old('genre', is_array($regData['genre'] ?? null) ? implode(', ', $regData['genre']) : ($regData['genre'] ?? '')) }}" 
                                   placeholder="e.g. কথাসাহিত্য, কবিতা, প্রবন্ধ, গবেষণা...">
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach(['কথাসাহিত্য', 'কবিতা', 'ছড়া', 'প্রবন্ধ', 'গবেষণা', 'ভ্রমণগদ্য', 'অনুবাদ', 'সায়েন্সফিকশন', 'অন্যান্য'] as $g)
                                    <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-0.5 shadow-2xs text-secondary small" 
                                            style="font-size: 11px;" onclick="toggleAdminGenre('{{ $g }}')">
                                        <i class="fa-solid fa-check me-0.5 text-success"></i> {{ $g }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- NID --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                National ID (NID)
                            </label>
                            <input type="text" name="nid" class="form-control rounded-3 font-monospace" 
                                   value="{{ old('nid', $regData['nid'] ?? '') }}" 
                                   placeholder="NID number">
                        </div>

                        {{-- Bio --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    Author Bio & Literary Background
                                </label>
                                <span class="text-muted small" id="bioCounter" style="font-size: 0.72rem;">0 characters</span>
                            </div>
                            <textarea name="bio" id="authorBioInput" rows="4" class="form-control rounded-3" 
                                      placeholder="Author biography, literary achievements, published works..." 
                                      oninput="updateCharCount(this, 'bioCounter')">{{ old('bio', is_array($regData['bio'] ?? null) ? implode("\n", $regData['bio']) : ($regData['bio'] ?? '')) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 4. SOCIAL MEDIA & WEB LINKS                               --}}
                {{-- ========================================================= --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-share-nodes text-info"></i>
                        <span>Social Media & Web Links</span>
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fab fa-facebook text-primary me-1"></i> Facebook Profile / Page URL
                            </label>
                            <input type="text" name="facebook" class="form-control rounded-3" 
                                   value="{{ old('facebook', $regData['facebook'] ?? '') }}" 
                                   placeholder="https://facebook.com/your-page">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fas fa-globe text-success me-1"></i> Personal Website / Portfolio
                            </label>
                            <input type="text" name="website" class="form-control rounded-3" 
                                   value="{{ old('website', $regData['website'] ?? '') }}" 
                                   placeholder="https://yourwebsite.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fab fa-x-twitter text-dark me-1"></i> Twitter / X Profile URL
                            </label>
                            <input type="text" name="twitter" class="form-control rounded-3" 
                                   value="{{ old('twitter', $regData['twitter'] ?? '') }}" 
                                   placeholder="https://x.com/username">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fab fa-youtube text-danger me-1"></i> YouTube Channel URL
                            </label>
                            <input type="text" name="youtube" class="form-control rounded-3" 
                                   value="{{ old('youtube', $regData['youtube'] ?? '') }}" 
                                   placeholder="https://youtube.com/@channel">
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 5. SELLER & PUBLISHER SPECIFIC DETAILS (Hidden for Author)--}}
                {{-- ========================================================= --}}
                <div id="businessDetailsCard" class="mb-4" style="{{ in_array(old('role', $user->role), ['seller', 'publisher']) ? '' : 'display:none;' }}">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fas fa-shop text-warning"></i>
                        <span>Store, Business & Trade Details (Sellers & Publishers)</span>
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6" id="shopNameWrap">
                            <label class="form-label small fw-bold text-dark">
                                Shop / Bookstore Name (Sellers)
                            </label>
                            <input type="text" name="shop_name" class="form-control rounded-3" 
                                   value="{{ old('shop_name', $regData['shop_name'] ?? '') }}" 
                                   placeholder="Bookstore Name">
                        </div>

                        <div class="col-md-6" id="zoneWrap">
                            <label class="form-label small fw-bold text-dark">
                                Zone / Area (Sellers)
                            </label>
                            <input type="text" name="zone" class="form-control rounded-3" 
                                   value="{{ old('zone', $regData['zone'] ?? '') }}" 
                                   placeholder="e.g. Dhaka Zone, Chittagong Zone...">
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
        counter.textContent = el.value.length + ' characters';
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
</style>
@endsection
