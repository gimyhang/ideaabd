{{-- Division & District Wise Database Partial (বিভাগ ও জেলা ভিত্তিক ডাটাবেজ) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    {{-- Header Banner --}}
    <div class="p-4 bg-gradient rounded-top-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%); color: #ffffff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="fw-bold mb-1 text-white">
                    বিভাগ ও জেলা ডাটাবেজ
                </h4>
                <p class="text-white-50 small mb-0">
                    {{ $campaign->title }}
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        এক্সপোর্ট
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 13px;">
                        <li><a class="dropdown-item py-2 fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'format' => 'excel']) }}">সকল বিভাগের এক্সেল (.xls)</a></li>
                        <li><a class="dropdown-item py-2 fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'format' => 'csv']) }}">সকল বিভাগের সিএসভি (.csv)</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header text-muted py-1 small fw-bold">নির্দিষ্ট বিভাগ:</li>
                        @foreach($geoDatabase as $divName => $districts)
                            <li>
                                <a class="dropdown-item py-1.5 small fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'division' => ($divName !== 'অনির্ধারিত বিভাগ' ? $divName : ''), 'format' => 'excel']) }}">
                                    {{ $divName }} ({{ $divisionStats[$divName] ?? 0 }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-bold" onclick="expandAllGeoAccordions()">
                    সব খুলুন
                </button>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-bold" onclick="collapseAllGeoAccordions()">
                    সব বন্ধ
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1.5 fw-bold shadow-sm" onclick="window.print()">
                    প্রিন্ট
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-0 border-bottom bg-light">
        <div class="col-6 col-md-3 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">অংশগ্রহণকারী</small>
            <div class="fs-4 fw-bold text-dark">{{ $totalRegistrations }} <span class="fs-6 text-muted fw-normal">জন</span></div>
        </div>
        <div class="col-6 col-md-3 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">বিভাগ</small>
            <div class="fs-4 fw-bold text-primary">{{ $totalGeoDivisions }} <span class="fs-6 text-muted fw-normal">টি</span></div>
        </div>
        <div class="col-6 col-md-3 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">জেলা</small>
            <div class="fs-4 fw-bold text-success">{{ $totalGeoDistricts }} <span class="fs-6 text-muted fw-normal">টি</span></div>
        </div>
        <div class="col-6 col-md-3 p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">লিটিলম্যাগ</small>
            <div class="fs-4 fw-bold text-warning">{{ $totalLittleMagCount }} <span class="fs-6 text-muted fw-normal">জন</span></div>
        </div>
    </div>

    {{-- Quick Division Selector Pills --}}
    <div class="p-3 bg-white border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span class="small fw-bold text-dark">
                বিভাগ নির্বাচন:
            </span>
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="geoLiveSearchInput" class="form-control bg-light border-start-0" placeholder="ডাটাবেজে খুঁজুন..." oninput="filterGeoLiveSearch(this.value)">
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2" id="geoDivisionPillsWrap">
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 fw-semibold active geo-nav-pill" onclick="filterGeoDivisionView('all', this)">
                সকল বিভাগ <span class="badge bg-white text-dark ms-1">{{ $totalRegistrations }}</span>
            </button>
            @foreach($geoDatabase as $divName => $districts)
                @php
                    $dCount = $divisionStats[$divName] ?? 0;
                @endphp
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold geo-nav-pill" data-div-target="{{ Str::slug($divName) }}" onclick="filterGeoDivisionView('{{ Str::slug($divName) }}', this)">
                    {{ $divName }} <span class="badge bg-secondary-subtle text-dark border ms-1">{{ $dCount }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main Geo Content (Divisions & Districts) --}}
    <div class="p-3 p-md-4 bg-light" id="geoMainContainer">
        @forelse($geoDatabase as $divName => $districts)
            @php
                $divSlug = Str::slug($divName);
                $divTotal = $divisionStats[$divName] ?? 0;
            @endphp
            <div class="geo-division-card card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden" id="geo-sec-{{ $divSlug }}" data-div-slug="{{ $divSlug }}">
                {{-- Division Header --}}
                <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="cursor: pointer;" onclick="toggleGeoAccordion('{{ $divSlug }}')">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary fw-bold" style="width: 36px; height: 36px; font-size: 16px;">
                            <i class="fa-solid fa-map"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <span>{{ $divName }} {{ $divName !== 'অনির্ধারিত বিভাগ' ? 'বিভাগ' : '' }}</span>
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 11px;">
                                    {{ $divTotal }} জন
                                </span>
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    {{ count($districts) }} টি জেলা
                                </span>
                            </h5>
                            <small class="text-muted">বর্ণানুক্রমে জেলা ও অংশগ্রহণকারী তালিকা</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                            <a href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'division' => ($divName !== 'অনির্ধারিত বিভাগ' ? $divName : ''), 'format' => 'excel']) }}" class="btn btn-sm btn-success text-white rounded-start-pill px-3 py-1 fw-bold shadow-2xs" title="{{ $divName }} বিভাগের জেলা ও নাম ভিত্তিক এক্সেল শিট (.xls) ডাউনলোড করুন">
                                <i class="fa-solid fa-file-excel me-1"></i> এক্সেল ডাউনলোড
                            </a>
                            <button type="button" class="btn btn-sm btn-success text-white dropdown-toggle dropdown-toggle-split rounded-end-pill px-2" data-bs-toggle="dropdown" aria-expanded="false" title="অন্যান্য ফরম্যাট">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 12px;">
                                <li>
                                    <a class="dropdown-item py-1.5 fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'division' => ($divName !== 'অনির্ধারিত বিভাগ' ? $divName : ''), 'format' => 'excel']) }}">
                                        <i class="fa-solid fa-file-excel text-success me-2"></i> এক্সেল শিট (.xls)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-1.5 fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'division' => ($divName !== 'অনির্ধারিত বিভাগ' ? $divName : ''), 'format' => 'csv']) }}">
                                        <i class="fa-solid fa-file-csv text-primary me-2"></i> সিএসভি ফাইল (.csv)
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1" onclick="event.stopPropagation(); printSingleSection('geo-sec-{{ $divSlug }}');" title="এই বিভাগ প্রিন্ট করুন">
                            <i class="fa-solid fa-print me-1"></i> প্রিন্ট
                        </button>
                        <i class="fa-solid fa-chevron-down text-muted transition-transform ms-1" id="chevron-{{ $divSlug }}"></i>
                    </div>
                </div>

                {{-- Division Body: Districts & Participant Tables --}}
                <div class="card-body p-3 p-md-4 geo-accordion-body" id="body-{{ $divSlug }}">
                    @foreach($districts as $distName => $participants)
                        @php
                            $distSlug = Str::slug($divSlug . '-' . $distName);
                            $distCount = $participants->count();
                        @endphp
                        <div class="geo-district-box border rounded-3 p-3 mb-3 bg-white shadow-2xs" id="dist-{{ $distSlug }}" data-district-name="{{ $distName }}">
                            {{-- District Subheader --}}
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-2 mb-3 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-teal-50 text-teal-800 border border-teal-200 px-3 py-1.5 rounded-pill fw-bold fs-6" style="background-color: #f0fdf4; color: #166534; border-color: #bbf7d0;">
                                        <i class="fa-solid fa-location-dot me-1 text-success"></i> {{ $distName }} {{ $distName !== 'অনির্ধারিত জেলা' ? 'জেলা' : '' }}
                                    </span>
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-bold">
                                        {{ $distCount }} জন অংশগ্রহণকারী
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-1.5">
                                    <a href="{{ route('admin.event-campaigns.show', ['campaign' => $campaign->id, 'district' => ($distName !== 'অনির্ধারিত জেলা' ? $distName : ''), 'sort' => 'alpha']) }}" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 small" title="মূল টেবিলে দেখুন">
                                        <i class="fa-solid fa-table me-1"></i> টেবিলে ফিল্টার
                                    </a>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 small" onclick="printSingleSection('dist-{{ $distSlug }}');" title="জেলা তালিকা প্রিন্ট">
                                        <i class="fa-solid fa-print"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Participants Table for this District (Sorted Alphabetically by Name) --}}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th style="min-width: 110px;">আইডি</th>
                                            <th style="min-width: 170px;">অংশগ্রহণকারী</th>
                                            <th style="min-width: 130px;">যোগাযোগ</th>
                                            <th style="min-width: 140px;">ঠিকানা</th>
                                            <th style="min-width: 75px;">ফি</th>
                                            <th style="min-width: 85px;">পেমেন্ট</th>
                                            <th style="min-width: 90px;" class="text-center">স্ট্যাটাস</th>
                                            <th style="min-width: 105px;">তারিখ</th>
                                            <th style="width: 90px;" class="text-end">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($participants as $p)
                                            @php
                                                $pPhoto = $p->form_data['student_photo'] ?? $p->form_data['photo'] ?? $p->form_data['author_photo'] ?? null;
                                                $pAuthorCat = $p->form_data['author_category'] ?? null;
                                                if (!$pAuthorCat && !empty($p->form_data['author_categories'])) {
                                                    $pAuthorCat = is_array($p->form_data['author_categories']) ? implode(', ', $p->form_data['author_categories']) : $p->form_data['author_categories'];
                                                }
                                                $pMag = $p->magazine_name;
                                            @endphp
                                            <tr class="geo-participant-row" data-search-text="{{ strtolower($p->name . ' ' . $p->phone . ' ' . $p->registration_number . ' ' . $p->resolved_thana . ' ' . $pMag . ' ' . $distName) }}">
                                                {{-- আইডি --}}
                                                <td>
                                                    <span class="badge bg-light text-primary border font-monospace px-2 py-1 fw-bold">
                                                        {{ $p->registration_number }}
                                                    </span>
                                                </td>

                                                {{-- অংশগ্রহণকারী --}}
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($pPhoto)
                                                            <img src="{{ asset('storage/' . $pPhoto) }}" alt="{{ $p->name }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 12px;">
                                                                {{ mb_substr($p->name, 0, 1) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $p->name }}</div>
                                                            @if($pMag)
                                                                <small class="badge bg-warning-subtle text-dark border border-warning-subtle py-0 px-1">{{ $pMag }}</small>
                                                            @elseif($pAuthorCat)
                                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $pAuthorCat }}</small>
                                                            @elseif($p->institution_or_org)
                                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $p->institution_or_org }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- যোগাযোগ --}}
                                                <td>
                                                    <a href="tel:{{ $p->phone }}" class="text-decoration-none fw-semibold text-dark font-monospace d-block small">
                                                        {{ $p->phone }}
                                                    </a>
                                                    @if($p->email)
                                                        <small class="text-muted d-block text-truncate" style="font-size: 11px; max-width: 140px;" title="{{ $p->email }}">
                                                            {{ $p->email }}
                                                        </small>
                                                    @endif
                                                </td>

                                                {{-- ঠিকানা --}}
                                                <td>
                                                    <div class="text-dark small">{{ $p->resolved_thana ?: 'অনির্ধারিত থানা' }}</div>
                                                    @if(!empty($p->form_data['perm_village']))
                                                        <small class="text-muted d-block" style="font-size: 11px;">{{ $p->form_data['perm_village'] }}</small>
                                                    @elseif(!empty($p->address))
                                                        <small class="text-muted d-block text-truncate" style="font-size: 11px; max-width: 130px;" title="{{ $p->address }}">{{ $p->address }}</small>
                                                    @endif
                                                </td>

                                                {{-- ফি --}}
                                                <td>
                                                    @if($campaign->has_fee_or_donation)
                                                        <div class="fw-bold text-dark">৳{{ number_format($p->amount_paid) }}</div>
                                                    @else
                                                        <span class="badge bg-light text-muted border">বিনামূল্যে</span>
                                                    @endif
                                                </td>

                                                {{-- পেমেন্ট --}}
                                                <td>
                                                    @if($campaign->has_fee_or_donation)
                                                        <span class="badge {{ $p->payment_status === 'verified' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-0.5" style="font-size: 10.5px;">
                                                            {{ $p->payment_status === 'verified' ? 'যাচাইকৃত' : ($p->payment_status === 'free' ? 'বিনামূল্যে' : 'পেন্ডিং') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted">-</span>
                                                    @endif
                                                </td>

                                                {{-- স্ট্যাটাস --}}
                                                <td class="text-center">
                                                    @if($p->status === 'confirmed' || $p->status === 'approved')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                                            অনুমোদিত
                                                        </span>
                                                    @elseif($p->status === 'selected')
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                                            নির্বাচিত
                                                        </span>
                                                    @elseif($p->status === 'pending')
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                                            পেন্ডিং
                                                        </span>
                                                    @elseif($p->status === 'attended')
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                                            উপস্থিত
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 10.5px;">
                                                            {{ ucfirst($p->status) }}
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- তারিখ --}}
                                                <td>
                                                    <span class="text-muted small">{{ $p->created_at->format('d M, Y') }}</span>
                                                </td>

                                                {{-- অ্যাকশন --}}
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.event-campaigns.registrations.print', $p->id) }}" target="_blank" class="btn btn-outline-dark btn-xs rounded-pill px-2 py-0.5" title="প্রিন্ট">
                                                            <i class="fa-solid fa-print"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-primary btn-xs rounded-pill px-2 py-0.5 ms-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id }}" title="বিস্তারিত দেখুন">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded-4 border">
                <i class="fa-solid fa-map-location-dot fs-1 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">কোনো বিভাগভিত্তিক ডেটা পাওয়া যায়নি</h5>
                <p class="text-muted small">অংশগ্রহণকারীরা নিবন্ধন করলে তাদের জেলা ও বিভাগ অনুযায়ী স্বয়ংক্রিয়ভাবে সাজানো হবে।</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    function toggleGeoAccordion(slug) {
        const body = document.getElementById('body-' + slug);
        const icon = document.getElementById('chevron-' + slug);
        if (!body) return;
        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            body.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(-90deg)';
        }
    }

    function expandAllGeoAccordions() {
        document.querySelectorAll('.geo-accordion-body').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.geo-division-card .fa-chevron-down').forEach(el => el.style.transform = 'rotate(0deg)');
    }

    function collapseAllGeoAccordions() {
        document.querySelectorAll('.geo-accordion-body').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.geo-division-card .fa-chevron-down').forEach(el => el.style.transform = 'rotate(-90deg)');
    }

    function filterGeoDivisionView(slug, btnElem) {
        document.querySelectorAll('.geo-nav-pill').forEach(btn => {
            btn.classList.remove('active', 'btn-dark');
            btn.classList.add('btn-outline-secondary');
        });

        if (btnElem) {
            btnElem.classList.add('active', 'btn-dark');
            btnElem.classList.remove('btn-outline-secondary');
        }

        const cards = document.querySelectorAll('.geo-division-card');
        if (slug === 'all') {
            cards.forEach(c => c.style.display = 'block');
        } else {
            cards.forEach(c => {
                if (c.getAttribute('data-div-slug') === slug) {
                    c.style.display = 'block';
                    const body = c.querySelector('.geo-accordion-body');
                    if (body) body.style.display = 'block';
                    c.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    c.style.display = 'none';
                }
            });
        }
    }

    function filterGeoLiveSearch(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.geo-participant-row');
        const distBoxes = document.querySelectorAll('.geo-district-box');
        const divCards = document.querySelectorAll('.geo-division-card');

        if (!q) {
            rows.forEach(r => r.style.display = '');
            distBoxes.forEach(b => b.style.display = '');
            divCards.forEach(c => c.style.display = '');
            return;
        }

        expandAllGeoAccordions();

        rows.forEach(r => {
            const text = r.getAttribute('data-search-text') || '';
            if (text.includes(q)) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });

        distBoxes.forEach(box => {
            const visibleRows = box.querySelectorAll('tbody tr.geo-participant-row:not([style*="display: none"])');
            if (visibleRows.length > 0) {
                box.style.display = '';
            } else {
                box.style.display = 'none';
            }
        });

        divCards.forEach(card => {
            const visibleDistBoxes = card.querySelectorAll('.geo-district-box:not([style*="display: none"])');
            if (visibleDistBoxes.length > 0) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function printSingleSection(elemId) {
        const elem = document.getElementById(elemId);
        if (!elem) return;
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>body{font-family: Arial, sans-serif; padding: 20px;} .btn, .btn-group{display:none !important;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(elem.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
