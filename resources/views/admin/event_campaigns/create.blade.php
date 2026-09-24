@extends('layouts.admin')

@section('title', 'New Campaign — ideaabd')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Breadcrumb & Title --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.event-campaigns.index') }}" class="text-decoration-none text-muted">Campaigns</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">New</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0 text-gray-900">New Campaign</h1>
        </div>
        <a href="{{ route('admin.event-campaigns.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 small">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="alert alert-danger rounded-4 p-3 mb-4">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Errors:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.event-campaigns.store') }}" method="POST" enctype="multipart/form-data" id="campaignForm">
        @csrf
        <input type="hidden" name="custom_fields_json" id="custom_fields_json" value="{{ old('custom_fields_json', '[]') }}">
        <input type="hidden" name="form_settings_json" id="form_settings_json" value="{{ old('form_settings_json', '{}') }}">

        <div class="row g-4">
            {{-- Left column: Campaign Details & Custom Fields Builder --}}
            <div class="col-lg-8">
                
                {{-- Basics --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bullhorn text-primary"></i> Basics
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="campaignTitle" class="form-control" placeholder="e.g. Literary Festival 2026 or Scholarship Form" value="{{ old('title') }}" required oninput="autoGenerateSlug(this.value)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">URL Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted fw-monospace small">{{ url('/') }}/</span>
                            <input type="text" name="slug" id="campaignSlug" class="form-control font-monospace" placeholder="rsutshab" value="{{ old('slug') }}" required style="font-size: 14px;">
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <small class="text-muted" style="font-size: 11.5px;">Browser direct URL: English letters, numbers, hyphens.</small>
                            <span id="slugPreview" class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">Preview: {{ url('/') }}/...</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="event" {{ old('type') == 'event' ? 'selected' : '' }}>Event</option>
                                <option value="scholarship" {{ old('type') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                                <option value="donation" {{ old('type') == 'donation' ? 'selected' : '' }}>Donation</option>
                                <option value="competition" {{ old('type') == 'competition' ? 'selected' : '' }}>Competition</option>
                                <option value="workshop" {{ old('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Badge</label>
                            <input type="text" name="badge_text" class="form-control" placeholder="e.g. Open, Registration Live, 2026" value="{{ old('badge_text') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Short Summary</label>
                        <textarea name="short_description" rows="2" class="form-control" placeholder="Teaser summary displayed below header...">{{ old('short_description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Full Description & Rules</label>
                        <textarea name="description" rows="6" class="form-control" placeholder="Detailed instructions, terms, and guidelines...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Banner Image</label>
                        <input type="file" name="banner_image" id="bannerInput" class="form-control" accept="image/*" onchange="previewBanner(this)">
                        <small class="text-muted" style="font-size: 11px;">Recommended: 1200x500px (JPG, PNG, WebP, max 2MB).</small>
                        <div id="bannerPreviewContainer" class="mt-2 d-none">
                            <img id="bannerPreviewImg" src="" alt="Preview" class="rounded-3 img-fluid border" style="max-height: 160px; object-fit: cover;">
                        </div>
                    </div>
                </div>

                {{-- Standard Form Fields Customizer (Rename Titles & Show/Hide) --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-list-check text-success"></i> Standard Fields Settings
                        </h5>
                        <span class="badge bg-light text-muted border">Title & Visibility</span>
                    </div>
                    <p class="text-muted small mb-3">
                        Change titles/labels of default registration fields or disable unneeded fields for this event.
                    </p>

                    <div class="table-responsive border rounded-3 overflow-hidden mb-1">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light text-secondary text-uppercase" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-3 py-2">Field</th>
                                    <th class="py-2">Display Title / Label (টাইটেল চেঞ্জ)</th>
                                    <th class="pe-3 py-2 text-end" style="width: 100px;">Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3 fw-semibold">Name</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm std-field-label" data-key="name" value="পূর্ণ নাম (Full Name)" placeholder="পূর্ণ নাম">
                                    </td>
                                    <td class="pe-3 text-end">
                                        <span class="badge bg-success-subtle text-success">Required</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-semibold">Phone</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm std-field-label" data-key="phone" value="মোবাইল নম্বর (Phone)" placeholder="মোবাইল নম্বর">
                                    </td>
                                    <td class="pe-3 text-end">
                                        <span class="badge bg-success-subtle text-success">Required</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-semibold">Email</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm std-field-label" data-key="email" value="ইমেইল ঠিকানা (Email)" placeholder="ইমেইল">
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="form-check form-switch d-inline-block m-0">
                                            <input class="form-check-input std-field-enable" type="checkbox" data-key="email" checked>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-semibold">Institution / Org</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm std-field-label" data-key="institution" value="প্রতিষ্ঠান / পেশা (Institution / Org)" placeholder="প্রতিষ্ঠান / পেশা">
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="form-check form-switch d-inline-block m-0">
                                            <input class="form-check-input std-field-enable" type="checkbox" data-key="institution" checked>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-semibold">District & Thana</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm std-field-label" data-key="location" value="জেলা ও থানা (District & Thana)" placeholder="জেলা ও থানা">
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="form-check form-switch d-inline-block m-0">
                                            <input class="form-check-input std-field-enable" type="checkbox" data-key="location" checked>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-semibold">Full Address</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm std-field-label" data-key="address" value="পূর্ণ ঠিকানা (Full Address)" placeholder="পূর্ণ ঠিকানা">
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="form-check form-switch d-inline-block m-0">
                                            <input class="form-check-input std-field-enable" type="checkbox" data-key="address" checked>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Dynamic Custom Fields Builder (Add/Remove & Title Change) --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-sliders text-primary"></i> Custom Fields (কমানো / বাড়ানো)
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" onclick="addCustomField()">
                            <i class="fa-solid fa-plus me-1"></i> Add Field (বাড়ানো)
                        </button>
                    </div>
                    <p class="text-muted small mb-3">
                        Add, remove, reorder, or rename custom questions/fields (e.g. T-Shirt Size, Roll, Essay Topic).
                    </p>

                    <div id="customFieldsContainer" class="d-flex flex-column gap-2 mb-2">
                        {{-- Injected by JS --}}
                    </div>
                    <div id="emptyFieldsNotice" class="text-center py-3 text-muted border rounded-3 bg-light" style="font-size: 13px;">
                        <i class="fa-solid fa-layer-group me-1 text-secondary"></i> No custom fields configured. Click "Add Field" to create new questions.
                    </div>
                </div>

                {{-- Fee & Donation Settings --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-success"></i> Fee & Donation
                    </h5>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="has_fee_or_donation" value="1" id="feeSwitch" onchange="toggleFeeSection(this.checked)" {{ old('has_fee_or_donation') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="feeSwitch">
                            Enable Fee or Donation
                        </label>
                    </div>

                    <div id="feeSettingsSection" class="{{ old('has_fee_or_donation') ? '' : 'd-none' }}">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">Fixed Fee (৳)</label>
                                <input type="number" step="0.01" name="fee_amount" class="form-control" placeholder="0.00" value="{{ old('fee_amount', '0.00') }}">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4 pt-2">
                                    <input class="form-check-input" type="checkbox" name="is_donation_flexible" value="1" id="flexibleDonationSwitch" {{ old('is_donation_flexible') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="flexibleDonationSwitch">
                                        Flexible Donation (User chooses amount)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">Min Donation (৳)</label>
                                <input type="number" step="0.01" name="min_donation" class="form-control" placeholder="10.00" value="{{ old('min_donation', '10.00') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">Payment Channels</label>
                                <input type="text" name="payment_methods" class="form-control" placeholder="bKash, Nagad, Rocket, Bank" value="{{ old('payment_methods', 'bKash, Nagad, Rocket') }}">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold text-dark small">Payment Instructions</label>
                            <textarea name="payment_instructions" rows="2" class="form-control" placeholder="e.g. Send Money to bKash Personal 01XXXXXXXXX and enter TrxID...">{{ old('payment_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column: Theme, Scheduling, Support & Actions --}}
            <div class="col-lg-4">

                {{-- Visual Theme & Colors --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-palette text-warning"></i> Theme & Color
                    </h5>

                    <label class="form-label fw-semibold text-dark small mb-2">Accent Color</label>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <input type="color" name="theme_color" id="themeColorInput" class="form-control form-control-color border-0 rounded-3 p-1" value="{{ old('theme_color', '#0284c7') }}" title="Choose accent color" style="width: 48px; height: 38px;">
                        <input type="text" id="themeColorHex" class="form-control font-monospace" value="{{ old('theme_color', '#0284c7') }}" oninput="syncColor(this.value)">
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #0284c7;" onclick="pickColor('#0284c7')"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #10b981;" onclick="pickColor('#10b981')"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #8b5cf6;" onclick="pickColor('#8b5cf6')"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #f43f5e;" onclick="pickColor('#f43f5e')"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #d97706;" onclick="pickColor('#d97706')"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #4f46e5;" onclick="pickColor('#4f46e5')"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-0 color-swatch" style="width: 26px; height: 26px; background: #0f172a;" onclick="pickColor('#0f172a')"></button>
                    </div>
                </div>

                {{-- Scheduling & Limits --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-clock text-info"></i> Schedule & Limits
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Starts At</label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Ends At</label>
                        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Max Quota</label>
                        <input type="number" name="max_participants" class="form-control" placeholder="Leave blank for unlimited" value="{{ old('max_participants') }}">
                    </div>

                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch" checked>
                        <label class="form-check-label fw-semibold text-dark" for="activeSwitch">Form Active</label>
                    </div>
                </div>

                {{-- Contact & Support --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-headset text-secondary"></i> Contact & Support
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" placeholder="01558712810" value="{{ old('contact_phone', '01558712810') }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" placeholder="support@ideaabd.com" value="{{ old('contact_email') }}">
                    </div>
                </div>

                {{-- Post Submission & Actions --}}
                <div class="card border-0 shadow-xs rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-success"></i> Completion
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Success Notice</label>
                        <textarea name="success_message" rows="2" class="form-control" placeholder="Registration successful!">{{ old('success_message') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Redirect URL</label>
                        <input type="url" name="redirect_url" class="form-control" placeholder="https://..." value="{{ old('redirect_url') }}">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Publish Campaign
                    </button>
                </div>

            </div>
        </div>
    </form>

</div>

<script>
// Live slug generator & preview
function autoGenerateSlug(text) {
    const slugInput = document.getElementById('campaignSlug');
    if (!slugInput.dataset.manual) {
        let slug = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        if (slug) {
            slugInput.value = slug;
            updateSlugPreview(slug);
        }
    }
}

document.getElementById('campaignSlug').addEventListener('input', function() {
    this.dataset.manual = '1';
    updateSlugPreview(this.value);
});

function updateSlugPreview(slug) {
    const el = document.getElementById('slugPreview');
    el.textContent = 'Preview: {{ url('/') }}/' + (slug || '...');
}

// Banner Preview
function previewBanner(input) {
    const container = document.getElementById('bannerPreviewContainer');
    const img = document.getElementById('bannerPreviewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Fee toggle
function toggleFeeSection(checked) {
    const sec = document.getElementById('feeSettingsSection');
    if (checked) {
        sec.classList.remove('d-none');
    } else {
        sec.classList.add('d-none');
    }
}

// Color synchronization
function pickColor(hex) {
    document.getElementById('themeColorInput').value = hex;
    document.getElementById('themeColorHex').value = hex;
}

function syncColor(hex) {
    if (/^#[0-9A-F]{6}$/i.test(hex)) {
        document.getElementById('themeColorInput').value = hex;
    }
}

document.getElementById('themeColorInput').addEventListener('input', function() {
    document.getElementById('themeColorHex').value = this.value;
});

// Dynamic Custom Fields Builder
let customFields = [];

try {
    const existing = document.getElementById('custom_fields_json').value;
    if (existing) {
        customFields = JSON.parse(existing);
    }
} catch (e) {
    customFields = [];
}

function renderCustomFields() {
    const container = document.getElementById('customFieldsContainer');
    const notice = document.getElementById('emptyFieldsNotice');
    container.innerHTML = '';

    if (!customFields || customFields.length === 0) {
        notice.classList.remove('d-none');
        document.getElementById('custom_fields_json').value = '[]';
        return;
    }

    notice.classList.add('d-none');

    customFields.forEach((field, index) => {
        const row = document.createElement('div');
        row.className = 'p-3 rounded-3 bg-light border position-relative';
        row.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark mb-1">Field Title / Label</label>
                    <input type="text" class="form-control form-control-sm" placeholder="e.g. T-Shirt Size" value="${escapeHtml(field.label || '')}" oninput="updateField(${index}, 'label', this.value)">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Key</label>
                    <input type="text" class="form-control form-control-sm font-monospace" placeholder="tshirt_size" value="${escapeHtml(field.name || '')}" oninput="updateField(${index}, 'name', this.value)">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Type</label>
                    <select class="form-select form-select-sm" onchange="updateField(${index}, 'type', this.value)">
                        <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="number" ${field.type === 'number' ? 'selected' : ''}>Number</option>
                        <option value="email" ${field.type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="select" ${field.type === 'select' ? 'selected' : ''}>Dropdown</option>
                        <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-center justify-content-between pt-md-3">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="req_${index}" ${field.required ? 'checked' : ''} onchange="updateField(${index}, 'required', this.checked)">
                        <label class="form-check-label small" for="req_${index}">Req</label>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-light border p-1 text-muted" onclick="moveCustomField(${index}, -1)" ${index === 0 ? 'disabled' : ''} title="Move Up">
                            <i class="fa-solid fa-arrow-up" style="font-size: 11px;"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border p-1 text-muted" onclick="moveCustomField(${index}, 1)" ${index === customFields.length - 1 ? 'disabled' : ''} title="Move Down">
                            <i class="fa-solid fa-arrow-down" style="font-size: 11px;"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeCustomField(${index})" title="Remove (কমানো)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
                ${field.type === 'select' ? `
                    <div class="col-12 mt-2">
                        <label class="form-label small fw-semibold text-dark mb-1">Dropdown Options (Comma-separated)</label>
                        <input type="text" class="form-control form-control-sm" placeholder="S, M, L, XL, XXL" value="${escapeHtml(field.options || '')}" oninput="updateField(${index}, 'options', this.value)">
                    </div>
                ` : ''}
            </div>
        `;
        container.appendChild(row);
    });

    document.getElementById('custom_fields_json').value = JSON.stringify(customFields);
}

function addCustomField() {
    const key = 'field_' + (customFields.length + 1);
    customFields.push({
        label: '',
        name: key,
        type: 'text',
        options: '',
        required: false
    });
    renderCustomFields();
}

function moveCustomField(index, direction) {
    const target = index + direction;
    if (target >= 0 && target < customFields.length) {
        const temp = customFields[index];
        customFields[index] = customFields[target];
        customFields[target] = temp;
        renderCustomFields();
    }
}

function updateField(index, prop, value) {
    if (customFields[index]) {
        customFields[index][prop] = value;
        if (prop === 'label' && (!customFields[index].name || customFields[index].name.startsWith('field_'))) {
            customFields[index].name = value.toLowerCase().replace(/[^\w]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
        }
        if (prop === 'type') {
            renderCustomFields();
        } else {
            document.getElementById('custom_fields_json').value = JSON.stringify(customFields);
        }
    }
}

function removeCustomField(index) {
    customFields.splice(index, 1);
    renderCustomFields();
}

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Sync Standard Form Settings before submit
function syncStandardFormSettings() {
    const fields = {};
    document.querySelectorAll('.std-field-label').forEach(input => {
        const key = input.dataset.key;
        const toggle = document.querySelector(`.std-field-enable[data-key="${key}"]`);
        fields[key] = {
            label: input.value.trim(),
            enabled: toggle ? toggle.checked : true
        };
    });
    document.getElementById('form_settings_json').value = JSON.stringify({ fields });
}

document.getElementById('campaignForm').addEventListener('submit', function () {
    document.getElementById('custom_fields_json').value = JSON.stringify(customFields);
    syncStandardFormSettings();
});

// Initial Render
renderCustomFields();
</script>
@endsection
