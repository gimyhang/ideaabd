@extends('layouts.app')
@section('title', 'লেখক রেজিস্ট্রেশন - ideaabd')
@section('content')
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
                            <h4 class="fw-bold mb-1" style="color:#198754">লেখক রেজিস্ট্রেশন</h4>
                            <small class="text-muted">আপনার মোবাইল নম্বরটি ইউজারনেম হিসেবে ব্যবহার হবে</small>
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
                        
                        {{-- ══ DYNAMIC ADJUSTABLE AUTHOR PHOTO STUDIO ══ --}}
                        <div class="mb-4 p-3.5 bg-light rounded-4 border">
                            <label class="form-label fw-bold text-dark d-block mb-1">
                                <i class="fas fa-camera text-success me-1"></i> লেখকের ছবি / প্রোফাইল ফটো <span class="text-muted small fw-normal">(এডজাস্টেবল ক্রপ ও ফিক্সড সাইজ)</span>
                            </label>
                            <p class="text-muted small mb-3" style="font-size: 12px;">
                                যেকোনো সাইজের ছবি নির্বাচন করুন। ছবির ওপর ড্র্যাগ করে ও জুম স্লাইডার দিয়ে মুখমণ্ডল সঠিকভাবে বসিয়ে নিন।
                            </p>

                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-5 text-center">
                                    {{-- Interactive Crop Canvas Container --}}
                                    <div class="position-relative mx-auto rounded-4 overflow-hidden border border-2 border-success-subtle shadow-xs bg-white" 
                                         style="width: 200px; height: 200px; cursor: grab; touch-action: none;" id="canvasWrapper">
                                        <canvas id="cropCanvas" width="200" height="200" style="display:block; width:100%; height:100%;"></canvas>
                                        
                                        {{-- Circular Overlay Mask Guide --}}
                                        <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none d-flex align-items-center justify-content-center" 
                                             style="box-shadow: 0 0 0 9999px rgba(0,0,0,0.45); border-radius: 50%; pointer-events: none;">
                                            <div class="border border-white border-opacity-75 rounded-circle w-100 h-100" style="border-style: dashed !important;"></div>
                                        </div>

                                        {{-- Initial placeholder when no image uploaded --}}
                                        <div id="canvasPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted p-2 pointer-events-none">
                                            <i class="fas fa-cloud-arrow-up text-success fs-2 mb-1"></i>
                                            <span style="font-size: 11px;" class="fw-semibold">ছবি আপলোড করুন</span>
                                        </div>
                                    </div>

                                    {{-- Hidden Input to hold the final Base64 Cropped Image --}}
                                    <input type="hidden" name="avatar_cropped" id="authorAvatarCropped">
                                </div>

                                <div class="col-12 col-md-7">
                                    {{-- File Picker --}}
                                    <div class="mb-2.5">
                                        <input type="file" name="avatar" id="authorAvatarInput" 
                                               accept="image/jpeg,image/png,image/jpg,image/webp" 
                                               class="form-control form-control-sm rounded-3 @error('avatar') is-invalid @enderror"
                                               onchange="loadAuthorImage(this)">
                                        @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    {{-- Interactive Controls: Zoom Slider, Rotate, Reset --}}
                                    <div id="cropControls" class="p-2.5 bg-white rounded-3 border" style="display: none;">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 11.5px;">
                                            <span class="text-muted fw-semibold"><i class="fas fa-magnifying-glass-plus text-success me-1"></i>জুম এডজাস্ট:</span>
                                            <span class="badge bg-light text-dark border font-monospace" id="zoomValBadge">100%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <button type="button" class="btn btn-sm btn-light border rounded-circle p-1" style="width:26px;height:26px;" onclick="adjustZoom(-0.1)" title="Zoom Out"><i class="fa-solid fa-minus" style="font-size:10px;"></i></button>
                                            <input type="range" class="form-range flex-grow-1" id="zoomSlider" min="0.2" max="3.5" step="0.05" value="1" oninput="onZoomChange(this.value)">
                                            <button type="button" class="btn btn-sm btn-light border rounded-circle p-1" style="width:26px;height:26px;" onclick="adjustZoom(0.1)" title="Zoom In"><i class="fa-solid fa-plus" style="font-size:10px;"></i></button>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <button type="button" class="btn btn-light btn-sm border rounded-pill px-2.5 py-1 text-dark small" onclick="rotateImage(90)">
                                                <i class="fas fa-rotate-right me-1 text-primary"></i> ঘোরান (Rotate)
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm border rounded-pill px-2.5 py-1 text-dark small" onclick="resetCrop()">
                                                <i class="fas fa-arrows-to-circle me-1 text-secondary"></i> রিসেট / কেন্দ্র
                                            </button>
                                        </div>
                                    </div>

                                    <div class="small text-muted mt-2" style="font-size: 11.5px;">
                                        <i class="fas fa-circle-check text-success me-1"></i> ছবি যত বড়ই হোক স্বয়ংক্রিয়ভাবে গোলাকার ফ্রেমে ফিক্সড হয়ে অ্যাডজাস্ট হবে।
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ══ BASIC CREDENTIALS ══ --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখকের পুরো নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="আপনার পুরো নাম">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                <label class="form-label fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="authorRegPassword" class="form-control rounded-start-3 @error('password') is-invalid @enderror" required minlength="6" maxlength="50" placeholder="ন্যূনতম ৬ অক্ষর" oninput="checkPasswordStrength(this.value, 'authorPwdStrengthBar', 'authorPwdStrengthText')">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('authorRegPassword', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="authorRegPasswordConfirm" class="form-control rounded-start-3" required minlength="6" maxlength="50" placeholder="পুনরায় লিখুন">
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
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-feather-pointed text-success me-1"></i> লেখকের অতিরিক্ত তথ্য ও সাহিত্য ঘরানা</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ছদ্মনাম / কলমনাম <span class="text-muted small">(যদি থাকে)</span></label>
                            <input type="text" name="pen_name" class="form-control rounded-3" value="{{ old('pen_name') }}" placeholder="ঐচ্ছিক — ব্যবহার না করলে আসল নাম ব্যবহার হবে">
                        </div>

                        {{-- ══ GENRE / WRITING TOPICS WITH TICK MARK CHECKBOXES ══ --}}
                        <div class="mb-3.5 p-3.5 bg-light rounded-4 border">
                            <label class="form-label fw-bold text-dark d-block mb-1.5">
                                <i class="fa-solid fa-tags text-success me-1"></i> Genre / Writing Topics (লেখার ধরন ও সাহিত্য ঘরানা)
                            </label>
                            <small class="text-muted d-block mb-2.5">
                                যে যে বিষয়ে আপনি লেখেন সেগুলোতে টিক চিহ্ন দিন (একাধিক নির্বাচন করা যাবে):
                            </small>

                            @php
                                $presetGenres = [
                                    'কথাসাহিত্য'   => ['icon' => 'fa-book-open', 'en' => 'Fiction / Stories'],
                                    'কবিতা'        => ['icon' => 'fa-feather', 'en' => 'Poetry'],
                                    'ছড়া'          => ['icon' => 'fa-music', 'en' => 'Rhymes'],
                                    'প্রবন্ধ'       => ['icon' => 'fa-file-lines', 'en' => 'Essays'],
                                    'গবেষণা'      => ['icon' => 'fa-microscope', 'en' => 'Research'],
                                    'ভ্রমণগদ্য'     => ['icon' => 'fa-compass', 'en' => 'Travelogue'],
                                    'অনুবাদ'       => ['icon' => 'fa-language', 'en' => 'Translation'],
                                    'সায়েন্সফিকশন'  => ['icon' => 'fa-rocket', 'en' => 'Sci-Fi'],
                                    'অন্যান্য'      => ['icon' => 'fa-ellipsis', 'en' => 'Others'],
                                ];
                                $oldGenres = old('genres', []);
                                if (is_string(old('genre')) && !empty(old('genre'))) {
                                    $oldGenres = array_merge($oldGenres, explode(',', old('genre')));
                                }
                                $oldGenres = array_map('trim', $oldGenres);
                            @endphp

                            <div class="row g-2" id="genreCheckboxGrid">
                                @foreach($presetGenres as $genreName => $genreMeta)
                                    @php $isChecked = in_array($genreName, $oldGenres); @endphp
                                    <div class="col-6 col-sm-4 col-md-4">
                                        <label class="genre-chip-label d-flex align-items-center justify-content-between p-2.5 rounded-3 border bg-white shadow-2xs cursor-pointer w-100 position-relative transition-all {{ $isChecked ? 'border-success bg-success-subtle text-success fw-bold' : 'text-dark' }}" 
                                               style="cursor: pointer; user-select: none; transition: all 0.15s ease;" for="genre_{{ $loop->index }}">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <i class="fa-solid {{ $genreMeta['icon'] }} text-muted genre-icon {{ $isChecked ? 'text-success' : '' }}" style="font-size: 13px;"></i>
                                                <span class="genre-text small text-truncate">{{ $genreName }}</span>
                                            </div>
                                            <div class="genre-check-indicator ms-1">
                                                <input type="checkbox" name="genres[]" value="{{ $genreName }}" id="genre_{{ $loop->index }}" 
                                                       class="genre-checkbox d-none" {{ $isChecked ? 'checked' : '' }} onchange="toggleGenreChip(this)">
                                                <span class="badge-tick rounded-circle d-flex align-items-center justify-content-center {{ $isChecked ? 'bg-success text-white' : 'border text-transparent' }}" 
                                                      style="width: 20px; height: 20px; font-size: 10px; transition: all 0.15s ease;">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Other Genre Write-in Box --}}
                            <div class="mt-2.5" id="otherGenreInputWrap" style="{{ in_array('অন্যান্য', $oldGenres) ? '' : 'display:none;' }}">
                                <input type="text" name="genre_other" id="genre_other" class="form-control form-control-sm rounded-3" 
                                       placeholder="অন্যান্য ঘরানা বা বিষয়ের নাম লিখুন..." value="{{ old('genre_other') }}">
                            </div>

                            {{-- Legacy single genre string hidden input fallback --}}
                            <input type="hidden" name="genre" id="genreCombinedInput" value="{{ old('genre') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">পরিচিতি / বায়ো</label>
                            <textarea name="bio" rows="3" class="form-control rounded-3 @error('bio') is-invalid @enderror"
                                      placeholder="আপনার সম্পর্কে সংক্ষেপে লিখুন (ঐচ্ছিক)...">{{ old('bio') }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
.genre-chip-label:hover {
    border-color: #198754 !important;
    transform: translateY(-1px);
}
</style>

<script>
/* =========================================================================
   1. DYNAMIC ADJUSTABLE PHOTO CROPPER CANVAS STUDIO
   ========================================================================= */
let canvas = document.getElementById('cropCanvas');
let ctx = canvas.getContext('2d');
let currentImg = null;
let imgX = 100;
let imgY = 100;
let scale = 1;
let rotation = 0;
let isDragging = false;
let startX, startY;

function loadAuthorImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            currentImg = new Image();
            currentImg.onload = function() {
                // Initialize positions
                document.getElementById('canvasPlaceholder').style.display = 'none';
                document.getElementById('cropControls').style.display = 'block';
                
                // Calculate fit scale
                const canvasW = canvas.width;
                const canvasH = canvas.height;
                const scaleW = canvasW / currentImg.width;
                const scaleH = canvasH / currentImg.height;
                scale = Math.max(scaleW, scaleH);
                
                document.getElementById('zoomSlider').value = scale;
                document.getElementById('zoomValBadge').textContent = `${Math.round(scale * 100)}%`;
                
                imgX = canvasW / 2;
                imgY = canvasH / 2;
                rotation = 0;
                
                renderCanvas();
            };
            currentImg.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function renderCanvas() {
    if (!currentImg) return;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.save();
    
    // Move to center of transformation
    ctx.translate(imgX, imgY);
    ctx.rotate((rotation * Math.PI) / 180);
    ctx.scale(scale, scale);
    
    // Draw centered
    ctx.drawImage(currentImg, -currentImg.width / 2, -currentImg.height / 2);
    ctx.restore();
    
    // Update hidden cropped Base64 input
    exportCroppedAvatar();
}

function exportCroppedAvatar() {
    if (!currentImg) return;
    // Export full canvas as high-quality JPEG
    const highResCanvas = document.createElement('canvas');
    highResCanvas.width = 400;
    highResCanvas.height = 400;
    const hrCtx = highResCanvas.getContext('2d');
    
    // Draw scaled representation
    const ratio = 400 / canvas.width;
    hrCtx.save();
    hrCtx.translate(imgX * ratio, imgY * ratio);
    hrCtx.rotate((rotation * Math.PI) / 180);
    hrCtx.scale(scale * ratio, scale * ratio);
    hrCtx.drawImage(currentImg, -currentImg.width / 2, -currentImg.height / 2);
    hrCtx.restore();
    
    const dataUrl = highResCanvas.toDataURL('image/jpeg', 0.92);
    document.getElementById('authorAvatarCropped').value = dataUrl;
}

function onZoomChange(val) {
    scale = parseFloat(val);
    document.getElementById('zoomValBadge').textContent = `${Math.round(scale * 100)}%`;
    renderCanvas();
}

function adjustZoom(delta) {
    const slider = document.getElementById('zoomSlider');
    let newVal = parseFloat(slider.value) + delta;
    newVal = Math.max(parseFloat(slider.min), Math.min(parseFloat(slider.max), newVal));
    slider.value = newVal;
    onZoomChange(newVal);
}

function rotateImage(deg) {
    rotation = (rotation + deg) % 360;
    renderCanvas();
}

function resetCrop() {
    if (!currentImg) return;
    imgX = canvas.width / 2;
    imgY = canvas.height / 2;
    const scaleW = canvas.width / currentImg.width;
    const scaleH = canvas.height / currentImg.height;
    scale = Math.max(scaleW, scaleH);
    rotation = 0;
    document.getElementById('zoomSlider').value = scale;
    document.getElementById('zoomValBadge').textContent = `${Math.round(scale * 100)}%`;
    renderCanvas();
}

// Canvas Mouse & Touch Dragging
const wrapper = document.getElementById('canvasWrapper');

function startDrag(e) {
    if (!currentImg) return;
    isDragging = true;
    wrapper.style.cursor = 'grabbing';
    const pos = getEventPos(e);
    startX = pos.x - imgX;
    startY = pos.y - imgY;
}

function onDrag(e) {
    if (!isDragging || !currentImg) return;
    if (e.cancelable) e.preventDefault();
    const pos = getEventPos(e);
    imgX = pos.x - startX;
    imgY = pos.y - startY;
    renderCanvas();
}

function stopDrag() {
    if (isDragging) {
        isDragging = false;
        wrapper.style.cursor = 'grab';
        exportCroppedAvatar();
    }
}

function getEventPos(e) {
    const rect = canvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

wrapper.addEventListener('mousedown', startDrag);
window.addEventListener('mousemove', onDrag);
window.addEventListener('mouseup', stopDrag);

wrapper.addEventListener('touchstart', startDrag, { passive: false });
window.addEventListener('touchmove', onDrag, { passive: false });
window.addEventListener('touchend', stopDrag);

/* =========================================================================
   2. GENRE / WRITING TOPICS CHIP SELECTOR & TICK MARKS
   ========================================================================= */
function toggleGenreChip(checkbox) {
    const label = checkbox.closest('.genre-chip-label');
    const icon = label.querySelector('.genre-icon');
    const tick = label.querySelector('.badge-tick');
    
    if (checkbox.checked) {
        label.classList.add('border-success', 'bg-success-subtle', 'text-success', 'fw-bold');
        label.classList.remove('text-dark');
        if (icon) icon.classList.add('text-success');
        if (tick) {
            tick.classList.remove('border', 'text-transparent');
            tick.classList.add('bg-success', 'text-white');
        }
    } else {
        label.classList.remove('border-success', 'bg-success-subtle', 'text-success', 'fw-bold');
        label.classList.add('text-dark');
        if (icon) icon.classList.remove('text-success');
        if (tick) {
            tick.classList.remove('bg-success', 'text-white');
            tick.classList.add('border', 'text-transparent');
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
   3. PASSWORD UTILITIES
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
    if (password.length >= 6) score += 25;
    if (password.length >= 8) score += 25;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 15;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 10;

    if (score < 40) {
        bar.style.width = '30%';
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'দুর্বল (Weak)';
        text.className = 'text-danger fw-bold';
    } else if (score < 75) {
        bar.style.width = '65%';
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
document.getElementById('authorRegForm')?.addEventListener('submit', function() {
    exportCroppedAvatar();
    syncCombinedGenres();
});
</script>
@endsection
