{{-- Division & District Wise Database Partial (বিভাগ ও জেলা ভিত্তিক ডাটাবেজ) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    {{-- Header Banner --}}
    <div class="p-4 bg-gradient" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%); color: #ffffff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-teal-500 text-white rounded-pill px-3 py-1 fw-bold" style="background-color: #0d9488; font-size: 11px;">
                        <i class="fa-solid fa-map-location-dot me-1"></i> ভৌগোলিক বিন্যাস ও ডাটাবেজ
                    </span>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1" style="font-size: 11px;">
                        <i class="fa-solid fa-arrow-down-a-z me-1"></i> বর্ণানুক্রমে সাজানো (A-Z / ক-হ)
                    </span>
                </div>
                <h4 class="fw-bold mb-1 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-earth-asia text-teal-300" style="color: #5eead4;"></i>
                    বিভাগ ও জেলা ভিত্তিক অংশগ্রহণকারী ডাটাবেজ
                </h4>
                <p class="text-white-50 small mb-0">
                    {{ $campaign->title }} — সকল অংশগ্রহণকারীর বিভাগ ও জেলা অনুযায়ী বর্ণানুক্রমিক তালিকা
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-excel me-1"></i> বিভাগ ও জেলা ভিত্তিক এক্সেল ডাউনলোড
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 13px;">
                        <li><a class="dropdown-item py-2 fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'format' => 'excel']) }}"><i class="fa-solid fa-file-excel text-success me-2"></i> সকল বিভাগের পূর্ণাঙ্গ এক্সেল শিট (.xls)</a></li>
                        <li><a class="dropdown-item py-2 fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'format' => 'csv']) }}"><i class="fa-solid fa-file-csv text-primary me-2"></i> সকল বিভাগের সিএসভি ফাইল (.csv)</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header text-muted py-1 small fw-bold"><i class="fa-solid fa-map me-1"></i> নির্দিষ্ট বিভাগ ডাউনলোড:</li>
                        @foreach($geoDatabase as $divName => $districts)
                            <li>
                                <a class="dropdown-item py-1.5 small fw-semibold" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'division' => ($divName !== 'অনির্ধারিত বিভাগ' ? $divName : ''), 'format' => 'excel']) }}">
                                    <i class="fa-solid fa-angle-right text-muted me-1.5"></i> {{ $divName }} ({{ $divisionStats[$divName] ?? 0 }} জন)
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-bold shadow-sm" onclick="expandAllGeoAccordions()">
                    <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> সব বিভাগ খুলুন
                </button>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-bold" onclick="collapseAllGeoAccordions()">
                    <i class="fa-solid fa-down-left-and-up-right-to-center me-1"></i> সব বন্ধ করুন
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1.5 fw-bold shadow-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> সম্পূর্ণ ডাটাবেজ প্রিন্ট
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-0 border-bottom bg-light">
        <div class="col-6 col-md-3 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">মোট অংশগ্রহণকারী</small>
            <div class="fs-4 fw-bold text-dark">{{ $totalRegistrations }} <span class="fs-6 text-muted fw-normal">জন</span></div>
        </div>
        <div class="col-6 col-md-3 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">অংশগ্রহণকারী বিভাগ</small>
            <div class="fs-4 fw-bold text-primary">{{ $totalGeoDivisions }} <span class="fs-6 text-muted fw-normal">টি</span></div>
        </div>
        <div class="col-6 col-md-3 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">অংশগ্রহণকারী জেলা</small>
            <div class="fs-4 fw-bold text-success">{{ $totalGeoDistricts }} <span class="fs-6 text-muted fw-normal">টি</span></div>
        </div>
        <div class="col-6 col-md-3 p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">লিটিলম্যাগ সম্পাদক</small>
            <div class="fs-4 fw-bold text-warning">{{ $totalLittleMagCount }} <span class="fs-6 text-muted fw-normal">জন</span></div>
        </div>
    </div>

    {{-- Quick Division Selector Pills --}}
    <div class="p-3 bg-white border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-filter text-primary"></i> দ্রুত বিভাগ নির্বাচন (বর্ণানুক্রমে):
            </span>
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="geoLiveSearchInput" class="form-control bg-light border-start-0" placeholder="ডাটাবেজে খুঁজুন (নাম/জেলা/থানা)..." oninput="filterGeoLiveSearch(this.value)">
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
                                            <th style="width: 45px;" class="text-center">#</th>
                                            <th style="width: 50px;">ছবি</th>
                                            <th style="min-width: 170px;">নাম (বর্ণানুক্রমে)</th>
                                            <th style="min-width: 150px;">ক্যাটাগরি / সাহিত্য শাখা / পত্রিকা</th>
                                            <th style="min-width: 150px;">থানা ও এলাকা</th>
                                            <th style="min-width: 140px;">যোগাযোগ (মোবাইল ও ইমেইল)</th>
                                            <th style="min-width: 110px;">রেজিস্ট্রেশন নং</th>
                                            <th style="min-width: 90px;" class="text-center">স্ট্যাটাস</th>
                                            <th style="width: 100px;" class="text-end">অ্যাকশন</th>
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
                                                $isLM = $p->isLittleMagEditor();
                                            @endphp
                                            <tr class="geo-participant-row" data-search-text="{{ strtolower($p->name . ' ' . $p->phone . ' ' . $p->registration_number . ' ' . $p->resolved_thana . ' ' . $pMag . ' ' . $distName) }}">
                                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    @if($pPhoto)
                                                        <img src="{{ asset('storage/' . $pPhoto) }}" alt="{{ $p->name }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                                            {{ mb_substr($p->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $p->name }}</div>
                                                    @if($p->institution_or_org)
                                                        <small class="text-muted d-block" style="font-size: 11px;">{{ $p->institution_or_org }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pMag)
                                                        <div class="d-inline-flex align-items-center gap-1 mb-1">
                                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-bold" style="font-size: 11px;">
                                                                <i class="fa-solid fa-newspaper text-warning me-1"></i> {{ $pMag }}
                                                            </span>
                                                            @if($p->magazine_issue_count)
                                                                <span class="badge bg-light text-muted border" style="font-size: 10px;">{{ $p->magazine_issue_count }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($pAuthorCat)
                                                        <div class="small text-secondary" style="font-size: 11.5px;">{{ $pAuthorCat }}</div>
                                                    @elseif($p->designation_or_class)
                                                        <div class="small text-secondary" style="font-size: 11.5px;">{{ $p->designation_or_class }}</div>
                                                    @elseif(!$pMag)
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="text-dark small">{{ $p->resolved_thana ?: 'অনির্ধারিত থানা' }}</div>
                                                    @if(!empty($p->form_data['perm_village']))
                                                        <small class="text-muted d-block" style="font-size: 11px;">{{ $p->form_data['perm_village'] }}</small>
                                                    @elseif(!empty($p->address))
                                                        <small class="text-muted d-block" style="font-size: 11px;">{{ Str::limit($p->address, 30) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="tel:{{ $p->phone }}" class="text-decoration-none fw-semibold text-dark font-monospace d-block small">
                                                        <i class="fa-solid fa-phone text-success me-1"></i> {{ $p->phone }}
                                                    </a>
                                                    @if($p->email)
                                                        <a href="mailto:{{ $p->email }}" class="text-muted text-decoration-none d-block text-truncate" style="font-size: 11px; max-width: 140px;">
                                                            {{ $p->email }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                                                        {{ $p->registration_number }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($p->status === 'confirmed' || $p->status === 'approved')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 10.5px;">
                                                            Approved
                                                        </span>
                                                    @elseif($p->status === 'pending')
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 10.5px;">
                                                            Pending
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 10.5px;">
                                                            {{ ucfirst($p->status) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.event-campaigns.registrations.print', $p->id) }}" target="_blank" class="btn btn-outline-dark btn-xs rounded-pill px-2 py-0.5" title="আমন্ত্রণ কার্ড / রসিদ প্রিন্ট">
                                                            <i class="fa-solid fa-id-card"></i>
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
