@extends('layouts.app')
@section('title', 'লেখক রেজিস্ট্রেশন - ideaabd')
@section('content')
@php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(); @endphp
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#E5FFE5,#D4FFD4)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width:52px;height:52px;background:#198754">
                            <i class="fas fa-pen-fancy text-white fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0" style="color:#198754">লেখক রেজিস্ট্রেশন</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'author') }}" enctype="multipart/form-data" id="authorRegForm">
                        @csrf
                        
                        {{-- ══ AUTHOR PROFILE PHOTO CIRCULAR PREVIEW & ZOOM ADJUSTER ══ --}}
                        <div class="mb-4 p-3.5 bg-light rounded-4 border">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-label fw-bold text-dark mb-0">
                                    <i class="fas fa-camera text-success me-1"></i> লেখকের ছবি / প্রোফাইল ফটো
                                </label>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> অটো .avif অপ্টিমাইজড
                                </span>
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-4 text-center">
                                    {{-- Fixed 150x150 Circular Avatar Frame (Clickable for Instant Upload) --}}
                                    <div class="position-relative mx-auto border border-3 border-success shadow-sm bg-light cursor-pointer avatar-upload-circle" 
                                         style="width: 150px; height: 150px; min-width: 150px; min-height: 150px; max-width: 150px; max-height: 150px; border-radius: 50% !important; overflow: hidden !important; -webkit-mask-image: -webkit-radial-gradient(white, black); box-sizing: border-box; cursor: pointer;" 
                                         id="avatarPreviewContainer"
                                         onclick="document.getElementById('authorAvatarInput').click()"
                                         title="ছবি আপলোড করতে ক্লিক করুন">
                                        
                                        {{-- Only this image scales inside the circular mask --}}
                                        <img id="authorAvatarLivePreview" src="" alt="Author Avatar Preview" 
                                             class="d-none" 
                                             style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; transform-origin: center center; transition: transform 0.05s ease-out;">

                                        {{-- Hover Overlay on Image --}}
                                        <div id="avatarHoverOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white bg-dark bg-opacity-50 opacity-0 transition-opacity" style="transition: opacity 0.2s ease; border-radius: 50%; pointer-events: none;">
                                            <i class="fas fa-camera fs-4 mb-1"></i>
                                            <span style="font-size: 11px;" class="fw-semibold">পরিবর্তন করুন</span>
                                        </div>

                                        {{-- Initial Placeholder --}}
                                        <div id="authorAvatarPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted p-2 bg-light" style="border-radius: 50%; pointer-events: none;">
                                            <i class="fas fa-cloud-arrow-up text-success fs-2 mb-1"></i>
                                            <span style="font-size: 12px;" class="fw-semibold text-dark">ছবি আপলোড</span>
                                            <span style="font-size: 10.5px;" class="text-muted">ক্লিক করে নির্বাচন করুন</span>
                                        </div>
                                    </div>

                                    <div id="photoStatusBadge" class="mt-2 text-success small fw-semibold" style="display: none; font-size: 11.5px;">
                                        <i class="fas fa-circle-check text-success me-1"></i> ছবি যুক্ত হয়েছে
                                    </div>

                                    {{-- Hidden Input to hold Base64 data if needed --}}
                                    <input type="hidden" name="avatar_cropped" id="authorAvatarCropped">
                                </div>

                                <div class="col-12 col-md-8">
                                    <div class="mb-2">
                                        <label for="authorAvatarInput" class="form-label small fw-semibold text-secondary mb-1">ডিভাইস থেকে ছবি আপলোড করুন:</label>
                                        <input type="file" name="avatar" id="authorAvatarInput" 
                                               accept="image/jpeg,image/png,image/jpg,image/webp,image/avif" 
                                               class="form-control rounded-3 @error('avatar') is-invalid @enderror"
                                               onchange="handleAuthorPhotoUpload(this)">
                                        @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    {{-- Zoom / Size Adjustment Slider Bar --}}
                                    <div id="photoZoomControls" class="p-2.5 bg-white rounded-3 border mt-2 shadow-2xs" style="display: none;">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 11.5px;">
                                            <span class="text-secondary fw-semibold">
                                                <i class="fas fa-magnifying-glass-plus text-success me-1"></i> ছবি ছোট / বড় অ্যাডজাস্ট:
                                            </span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" id="photoZoomBadge">100%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:26px;height:26px;" onclick="adjustAvatarZoom(-0.1)" title="ছোট করুন">
                                                <i class="fa-solid fa-minus text-secondary" style="font-size:10px;"></i>
                                            </button>
                                            <input type="range" class="form-range flex-grow-1" id="avatarZoomSlider" min="1.0" max="3.0" step="0.05" value="1.0" oninput="onAvatarZoomSlider(this.value)">
                                            <button type="button" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:26px;height:26px;" onclick="adjustAvatarZoom(0.1)" title="বড় করুন">
                                                <i class="fa-solid fa-plus text-secondary" style="font-size:10px;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 11px;" onclick="resetAvatarZoom()">
                                                রিসেট
                                            </button>
                                        </div>
                                    </div>

                                    <div id="photoActionButtons" class="d-flex align-items-center gap-2 mt-2" style="display: none !important;">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="removeAuthorPhoto()">
                                            <i class="fas fa-trash-can me-1"></i> ছবি মুছুন / পরিবর্তন
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ══ BASIC CREDENTIALS: BENGALI & ENGLISH NAMES ══ --}}
                        <div class="row g-2.5 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold">লেখক নাম (বাংলা) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required placeholder="বাংলায় পূর্ণ নাম">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold">লেখক নাম (ইংরেজি) <span class="text-muted small">(ঐচ্ছিক)</span></label>
                                <input type="text" name="name_en" class="form-control rounded-3 @error('name_en') is-invalid @enderror"
                                       value="{{ old('name_en') }}" placeholder="Full Name in English">
                                @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">মোবাইল নম্বর (ইউজারনেম) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-success"></i></span>
                                <input type="tel" name="phone" class="form-control rounded-end-3 @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-text small text-muted"><i class="fa-solid fa-shield-check text-success me-1"></i> এই মোবাইল নম্বরটি দিয়ে আপনি ওয়েবসাইটে লগইন করবেন।</div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখকের নিজস্ব সক্রিয় ইমেইল <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-primary"></i></span>
                                <input type="email" name="email" class="form-control rounded-end-3 @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required placeholder="yourname@gmail.com">
                            </div>
                            <div class="form-text small text-muted"><i class="fa-solid fa-circle-info text-primary me-1"></i> অ্যাকাউন্ট অনুমোদন, পাসওয়ার্ড রিসেট ও লেখা অনুমোদনের নোটিফিকেশন এই ইমেইলে যাবে।</div>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span> <small class="text-muted fw-normal">(কমপক্ষে ৮ অক্ষর)</small></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="authorRegPassword" class="form-control rounded-start-3 @error('password') is-invalid @enderror" required minlength="8" maxlength="50" placeholder="কমপক্ষে ৮ অক্ষর" oninput="checkPasswordStrength(this.value, 'authorPwdStrengthBar', 'authorPwdStrengthText')">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('authorRegPassword', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="authorRegPasswordConfirm" class="form-control rounded-start-3" required minlength="8" maxlength="50" placeholder="পুনরায় ৮ অক্ষরের পাসওয়ার্ড লিখুন">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('authorRegPasswordConfirm', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 11.5px;">
                                    <span class="text-muted">পাসওয়ার্ডের শক্তি: <strong id="authorPwdStrengthText" class="text-secondary">টাইপ করুন...</strong></span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div id="authorPwdStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-feather-pointed text-success me-1"></i> অতিরিক্ত তথ্য ও সাহিত্যকর্ম</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখকের ছদ্মনাম</label>
                            <input type="text" name="pen_name" class="form-control rounded-3" value="{{ old('pen_name') }}" placeholder="ঐচ্ছিক">
                        </div>

                        {{-- ══ WRITING TOPICS ══ --}}
                        <div class="mb-4 p-3.5 bg-light rounded-4 border">
                            <label class="form-label fw-bold text-dark d-block mb-3 fs-6">
                                <i class="fa-solid fa-tags text-success me-1.5"></i> Writing Topics
                            </label>

                            @php
                                $presetGenres = [
                                    'কথাসাহিত্য'   => 'fa-book-open',
                                    'কবিতা'        => 'fa-feather',
                                    'ছড়া'          => 'fa-music',
                                    'প্রবন্ধ'       => 'fa-file-lines',
                                    'গবেষণা'      => 'fa-microscope',
                                    'ভ্রমণগদ্য'     => 'fa-compass',
                                    'অনুবাদ'       => 'fa-language',
                                    'সায়েন্সফিকশন'  => 'fa-rocket',
                                    'অন্যান্য'      => 'fa-ellipsis',
                                ];
                                $oldGenres = old('genres', []);
                                if (is_string(old('genre')) && !empty(old('genre'))) {
                                    $oldGenres = array_merge($oldGenres, explode(',', old('genre')));
                                }
                                $oldGenres = array_map('trim', $oldGenres);
                            @endphp

                            <div class="row g-2.5" id="genreCheckboxGrid">
                                @foreach($presetGenres as $genreName => $icon)
                                    @php $isChecked = in_array($genreName, $oldGenres); @endphp
                                    <div class="col-6 col-md-4">
                                        <label class="genre-card-pill d-flex align-items-center justify-content-between p-2.5 rounded-3 border bg-white cursor-pointer w-100 position-relative shadow-2xs {{ $isChecked ? 'active-genre-card' : '' }}" 
                                               style="cursor: pointer; user-select: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);" for="genre_{{ $loop->index }}">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <div class="genre-icon-box rounded-circle d-flex align-items-center justify-content-center {{ $isChecked ? 'bg-success text-white' : 'bg-light text-secondary' }}" 
                                                     style="width: 30px; height: 30px; min-width: 30px; font-size: 12px; transition: all 0.2s ease;">
                                                    <i class="fa-solid {{ $icon }}"></i>
                                                </div>
                                                <span class="genre-text fw-semibold text-truncate {{ $isChecked ? 'text-success' : 'text-dark' }}" style="font-size: 13.5px;">{{ $genreName }}</span>
                                            </div>
                                            <div class="genre-check-indicator ms-1 flex-shrink-0">
                                                <input type="checkbox" name="genres[]" value="{{ $genreName }}" id="genre_{{ $loop->index }}" 
                                                       class="genre-checkbox d-none" {{ $isChecked ? 'checked' : '' }} onchange="toggleGenreChip(this)">
                                                <span class="badge-tick rounded-2 d-flex align-items-center justify-content-center {{ $isChecked ? 'bg-success text-white border-success' : 'border border-secondary-subtle text-transparent bg-white' }}" 
                                                      style="width: 20px; height: 20px; font-size: 10.5px; transition: all 0.2s ease;">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Other Genre Write-in Box --}}
                            <div class="mt-3" id="otherGenreInputWrap" style="{{ in_array('অন্যান্য', $oldGenres) ? '' : 'display:none;' }}">
                                <input type="text" name="genre_other" id="genre_other" class="form-control rounded-3" 
                                       placeholder="অন্যান্য বিষয় লিখুন..." value="{{ old('genre_other') }}">
                            </div>

                            {{-- Legacy single genre string hidden input fallback --}}
                            <input type="hidden" name="genre" id="genreCombinedInput" value="{{ old('genre') }}">
                        </div>

                        {{-- ══ DYNAMIC BIO TEXTAREA ══ --}}
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <label class="form-label fw-semibold text-dark mb-0">
                                    <i class="fas fa-pen-nib text-success me-1"></i> লেখক পরিচিতি / বায়ো
                                </label>
                                <span class="badge bg-light text-secondary border font-monospace" id="bioCounterBadge" style="font-size: 11.5px;">০ / ৫০০ শব্দ</span>
                            </div>
                            
                            <textarea name="bio" id="authorBioInput" rows="9" 
                                      class="form-control rounded-3 p-3 @error('bio') is-invalid @enderror bio-textarea-dynamic"
                                      style="min-height: 220px; height: 240px; font-size: 14.5px; line-height: 1.8; overflow-y: auto; resize: vertical;"
                                      placeholder="বায়ো লিখুন:"
                                      oninput="updateBioStats(this)">{{ old('bio') }}</textarea>
                            @error('bio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">জাতীয় পরিচয়পত্র নম্বর</label>
                            <input type="text" name="nid" class="form-control rounded-3" value="{{ old('nid') }}" placeholder="ঐচ্ছিক">
                        </div>

                        {{-- Submit Notice Banner --}}
                        <div class="alert alert-success bg-success-subtle border-success-subtle text-success-emphasis small py-2.5 px-3 rounded-3 d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-circle-check fs-5 text-success"></i>
                            <span class="fw-semibold">
                                আপনার রেজিস্ট্রেশন সফল হয়েছে। ২৪ ঘণ্টার মধ্যে একটিভ না হলে সাপোর্ট টিমকে অবগত করুন।
                            </span>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold text-white rounded-pill shadow-sm" style="background:#198754; font-size: 15.5px;" id="authorSubmitBtn">
                            <i class="fas fa-paper-plane me-1.5"></i> সাবমিট করুন
                        </button>
                        
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none">
                                <i class="fa-solid fa-arrow-left me-1"></i> অন্য ধরনের অ্যাকাউন্ট (পাঠক / ক্রেতা / প্রকাশক)
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Avatar Upload Circle Clickable Styling */
.avatar-upload-circle {
    transition: all 0.2s ease-in-out;
}
.avatar-upload-circle:hover {
    border-color: #146c43 !important;
    transform: scale(1.02);
    box-shadow: 0 6px 16px rgba(25, 135, 84, 0.25) !important;
}
.avatar-upload-circle:hover #avatarHoverOverlay {
    opacity: 1 !important;
}

/* Genre Cards Modern Styling */
.genre-card-pill {
    border-color: #e2e8f0 !important;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.genre-card-pill:hover {
    border-color: #198754 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.12) !important;
}
.genre-card-pill.active-genre-card {
    border-color: #198754 !important;
    background: #f0fdf4 !important;
    box-shadow: 0 2px 8px rgba(25, 135, 84, 0.15) !important;
}
.genre-card-pill.active-genre-card .genre-text {
    color: #198754 !important;
    font-weight: 700 !important;
}
.genre-card-pill.active-genre-card .genre-icon-box {
    background: #198754 !important;
    color: #ffffff !important;
}

/* Dynamic Bio Textarea Custom Scrollbar */
.bio-textarea-dynamic {
    scrollbar-width: thin;
    scrollbar-color: #198754 #f1f5f9;
}
.bio-textarea-dynamic::-webkit-scrollbar {
    width: 8px;
}
.bio-textarea-dynamic::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.bio-textarea-dynamic::-webkit-scrollbar-thumb {
    background: #198754;
    border-radius: 4px;
}
.bio-textarea-dynamic::-webkit-scrollbar-thumb:hover {
    background: #157347;
}
</style>

<script>
let currentAvatarZoom = 1.0;
let originalAvatarImg = null;

function handleAuthorPhotoUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (!file.type.startsWith('image/')) {
            alert('অনুগ্রহ করে একটি ছবি ফাইল (JPG, PNG, WebP, AVIF) নির্বাচন করুন।');
            return;
        }

        const previewImg = document.getElementById('authorAvatarLivePreview');
        const placeholder = document.getElementById('authorAvatarPlaceholder');
        const statusBadge = document.getElementById('photoStatusBadge');
        const actionButtons = document.getElementById('photoActionButtons');
        const zoomControls = document.getElementById('photoZoomControls');
        const slider = document.getElementById('avatarZoomSlider');
        const badge = document.getElementById('photoZoomBadge');

        const reader = new FileReader();
        reader.onload = function(e) {
            const dataUri = e.target.result;
            
            // Show image inside fixed circular frame
            if (previewImg) {
                previewImg.src = dataUri;
                previewImg.style.setProperty('display', 'block', 'important');
                previewImg.style.transform = 'scale(1)';
                previewImg.style.transformOrigin = 'center center';
                previewImg.classList.remove('d-none');
            }
            if (placeholder) {
                placeholder.style.setProperty('display', 'none', 'important');
            }
            if (statusBadge) {
                statusBadge.style.display = 'block';
            }
            if (actionButtons) {
                actionButtons.style.setProperty('display', 'flex', 'important');
            }
            if (zoomControls) {
                zoomControls.style.display = 'block';
            }
            
            // Reset zoom to 100%
            currentAvatarZoom = 1.0;
            if (slider) slider.value = 1;
            if (badge) badge.textContent = '100%';

            originalAvatarImg = new Image();
            originalAvatarImg.onload = function() {
                renderCroppedBase64();
            };
            originalAvatarImg.src = dataUri;
        };
        reader.readAsDataURL(file);
    }
}

function onAvatarZoomSlider(val) {
    currentAvatarZoom = parseFloat(val);
    const badge = document.getElementById('photoZoomBadge');
    const previewImg = document.getElementById('authorAvatarLivePreview');
    
    if (badge) {
        badge.textContent = Math.round(currentAvatarZoom * 100) + '%';
    }
    if (previewImg) {
        previewImg.style.transform = `scale(${currentAvatarZoom})`;
        previewImg.style.transformOrigin = 'center center';
    }
    renderCroppedBase64();
}

function adjustAvatarZoom(delta) {
    let newVal = Math.min(3.0, Math.max(1.0, currentAvatarZoom + delta));
    const slider = document.getElementById('avatarZoomSlider');
    if (slider) {
        slider.value = newVal.toFixed(2);
    }
    onAvatarZoomSlider(newVal);
}

function resetAvatarZoom() {
    const slider = document.getElementById('avatarZoomSlider');
    if (slider) {
        slider.value = 1;
    }
    onAvatarZoomSlider(1);
}

function renderCroppedBase64() {
    if (!originalAvatarImg || !originalAvatarImg.width) return;
    
    const canvas = document.createElement('canvas');
    const size = 600;
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');
    
    const imgW = originalAvatarImg.naturalWidth || originalAvatarImg.width;
    const imgH = originalAvatarImg.naturalHeight || originalAvatarImg.height;
    
    // Scale and crop centered
    const minDim = Math.min(imgW, imgH);
    const cropSize = minDim / currentAvatarZoom;
    const sx = (imgW - cropSize) / 2;
    const sy = (imgH - cropSize) / 2;
    
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);
    
    ctx.drawImage(originalAvatarImg, Math.max(0, sx), Math.max(0, sy), cropSize, cropSize, 0, 0, size, size);
    
    const croppedDataUri = canvas.toDataURL('image/jpeg', 0.92);
    const croppedInput = document.getElementById('authorAvatarCropped');
    if (croppedInput) {
        croppedInput.value = croppedDataUri;
    }
}

function removeAuthorPhoto() {
    const input = document.getElementById('authorAvatarInput');
    const previewImg = document.getElementById('authorAvatarLivePreview');
    const placeholder = document.getElementById('authorAvatarPlaceholder');
    const statusBadge = document.getElementById('photoStatusBadge');
    const actionButtons = document.getElementById('photoActionButtons');
    const zoomControls = document.getElementById('photoZoomControls');
    const croppedInput = document.getElementById('authorAvatarCropped');
    const slider = document.getElementById('avatarZoomSlider');
    const badge = document.getElementById('photoZoomBadge');

    if (input) input.value = '';
    if (croppedInput) croppedInput.value = '';
    originalAvatarImg = null;
    currentAvatarZoom = 1.0;

    if (previewImg) {
        previewImg.src = '';
        previewImg.style.transform = 'scale(1)';
        previewImg.style.setProperty('display', 'none', 'important');
        previewImg.classList.add('d-none');
    }
    if (placeholder) {
        placeholder.style.setProperty('display', 'flex', 'important');
    }
    if (statusBadge) {
        statusBadge.style.display = 'none';
    }
    if (actionButtons) {
        actionButtons.style.setProperty('display', 'none', 'important');
    }
    if (zoomControls) {
        zoomControls.style.display = 'none';
    }
    if (slider) {
        slider.value = 1;
    }
    if (badge) {
        badge.textContent = '100%';
    }
}

/* =========================================================================
   2. GENRE / WRITING TOPICS CHIP SELECTOR & TICK MARKS
   ========================================================================= */
function toggleGenreChip(checkbox) {
    const label = checkbox.closest('.genre-card-pill');
    const iconBox = label.querySelector('.genre-icon-box');
    const tick = label.querySelector('.badge-tick');
    const text = label.querySelector('.genre-text');
    
    if (checkbox.checked) {
        label.classList.add('active-genre-card');
        if (iconBox) {
            iconBox.classList.remove('bg-light', 'text-secondary');
            iconBox.classList.add('bg-success', 'text-white');
        }
        if (text) {
            text.classList.remove('text-dark');
            text.classList.add('text-success');
        }
        if (tick) {
            tick.classList.remove('border', 'text-transparent', 'bg-light');
            tick.classList.add('bg-success', 'text-white', 'border-success');
        }
    } else {
        label.classList.remove('active-genre-card');
        if (iconBox) {
            iconBox.classList.remove('bg-success', 'text-white');
            iconBox.classList.add('bg-light', 'text-secondary');
        }
        if (text) {
            text.classList.remove('text-success');
            text.classList.add('text-dark');
        }
        if (tick) {
            tick.classList.remove('bg-success', 'text-white', 'border-success');
            tick.classList.add('border', 'text-transparent', 'bg-light');
        }
    }

    // Toggle other genre input if "অন্যান্য" is checked
    const otherCheckbox = Array.from(document.querySelectorAll('.genre-checkbox')).find(c => c.value === 'অন্যান্য');
    const otherWrap = document.getElementById('otherGenreInputWrap');
    if (otherWrap && otherCheckbox) {
        otherWrap.style.display = otherCheckbox.checked ? 'block' : 'none';
    }

    // Sync combined genres string
    syncCombinedGenres();
}

function syncCombinedGenres() {
    const checked = Array.from(document.querySelectorAll('.genre-checkbox:checked')).map(c => c.value);
    const otherInput = document.getElementById('genre_other');
    if (checked.includes('অন্যান্য') && otherInput && otherInput.value.trim()) {
        checked.push(otherInput.value.trim());
    }
    const combined = document.getElementById('genreCombinedInput');
    if (combined) {
        combined.value = checked.join(', ');
    }
}

document.getElementById('genre_other')?.addEventListener('input', syncCombinedGenres);

/* =========================================================================
   3. DYNAMIC BIO WORD / CHAR STATS & EXPANSION
   ========================================================================= */
function toBengaliNumber(num) {
    const bnNums = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return String(num).replace(/[0-9]/g, d => bnNums[d]);
}

function updateBioStats(textarea) {
    const text = textarea.value.trim();
    const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
    const chars = textarea.value.length;

    const badge = document.getElementById('bioCounterBadge');
    const charSpan = document.getElementById('bioCharCount');

    if (badge) {
        badge.textContent = `${toBengaliNumber(words)} / ${toBengaliNumber(500)} শব্দ`;
        if (words > 500) {
            badge.className = 'badge bg-danger text-white border border-danger font-monospace';
        } else if (words >= 400) {
            badge.className = 'badge bg-warning text-dark border border-warning font-monospace';
        } else {
            badge.className = 'badge bg-light text-secondary border font-monospace';
        }
    }

    if (charSpan) {
        charSpan.textContent = `${toBengaliNumber(chars)} অক্ষর`;
    }
}

// Initial bio stats sync on page load
document.addEventListener('DOMContentLoaded', function() {
    const bioTextarea = document.getElementById('authorBioInput');
    if (bioTextarea && bioTextarea.value) {
        updateBioStats(bioTextarea);
    }
});

/* =========================================================================
   4. PASSWORD UTILITIES & MINIMUM 8 CHARACTERS CHECK
   ========================================================================= */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        if (icon) icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkPasswordStrength(password, barId, textId) {
    const bar = document.getElementById(barId);
    const text = document.getElementById(textId);
    if (!bar || !text) return;

    if (!password) {
        bar.style.width = '0%';
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'টাইপ করুন...';
        text.className = 'text-secondary';
        return;
    }

    let score = 0;
    if (password.length >= 8) score += 30;
    if (password.length >= 10) score += 15;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 15;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 15;

    if (password.length < 8) {
        bar.style.width = '25%';
        bar.className = 'progress-bar bg-danger';
        text.textContent = `অপূর্ণাঙ্গ (${toBengaliNumber(password.length)}/৮ অক্ষর — কমপক্ষে ৮ অক্ষর আবশ্যক)`;
        text.className = 'text-danger fw-bold';
    } else if (score < 60) {
        bar.style.width = '55%';
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'মাঝারি (Medium)';
        text.className = 'text-warning fw-bold';
    } else {
        bar.style.width = '100%';
        bar.className = 'progress-bar bg-success';
        text.textContent = 'খুব শক্তিশালী (Strong)';
        text.className = 'text-success fw-bold';
    }
}

// Form submit handler
document.getElementById('authorRegForm')?.addEventListener('submit', function(e) {
    const pwdInput = document.getElementById('authorRegPassword');
    if (pwdInput && pwdInput.value.length < 8) {
        e.preventDefault();
        alert('পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।');
        pwdInput.focus();
        return false;
    }

    syncCombinedGenres();
    
    const submitBtn = document.getElementById('authorSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>আপনার তথ্য ও ছবি প্রসেসিং হচ্ছে...';
    }
});
</script>
@endsection
