@extends('layouts.app')

@section('title', 'Library Book Grant — Registration & Application — ideaabd')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --lib-navy: #064e3b;
        --lib-green: #047857;
        --lib-light: #ecfdf5;
        --lib-gold: #f59e0b;
    }
    .scholarship-form-wrap {
        max-width: 900px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .form-table-card {
        background: #ffffff;
        border: 1.5px solid var(--lib-green);
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(6, 78, 59, 0.08);
        overflow: hidden;
    }
    .form-header-bar {
        background: linear-gradient(135deg, #064e3b 0%, #047857 55%, #059669 100%);
        color: #ffffff;
        padding: 16px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
    }
    .form-title-heading {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -0.3px;
        color: #ffffff;
        margin: 0;
        line-height: 1.2;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-title-heading .title-icon-wrap {
        width: 34px;
        height: 34px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fbbf24;
        font-size: 16px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
    }
    .form-section-head {
        background: #f8fafc;
        color: #0f172a;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: 0.6px;
        padding: 10px 18px;
        border-top: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
        text-transform: uppercase;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .grid-table {
        width: 100%;
        border-collapse: collapse;
    }
    .grid-table td, .grid-table th {
        border: 1px solid #e2e8f0;
        padding: 7px 10px;
        vertical-align: middle;
    }
    .grid-table .label-col {
        background-color: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        width: 20%;
    }
    .grid-table .val-col {
        padding: 5px 8px;
    }
    .grid-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 6px 9px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .grid-input:focus {
        border-color: var(--lib-green);
        outline: 0;
        box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.18);
    }
    .photo-cell-box {
        width: 130px;
        height: 155px;
        border: 1.5px dashed #94a3b8;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        margin: 0 auto;
        transition: all 0.2s;
    }
    .photo-cell-box:hover {
        border-color: var(--lib-green);
        background: #f0fdf4;
    }
    .photo-cell-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }
    .opt-badge {
        font-size: 10px;
        font-weight: bold;
        background: #047857;
        color: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        margin-top: 4px;
        display: none;
    }
    .genre-chip-box {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 12px;
        color: #334155;
        cursor: pointer;
        user-select: none;
        margin: 2px;
    }
    .genre-chip-box input[type="checkbox"] {
        accent-color: #047857;
        cursor: pointer;
    }
    .word-badge {
        font-size: 11px;
        font-weight: bold;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .btn-submit-app {
        background: linear-gradient(135deg, #064e3b 0%, #047857 60%, #059669 100%);
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        padding: 11px 36px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(4, 120, 87, 0.3);
    }
    .btn-submit-app:hover {
        background: #064e3b;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(4, 120, 87, 0.45);
    }
</style>

<div class="container py-4">
    <div class="scholarship-form-wrap">

        @if(session('error'))
            <div class="alert alert-danger rounded-2 py-2.5 px-3 mb-3 small">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger rounded-2 py-2 px-3 mb-3 small">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="dynamicLibraryForm" class="form-table-card">
            @csrf
            
            {{-- Hidden input for Client-Side Auto-Optimized Photo Blob (Base64) --}}
            <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

            {{-- HEADER BAR --}}
            <div class="form-header-bar">
                <div class="form-title-heading">
                    <div class="title-icon-wrap">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <div>
                        <span>Library Book Grant</span>
                    </div>
                </div>
                <span class="badge bg-warning text-dark fw-bold px-2.5 py-1.5 rounded-pill shadow-xs" style="font-size: 11px; letter-spacing: 0.5px;">LIBRARY COPY</span>
            </div>

            {{-- 1. LIBRARY DETAILS --}}
            <div class="form-section-head">1. Library & Institutional Details</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Library Name <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 48%;">
                        <input type="text" name="institution_or_org" class="grid-input" placeholder="Enter Library or Institution Name" value="{{ old('institution_or_org') }}" required>
                    </td>
                    <td rowspan="4" colspan="2" class="text-center" style="width: 32%; vertical-align: middle; background: #fafafa;">
                        <div class="photo-cell-box" onclick="document.getElementById('rawPhotoInput').click()" title="Upload Photo">
                            <img id="previewImg" class="photo-cell-img" alt="Library Photo">
                            <div id="uploadPrompt" class="p-2 text-center">
                                <div style="font-size: 24px;">🏛️</div>
                                <div style="font-size: 11px; font-weight: bold; color: #047857;">Upload Photo</div>
                                <div style="font-size: 9.5px; color: #64748b;">(Signboard / Building)</div>
                            </div>
                        </div>
                        <div id="optSizeBadge" class="opt-badge">✓ Ready</div>
                        <input type="file" id="rawPhotoInput" name="student_photo" accept="image/*" class="d-none" onchange="handlePhotoOptimize(this)">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Library Type <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="library_type" class="grid-input" required>
                            <option value="Public / Community Library" {{ old('library_type') === 'Public / Community Library' ? 'selected' : '' }}>Public / Community Library</option>
                            <option value="School Library" {{ old('library_type') === 'School Library' ? 'selected' : '' }}>School Library</option>
                            <option value="College Library" {{ old('library_type') === 'College Library' ? 'selected' : '' }}>College Library</option>
                            <option value="Madrasa Library" {{ old('library_type') === 'Madrasa Library' ? 'selected' : '' }}>Madrasa Library</option>
                            <option value="University / Departmental Library" {{ old('library_type') === 'University / Departmental Library' ? 'selected' : '' }}>University / Departmental Library</option>
                            <option value="Youth Club / Organization" {{ old('library_type') === 'Youth Club / Organization' ? 'selected' : '' }}>Youth Club / Organization</option>
                            <option value="Other Library" {{ old('library_type') === 'Other Library' ? 'selected' : '' }}>Other Library</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Registration No</td>
                    <td class="val-col">
                        <input type="text" name="reg_no" class="grid-input" placeholder="Govt / Book Center Reg No (if any)" value="{{ old('reg_no') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Est. Year</td>
                    <td class="val-col">
                        <input type="text" name="established_year" class="grid-input" placeholder="e.g. 2020" value="{{ old('established_year') }}">
                    </td>
                </tr>
            </table>

            {{-- 2. REPRESENTATIVE DETAILS --}}
            <div class="form-section-head">2. Representative & Contact Details</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Representative Name <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 30%;">
                        <input type="text" name="name" class="grid-input" placeholder="Full Name" value="{{ old('name', $user?->name) }}" required>
                    </td>
                    <td class="label-col" style="width: 20%;">Designation <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 30%;">
                        <select name="designation_or_class" class="grid-input" required>
                            <option value="General Secretary" {{ old('designation_or_class') === 'General Secretary' ? 'selected' : '' }}>General Secretary</option>
                            <option value="President" {{ old('designation_or_class') === 'President' ? 'selected' : '' }}>President</option>
                            <option value="Director / Founder" {{ old('designation_or_class') === 'Director / Founder' ? 'selected' : '' }}>Director / Founder</option>
                            <option value="Principal / Headmaster" {{ old('designation_or_class') === 'Principal / Headmaster' ? 'selected' : '' }}>Principal / Headmaster</option>
                            <option value="Librarian" {{ old('designation_or_class') === 'Librarian' ? 'selected' : '' }}>Librarian</option>
                            <option value="Member / Convener" {{ old('designation_or_class') === 'Member / Convener' ? 'selected' : '' }}>Member / Convener</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Mobile Number <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="tel" name="phone" id="libPhone" class="grid-input font-monospace" placeholder="017XXXXXXXX" value="{{ old('phone', $user?->phone) }}" required oninput="formatPhone(this)">
                    </td>
                    <td class="label-col">Alternative Mobile</td>
                    <td class="val-col">
                        <input type="tel" name="guardian_phone" class="grid-input font-monospace" placeholder="018XXXXXXXX" value="{{ old('guardian_phone') }}" oninput="formatPhone(this)">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Email Address</td>
                    <td class="val-col">
                        <input type="email" name="email" class="grid-input" placeholder="info@example.com (Optional)" value="{{ old('email', $user?->email) }}">
                    </td>
                    <td class="label-col">National ID (NID) No</td>
                    <td class="val-col">
                        <input type="text" name="nid" class="grid-input font-monospace" placeholder="10 or 17 Digit NID Number" value="{{ old('nid') }}">
                    </td>
                </tr>
            </table>

            {{-- 3. LOCATION & ADDRESS --}}
            <div class="form-section-head">3. Location & Address</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Division <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 30%;">
                        <select name="division" id="libDivision" class="grid-input" required>
                            <option value="">-- Select Division --</option>
                        </select>
                    </td>
                    <td class="label-col" style="width: 20%;">District <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 30%;">
                        <select name="district" id="libDistrict" class="grid-input" required>
                            <option value="">-- Select District --</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Upazila / Thana / Pourashava <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="thana" id="libUpazila" class="grid-input" required>
                            <option value="">-- Select Upazila / City / Pourashava --</option>
                        </select>
                    </td>
                    <td class="label-col">Post Office & Code</td>
                    <td class="val-col">
                        <select name="post_office" id="libPostOffice" class="grid-input">
                            <option value="">-- Select Post Office --</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Full Address <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="address" id="libAddress" class="grid-input" placeholder="Village / Area / Ward, Road No, Holding No or landmark..." value="{{ old('address') }}" required>
                    </td>
                </tr>
            </table>

            {{-- 4. STATISTICS & REQUIREMENTS --}}
            <div class="form-section-head">4. Statistics & Book Requirements</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Regular Readers</td>
                    <td class="val-col" style="width: 30%;">
                        <input type="text" name="reader_count" class="grid-input" placeholder="e.g. 50" value="{{ old('reader_count') }}">
                    </td>
                    <td class="label-col" style="width: 20%;">Current Books</td>
                    <td class="val-col" style="width: 30%;">
                        <input type="text" name="current_book_count" class="grid-input" placeholder="e.g. 500" value="{{ old('current_book_count') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Delivery Method <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <select name="delivery_method" class="grid-input" required>
                            <option value="Direct Pickup from Office" selected>Direct Pickup from Office</option>
                            <option value="From Book Distribution Event / Seminar">From Book Distribution Event / Seminar</option>
                            <option value="Courier Service (Sundarban / SA Paribahan)">Courier Service (Sundarban / SA Paribahan)</option>
                            <option value="By Postal Mail">By Postal Mail</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Preferred Categories</td>
                    <td colspan="3" class="val-col">
                        <div class="d-flex flex-wrap gap-1">
                            @php
                                $genreOptions = [
                                    'Literature & Novels', 'Liberation War & History', 'Poetry & Rhymes',
                                    'Children & Teenagers', 'Science & Technology', 'Career & Self-Development',
                                    'Islamic & Religious', 'General Knowledge', 'Biography & Memoirs'
                                ];
                            @endphp
                            @foreach($genreOptions as $genre)
                                <label class="genre-chip-box">
                                    <input type="checkbox" name="preferred_genres[]" value="{{ $genre }}">
                                    <span>{{ $genre }}</span>
                                </label>
                            @endforeach
                        </div>
                    </td>
                </tr>
            </table>

            {{-- 5. PURPOSE OF GRANT --}}
            <div class="form-section-head d-flex align-items-center justify-content-between">
                <span>5. Purpose of Grant / Brief Statement</span>
                <span id="wordCounter" class="word-badge bg-secondary text-white">0 / 80 words</span>
            </div>
            <div class="p-3 bg-white">
                <textarea name="scholarship_reason" id="reasonText" rows="3" class="grid-input" placeholder="Briefly describe your library activities and why this book grant is needed (Maximum 80 words)..." oninput="handleWordCount(this)">{{ old('scholarship_reason') }}</textarea>
                <div id="wordLimitAlert" class="text-danger small mt-1 d-none font-monospace">
                    ⚠ Word limit of 80 words exceeded. Please shorten.
                </div>
            </div>

            {{-- 6. DECLARATION & SUBMISSION --}}
            <div class="p-3 bg-light border-top text-center">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <input type="checkbox" id="agreeCheck" name="declaration_agreed" checked required style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="agreeCheck" class="small mb-0 text-dark fw-semibold" style="cursor: pointer;">
                        I declare that all the information provided above is true and accurate. <span class="text-danger">*</span>
                    </label>
                </div>

                <button type="submit" id="submitBtn" class="btn-submit-app">
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit Application
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    // Client-side instant Canvas Auto-Optimizer for Library Photo
    function handlePhotoOptimize(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const initialSizeKB = (file.size / 1024).toFixed(0);

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Target photo size: 300 x 360
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const targetW = 300;
                const targetH = 360;

                canvas.width = targetW;
                canvas.height = targetH;

                // Center Crop
                const srcRatio = img.width / img.height;
                const targetRatio = targetW / targetH;
                let cropW = img.width;
                let cropH = img.height;
                let cropX = 0;
                let cropY = 0;

                if (srcRatio > targetRatio) {
                    cropW = img.height * targetRatio;
                    cropX = (img.width - cropW) / 2;
                } else {
                    cropH = img.width / targetRatio;
                    cropY = (img.height - cropH) / 2;
                }

                ctx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, targetW, targetH);

                // High quality compressed JPEG data URL
                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                
                // Put in hidden input for instant upload
                document.getElementById('optimizedPhotoData').value = dataUrl;

                // Show preview
                const preview = document.getElementById('previewImg');
                const prompt = document.getElementById('uploadPrompt');
                const badge = document.getElementById('optSizeBadge');

                preview.src = dataUrl;
                preview.style.display = 'block';
                prompt.style.display = 'none';

                badge.textContent = `✓ Ready`;
                badge.style.display = 'inline-block';
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Live word counter & limiter for library statement
    function handleWordCount(textarea) {
        const text = textarea.value.trim();
        const words = text ? text.split(/\s+/).filter(Boolean) : [];
        const count = words.length;
        const badge = document.getElementById('wordCounter');
        const alertBox = document.getElementById('wordLimitAlert');
        const submitBtn = document.getElementById('submitBtn');

        badge.textContent = count + ' / 80 words';

        if (count > 80) {
            badge.className = 'word-badge bg-danger text-white';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = true;
        } else if (count >= 70) {
            badge.className = 'word-badge bg-warning text-dark';
            alertBox.classList.add('d-none');
            submitBtn.disabled = false;
        } else {
            badge.className = 'word-badge bg-success text-white';
            alertBox.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    // Auto format phone number
    function formatPhone(input) {
        input.value = input.value.replace(/[^0-9]/g, '').slice(0, 11);
    }

    // Dynamic 4-Tier Location Cascading (বিভাগ -> জেলা -> মহানগর/উপজেলা/পৌরসভা -> পোস্ট অফিস)
    function initLibraryLocationCascade() {
        const divSelect = document.getElementById('libDivision');
        const distSelect = document.getElementById('libDistrict');
        const upaSelect = document.getElementById('libUpazila');
        const poSelect = document.getElementById('libPostOffice');
        if (!divSelect || !window.BD_GEO) return;

        const oldDiv = "{{ old('division') }}";
        const oldDist = "{{ old('district') }}";
        const oldUpa = "{{ old('thana') }}";
        const oldPo = "{{ old('post_office') }}";

        // 1. Populate Divisions
        divSelect.innerHTML = '<option value="">-- Select Division --</option>';
        if (window.BD_GEO.divisions) {
            Object.keys(window.BD_GEO.divisions).forEach(div => {
                const opt = document.createElement('option');
                opt.value = div;
                opt.textContent = div;
                if (oldDiv === div || (oldDiv && oldDiv.toLowerCase() === div.toLowerCase())) {
                    opt.selected = true;
                }
                divSelect.appendChild(opt);
            });
        }

        // 2. Populate Districts based on selected Division
        function populateDistricts(selectedDiv, preselectedDist = '') {
            distSelect.innerHTML = '<option value="">-- Select District --</option>';
            upaSelect.innerHTML = '<option value="">-- Select Upazila / City / Pourashava --</option>';
            poSelect.innerHTML = '<option value="">-- Select Post Office --</option>';

            if (selectedDiv && window.BD_GEO.divisions[selectedDiv]) {
                window.BD_GEO.divisions[selectedDiv].forEach(dist => {
                    const opt = document.createElement('option');
                    opt.value = dist;
                    opt.textContent = dist;
                    if (preselectedDist === dist || (preselectedDist && preselectedDist.toLowerCase() === dist.toLowerCase())) {
                        opt.selected = true;
                    }
                    distSelect.appendChild(opt);
                });
            }
        }

        // 3. Populate Upazilas / Pourashavas & Post Offices based on selected District
        function populateUpazilasAndPostOffices(selectedDist, preselectedUpa = '', preselectedPo = '') {
            upaSelect.innerHTML = '<option value="">-- Select Upazila / City / Pourashava --</option>';
            poSelect.innerHTML = '<option value="">-- Select Post Office --</option>';

            if (selectedDist && window.BD_GEO.upazilas && window.BD_GEO.upazilas[selectedDist]) {
                window.BD_GEO.upazilas[selectedDist].forEach(upa => {
                    const opt = document.createElement('option');
                    opt.value = upa;
                    opt.textContent = upa;
                    if (preselectedUpa === upa || (preselectedUpa && preselectedUpa.toLowerCase() === upa.toLowerCase())) {
                        opt.selected = true;
                    }
                    upaSelect.appendChild(opt);
                });
            }

            // Post Offices
            if (selectedDist) {
                let poList = [];
                if (window.BD_GEO.postOffices && window.BD_GEO.postOffices[selectedDist]) {
                    poList = window.BD_GEO.postOffices[selectedDist];
                } else if (window.BD_GEO.upazilas && window.BD_GEO.upazilas[selectedDist]) {
                    // Fallback generating post offices from district and upazilas
                    poList.push(`${selectedDist} Head Post Office`);
                    window.BD_GEO.upazilas[selectedDist].forEach(u => poList.push(`${u} Post Office`));
                }

                poList.forEach(po => {
                    const opt = document.createElement('option');
                    opt.value = po;
                    opt.textContent = po;
                    if (preselectedPo === po || (preselectedPo && preselectedPo.toLowerCase() === po.toLowerCase())) {
                        opt.selected = true;
                    }
                    poSelect.appendChild(opt);
                });
            }
        }

        divSelect.addEventListener('change', function() {
            populateDistricts(this.value);
        });

        distSelect.addEventListener('change', function() {
            populateUpazilasAndPostOffices(this.value);
        });

        // Preload default / old selections
        if (oldDiv) {
            populateDistricts(oldDiv, oldDist);
            if (oldDist) {
                populateUpazilasAndPostOffices(oldDist, oldUpa, oldPo);
            }
        }
    }

    // Prevent double submission & show loading
    document.getElementById('dynamicLibraryForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting Application...';
        btn.disabled = true;
    });

    document.addEventListener('DOMContentLoaded', function() {
        initLibraryLocationCascade();

        const reason = document.getElementById('reasonText');
        if (reason && reason.value) {
            handleWordCount(reason);
        }
    });
</script>
<script src="{{ asset('js/bd-geo-data.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initLibraryLocationCascade === 'function') {
            initLibraryLocationCascade();
        }
    });
</script>
@endsection
