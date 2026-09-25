@extends('layouts.app')

@section('title', 'Education Scholarship Application Form')

@section('content')
<style>
    .scholarship-form-wrap {
        max-width: 900px;
        margin: 0 auto;
    }
    .form-table-card {
        background: #ffffff;
        border: 1.5px solid #0f3a68;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .form-header-bar {
        background: #0f3a68;
        color: #ffffff;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .form-section-head {
        background: #f1f5f9;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 16px;
        border-top: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
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
        width: 18%;
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
        border-color: #0f3a68;
        outline: 0;
        box-shadow: 0 0 0 2px rgba(15, 58, 104, 0.15);
    }
    .grid-input.text-uppercase {
        text-transform: uppercase;
    }
    .photo-cell-box {
        width: 125px;
        height: 150px;
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
        border-color: #0f3a68;
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
        background: #15803d;
        color: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        margin-top: 4px;
        display: none;
    }
    .word-badge {
        font-size: 11px;
        font-weight: bold;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .btn-submit-app {
        background: #0f3a68;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        padding: 10px 32px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-submit-app:hover {
        background: #1e40af;
        transform: translateY(-1px);
    }
</style>

<div class="container py-4">
    <div class="scholarship-form-wrap">

        @if(session('error'))
            <div class="alert alert-danger rounded-2 py-2.5 px-3 mb-3 small">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-2 py-2 px-3 mb-3 small">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="dynamicScholarshipForm" class="form-table-card">
            @csrf
            
            {{-- Hidden input for Client-Side Auto-Optimized Photo Blob (Base64) --}}
            <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

            {{-- HEADER BAR --}}
            <div class="form-header-bar">
                <div>
                    <h5 class="mb-0 fw-bold" style="font-size: 16px;">Joyee Shikkha Britti — Application Form</h5>
                    <div style="font-size: 11px; opacity: 0.9;">Session: 2026-2027 | Higher Secondary & Bachelor's (Hons)</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark fw-bold" style="font-size: 11px;">STUDENT COPY</span>
                </div>
            </div>

            {{-- 1. APPLICANT DETAILS --}}
            <div class="form-section-head">1. Applicant Details</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Student Name <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 48%;">
                        <input type="text" name="name" class="grid-input text-uppercase" placeholder="FULL NAME IN CAPITAL" value="{{ old('name', $user?->name) }}" required>
                    </td>
                    <td rowspan="4" colspan="2" class="text-center" style="width: 34%; vertical-align: middle; background: #fafafa;">
                        <div class="photo-cell-box" onclick="document.getElementById('rawPhotoInput').click()" title="Click to upload/optimize photo">
                            <img id="previewImg" class="photo-cell-img" alt="Student Photo">
                            <div id="uploadPrompt" class="p-2 text-center">
                                <div style="font-size: 24px;">📷</div>
                                <div style="font-size: 11px; font-weight: bold; color: #0f3a68;">Student Photo</div>
                                <div style="font-size: 9.5px; color: #64748b;">Auto-Optimized</div>
                            </div>
                        </div>
                        <div id="optSizeBadge" class="opt-badge">✓ Auto-Optimized</div>
                        <input type="file" id="rawPhotoInput" name="student_photo" accept="image/*" class="d-none" onchange="handlePhotoOptimize(this)">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Gender <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="gender" class="grid-input" required>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Father's Name <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="father_name" class="grid-input text-uppercase" placeholder="FATHER'S FULL NAME" value="{{ old('father_name') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Mother's Name <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="mother_name" class="grid-input text-uppercase" placeholder="MOTHER'S FULL NAME" value="{{ old('mother_name') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Religion <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="religion" class="grid-input" required>
                            <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Hinduism" {{ old('religion') === 'Hinduism' ? 'selected' : '' }}>Hinduism</option>
                            <option value="Christianity" {{ old('religion') === 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option value="Buddhism" {{ old('religion') === 'Buddhism' ? 'selected' : '' }}>Buddhism</option>
                            <option value="Others" {{ old('religion') === 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                    </td>
                    <td class="label-col">Nationality <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="nationality" class="grid-input text-uppercase" value="{{ old('nationality', 'BANGLADESHI') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Birth Date <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="date" name="birth_date" class="grid-input" value="{{ old('birth_date') }}" required>
                    </td>
                    <td class="label-col">Mobile No <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="tel" name="phone" id="studentPhone" class="grid-input font-monospace" placeholder="017XXXXXXXX" value="{{ old('phone', $user?->phone) }}" required oninput="formatPhone(this)">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Marital Status <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="marital_status" class="grid-input" required>
                            <option value="Unmarried" {{ old('marital_status') === 'Unmarried' ? 'selected' : '' }}>Unmarried</option>
                            <option value="Married" {{ old('marital_status') === 'Married' ? 'selected' : '' }}>Married</option>
                        </select>
                    </td>
                    <td class="label-col">Family Annual Income (Tk) <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="number" name="annual_income" class="grid-input font-monospace" placeholder="e.g. 50000" value="{{ old('annual_income') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Guardian Name</td>
                    <td class="val-col">
                        <input type="text" name="guardian_name" class="grid-input text-uppercase" placeholder="GUARDIAN'S NAME" value="{{ old('guardian_name') }}">
                    </td>
                    <td class="label-col">Guardian Mobile</td>
                    <td class="val-col">
                        <input type="tel" name="guardian_phone" class="grid-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('guardian_phone') }}" oninput="formatPhone(this)">
                    </td>
                </tr>
            </table>

            {{-- 2. ACADEMIC INFORMATION --}}
            <div class="form-section-head">2. Academic Information</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Group <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <select name="group" class="grid-input" required>
                            <option value="HUMANITIES" {{ old('group') === 'HUMANITIES' ? 'selected' : '' }}>HUMANITIES</option>
                            <option value="SCIENCE" {{ old('group') === 'SCIENCE' ? 'selected' : '' }}>SCIENCE</option>
                            <option value="BUSINESS STUDIES" {{ old('group') === 'BUSINESS STUDIES' ? 'selected' : '' }}>BUSINESS STUDIES</option>
                            <option value="OTHERS" {{ old('group') === 'OTHERS' ? 'selected' : '' }}>OTHERS</option>
                        </select>
                    </td>
                    <td class="label-col">Admission Roll <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <input type="text" name="admission_roll" class="grid-input" placeholder="e.g. 5288534" value="{{ old('admission_roll') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Merit Position</td>
                    <td class="val-col">
                        <input type="text" name="merit_position" class="grid-input" placeholder="e.g. 1682" value="{{ old('merit_position') }}">
                    </td>
                    <td class="label-col">College Code</td>
                    <td class="val-col">
                        <input type="text" name="college_code" class="grid-input" placeholder="e.g. 3401" value="{{ old('college_code') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">College Name <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="college_name" class="grid-input text-uppercase" placeholder="e.g. Dinajpur Govt. College" value="{{ old('college_name') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Assigned Subject <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="assigned_subject" class="grid-input text-uppercase" placeholder="e.g. ENGLISH (1101)" value="{{ old('assigned_subject') }}" required>
                    </td>
                    <td class="label-col">Previous Subject</td>
                    <td class="val-col">
                        <input type="text" name="previous_subject" class="grid-input text-uppercase" placeholder="e.g. ECONOMICS (2201)" value="{{ old('previous_subject') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Subject Choice</td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="subject_choice" class="grid-input text-uppercase" placeholder="e.g. ENGLISH, ECONOMICS, BANGLA, SOCIOLOGY, POLITICAL SCIENCE" value="{{ old('subject_choice') }}">
                    </td>
                </tr>
            </table>

            {{-- 3. EDUCATIONAL QUALIFICATIONS --}}
            <div class="form-section-head">3. Educational Qualifications</div>
            <div class="table-responsive">
                <table class="grid-table text-center mb-0">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 11px; font-weight: bold;">
                            <th style="width: 20%; text-align: left;">Examination</th>
                            <th style="width: 14%;">Roll No</th>
                            <th style="width: 16%;">Board / Univ</th>
                            <th style="width: 28%;">Institute Name</th>
                            <th style="width: 11%;">Year</th>
                            <th style="width: 11%;">GPA / CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">SSC / Equivalent <span class="text-danger">*</span></td>
                            <td><input type="text" name="ssc_roll" class="grid-input text-center font-monospace" placeholder="Roll" value="{{ old('ssc_roll') }}" required></td>
                            <td><input type="text" name="ssc_board" class="grid-input text-center text-uppercase" placeholder="e.g. DINAJPUR" value="{{ old('ssc_board') }}" required></td>
                            <td><input type="text" name="ssc_institute" class="grid-input text-uppercase" placeholder="School Name" value="{{ old('ssc_institute') }}" required></td>
                            <td><input type="number" name="ssc_year" class="grid-input text-center font-monospace" placeholder="2019" value="{{ old('ssc_year') }}" required></td>
                            <td><input type="text" name="ssc_gpa" class="grid-input text-center font-monospace" placeholder="4.67" value="{{ old('ssc_gpa') }}" required></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">HSC / Equivalent</td>
                            <td><input type="text" name="hsc_roll" class="grid-input text-center font-monospace" placeholder="Roll" value="{{ old('hsc_roll') }}"></td>
                            <td><input type="text" name="hsc_board" class="grid-input text-center text-uppercase" placeholder="e.g. DINAJPUR" value="{{ old('hsc_board') }}"></td>
                            <td><input type="text" name="hsc_institute" class="grid-input text-uppercase" placeholder="College Name" value="{{ old('hsc_institute') }}"></td>
                            <td><input type="number" name="hsc_year" class="grid-input text-center font-monospace" placeholder="2021" value="{{ old('hsc_year') }}"></td>
                            <td><input type="text" name="hsc_gpa" class="grid-input text-center font-monospace" placeholder="4.67" value="{{ old('hsc_gpa') }}"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">Graduation / Degree</td>
                            <td><input type="text" name="grad_roll" class="grid-input text-center font-monospace" placeholder="Roll / Reg" value="{{ old('grad_roll') }}"></td>
                            <td><input type="text" name="grad_board" class="grid-input text-center text-uppercase" placeholder="e.g. NU / DU" value="{{ old('grad_board') }}"></td>
                            <td><input type="text" name="grad_institute" class="grid-input text-uppercase" placeholder="College / University" value="{{ old('grad_institute') }}"></td>
                            <td><input type="number" name="grad_year" class="grid-input text-center font-monospace" placeholder="2025" value="{{ old('grad_year') }}"></td>
                            <td><input type="text" name="grad_cgpa" class="grid-input text-center font-monospace" placeholder="3.50" value="{{ old('grad_cgpa') }}"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">Masters / Other Degree</td>
                            <td><input type="text" name="other_roll" class="grid-input text-center font-monospace" placeholder="Roll / Reg" value="{{ old('other_roll') }}"></td>
                            <td><input type="text" name="other_board" class="grid-input text-center text-uppercase" placeholder="Board / Univ" value="{{ old('other_board') }}"></td>
                            <td><input type="text" name="other_institute" class="grid-input text-uppercase" placeholder="Institute / Dept" value="{{ old('other_institute') }}"></td>
                            <td><input type="number" name="other_year" class="grid-input text-center font-monospace" placeholder="Year" value="{{ old('other_year') }}"></td>
                            <td><input type="text" name="other_cgpa" class="grid-input text-center font-monospace" placeholder="CGPA" value="{{ old('other_cgpa') }}"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- 4. ADDRESS DETAILS --}}
            <div class="form-section-head d-flex justify-content-between align-items-center">
                <span>4. Address Details</span>
                <label class="mb-0 fw-normal small text-dark d-flex align-items-center gap-1" style="text-transform: none; cursor: pointer;">
                    <input type="checkbox" id="syncAddressCheck" onchange="syncPermanentToPresent()"> <strong>Same as Permanent Address</strong>
                </label>
            </div>
            
            {{-- Hidden Full Formatted Address Inputs for Backend Storage & Print --}}
            <input type="hidden" name="permanent_address" id="permFullAddress" value="{{ old('permanent_address') }}">
            <input type="hidden" name="present_address" id="presFullAddress" value="{{ old('present_address') }}">

            {{-- 4.1 Permanent Address --}}
            <div class="p-2 bg-light border-bottom fw-bold small text-primary">
                <i class="fa-solid fa-house-user me-1"></i> Permanent Address (স্থায়ী ঠিকানা)
            </div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Division (বিভাগ) <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <select name="perm_division" id="permDivision" class="grid-input" required></select>
                    </td>
                    <td class="label-col">District (জেলা) <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <select name="permanent_district" id="permDistrict" class="grid-input" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">City Corp / Upazila (উপজেলা) <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="perm_upazila" id="permUpazila" class="grid-input" required></select>
                    </td>
                    <td class="label-col">Post Office (পোস্ট অফিস) <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="perm_post_office" id="permPostOffice" class="grid-input" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Village / Road / House <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="perm_village" id="permVillage" class="grid-input text-uppercase" placeholder="Village / Ward / Road / House details" required>
                    </td>
                </tr>
            </table>

            {{-- 4.2 Present Address --}}
            <div class="p-2 bg-light border-top border-bottom fw-bold small text-primary">
                <i class="fa-solid fa-location-dot me-1"></i> Present Address (বর্তমান ঠিকানা)
            </div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Division (বিভাগ) <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <select name="pres_division" id="presDivision" class="grid-input" required></select>
                    </td>
                    <td class="label-col">District (জেলা) <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <select name="present_district" id="presDistrict" class="grid-input" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">City Corp / Upazila (উপজেলা) <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pres_upazila" id="presUpazila" class="grid-input" required></select>
                    </td>
                    <td class="label-col">Post Office (পোস্ট অফিস) <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pres_post_office" id="presPostOffice" class="grid-input" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Village / Road / House <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="pres_village" id="presVillage" class="grid-input text-uppercase" placeholder="Village / Ward / Road / House details" required>
                    </td>
                </tr>
            </table>

            {{-- 5. REASON FOR SCHOLARSHIP (50 WORDS MAX) --}}
            <div class="form-section-head d-flex justify-content-between align-items-center">
                <span>5. Reason for Scholarship Application (Max 50 Words) <span class="text-danger">*</span></span>
                <span id="wordCounter" class="word-badge bg-secondary text-white">0 / 50 words</span>
            </div>
            <div class="p-3 bg-white">
                <textarea name="scholarship_reason" id="reasonText" rows="3" class="grid-input" placeholder="State briefly why you need this scholarship (Max 50 words in English)..." oninput="handleWordCount(this)" required>{{ old('scholarship_reason') }}</textarea>
                <div id="wordLimitAlert" class="text-danger small mt-1 d-none font-monospace">
                    ⚠ Exceeded 50 words limit. Extra words will be ignored.
                </div>
            </div>

            {{-- 6. DECLARATION & SUBMISSION --}}
            <div class="p-3 bg-light border-top text-center">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <input type="checkbox" id="agreeCheck" name="declaration_agreed" checked required style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="agreeCheck" class="small mb-0 text-dark fw-semibold" style="cursor: pointer;">
                        I declare that all information provided is accurate and true. <span class="text-danger">*</span>
                    </label>
                </div>

                {{-- Organization Disclaimer --}}
                <div class="text-muted mt-1 mb-3" style="font-size: 11.5px; line-height: 1.55; max-width: 740px; margin: 0 auto;">
                    Idea Prokashon does not provide any scholarships; it only facilitates online application submission through its website to support the Firiye Dekha organization and make the application process easier for applicants.
                </div>

                <button type="submit" id="submitBtn" class="btn-submit-app">
                    Submit Scholarship Application
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    // Client-side instant Canvas Auto-Optimizer for student photo
    function handlePhotoOptimize(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const initialSizeKB = (file.size / 1024).toFixed(0);

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Target passport size: 300 x 360 (5:6 aspect ratio)
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

                // High quality compressed WebP/JPEG data URL
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

                const optSizeKB = ((dataUrl.length * 0.75) / 1024).toFixed(0);
                badge.textContent = `✓ ${initialSizeKB} KB → ${optSizeKB} KB`;
                badge.style.display = 'inline-block';
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Live 50-word counter & strict limiter
    function handleWordCount(textarea) {
        const text = textarea.value.trim();
        const words = text ? text.split(/\s+/).filter(Boolean) : [];
        const count = words.length;
        const badge = document.getElementById('wordCounter');
        const alertBox = document.getElementById('wordLimitAlert');
        const submitBtn = document.getElementById('submitBtn');

        badge.textContent = count + ' / 50 words';

        if (count > 50) {
            badge.className = 'word-badge bg-danger text-white';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = true;
        } else if (count >= 45) {
            badge.className = 'word-badge bg-warning text-dark';
            alertBox.classList.add('d-none');
            submitBtn.disabled = false;
        } else {
            badge.className = 'word-badge bg-success text-white';
            alertBox.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    // Auto sync address
    function syncAddresses() {
        const checked = document.getElementById('syncAddressCheck').checked;
        if (checked) {
            document.getElementById('presAddr').value = document.getElementById('permAddr').value;
            document.getElementById('presDist').value = document.getElementById('permDist').value;
        }
    }

    // Auto format phone
    function formatPhone(input) {
        input.value = input.value.replace(/[^0-9]/g, '').slice(0, 11);
    }

    // Prevent double submission
    document.getElementById('dynamicScholarshipForm').addEventListener('submit', function(e) {
        assembleFormattedAddress('perm');
        assembleFormattedAddress('pres');
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
        btn.disabled = true;
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initAddressChaining === 'function') {
            initAddressChaining('perm');
            initAddressChaining('pres');
        }

        const reason = document.getElementById('reasonText');
        if (reason && reason.value) {
            handleWordCount(reason);
        }
    });
</script>
<script src="{{ asset('js/bd-geo-data.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initAddressChaining === 'function') {
            initAddressChaining('perm');
            initAddressChaining('pres');
        }
    });
</script>
@endsection
