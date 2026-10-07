{{-- Little Magazine Editors Directory Partial (লিটিলম্যাগ সম্পাদক তালিকা ও ডিরেক্টরি) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    {{-- Header Banner --}}
    <div class="p-4 bg-gradient" style="background: linear-gradient(135deg, #78350f 0%, #92400e 50%, #b45309 100%); color: #ffffff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold" style="font-size: 11px;">
                        <i class="fa-solid fa-feather-pointed me-1"></i> লিটিলম্যাগ ও সাহিত্য পত্রিকা বিশেষ তালিকা
                    </span>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1" style="font-size: 11px;">
                        <i class="fa-solid fa-arrow-down-a-z me-1"></i> বর্ণানুক্রমে সাজানো
                    </span>
                </div>
                <h4 class="fw-bold mb-1 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-newspaper text-warning"></i>
                    লিটিলম্যাগাজিন সম্পাদক তালিকা ও ডিরেক্টরি
                </h4>
                <p class="text-white-50 small mb-0">
                    {{ $campaign->title }} — ৩য় লিটিলম্যাগ মেলায় অংশগ্রহণকারী সকল সম্পাদক ও তাদের পত্রিকার বিস্তারিত ডাটাবেজ
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'littlemag']) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> এক্সপোর্ট লিটিলম্যাগ এক্সেল (CSV)
                </a>
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-bold shadow-sm" onclick="printLittleMagTable()">
                    <i class="fa-solid fa-print me-1"></i> প্রিন্ট ডিরেক্টরি
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-0 border-bottom bg-light">
        <div class="col-6 col-md-4 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">মোট লিটিলম্যাগ সম্পাদক</small>
            <div class="fs-4 fw-bold text-dark">{{ $totalLittleMagCount }} <span class="fs-6 text-muted fw-normal">জন</span></div>
        </div>
        <div class="col-6 col-md-4 border-end p-3 text-center">
            @php
                $lmDistrictsCount = $littleMagList->pluck('resolved_district')->unique()->filter(fn($d) => $d !== 'অনির্ধারিত জেলা')->count();
            @endphp
            <small class="text-muted d-block fw-semibold mb-1">অংশগ্রহণকারী জেলা সংখ্যা</small>
            <div class="fs-4 fw-bold text-success">{{ $lmDistrictsCount }} <span class="fs-6 text-muted fw-normal">টি জেলা</span></div>
        </div>
        <div class="col-12 col-md-4 p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">তালিকা সর্টিং ও বিন্যাস</small>
            <div class="fs-6 fw-bold text-primary mt-1">
                <i class="fa-solid fa-arrow-down-a-z me-1"></i> পত্রিকার নামানুসারে বর্ণানুক্রমিক
            </div>
        </div>
    </div>

    {{-- Search & Controls --}}
    <div class="p-3 bg-white border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="input-group" style="max-width: 380px;">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="littleMagSearchInput" class="form-control bg-light border-start-0" placeholder="পত্রিকার নাম, সম্পাদক, জেলা দিয়ে খুঁজুন..." oninput="filterLittleMagTable(this.value)">
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border font-monospace px-2.5 py-1.5" id="littleMagCountBadge">
                    মোট {{ $totalLittleMagCount }} টি রেকর্ড
                </span>
            </div>
        </div>
    </div>

    {{-- Little Mag Table Content --}}
    <div class="table-responsive" id="littleMagPrintableWrap">
        <table class="table table-hover align-middle mb-0" id="littleMagTable" style="font-size: 13.5px;">
            <thead class="table-light text-muted">
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th style="min-width: 190px;">পত্রিকার নাম (Little Magazine)</th>
                    <th style="min-width: 180px;">সম্পাদকের নাম</th>
                    <th style="min-width: 120px;" class="text-center">প্রকাশিত সংখ্যা</th>
                    <th style="min-width: 150px;">বিভাগ ও জেলা</th>
                    <th style="min-width: 160px;">উপজেলা ও ঠিকানা</th>
                    <th style="min-width: 160px;">মোবাইল ও যোগাযোগ</th>
                    <th style="min-width: 120px;">রেজিস্ট্রেশন নং</th>
                    <th style="min-width: 90px;" class="text-center">স্ট্যাটাস</th>
                    <th style="min-width: 110px;" class="text-end pe-3">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($littleMagList as $lm)
                    @php
                        $lmPhoto = $lm->form_data['student_photo'] ?? $lm->form_data['photo'] ?? $lm->form_data['author_photo'] ?? null;
                        $magName = $lm->magazine_name ?: 'পত্রিকার নাম উল্লেখ নেই';
                        $issueCount = $lm->magazine_issue_count ?: '-';
                        $div = $lm->resolved_division;
                        $dist = $lm->resolved_district;
                        $thana = $lm->resolved_thana ?: '-';
                        $village = $lm->form_data['perm_village'] ?? ($lm->address ?: '');
                    @endphp
                    <tr class="littlemag-row" data-search-text="{{ strtolower($magName . ' ' . $lm->name . ' ' . $lm->phone . ' ' . $dist . ' ' . $div . ' ' . $lm->registration_number . ' ' . $thana) }}">
                        <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-amber-50 text-amber-900 border border-amber-200" style="width: 36px; height: 36px; background-color: #fef3c7; color: #78350f; border-color: #fde68a;">
                                    <i class="fa-solid fa-newspaper fs-6"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $magName }}</div>
                                    <small class="text-muted d-block" style="font-size: 11px;">লিটিলম্যাগাজিন</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($lmPhoto)
                                    <img src="{{ asset('storage/' . $lmPhoto) }}" alt="{{ $lm->name }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-warning bg-opacity-15 text-warning-emphasis d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                        {{ mb_substr($lm->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $lm->name }}</div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1.5 py-0.5" style="font-size: 10px;">
                                        সম্পাদক
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($issueCount !== '-')
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 11px;">
                                    {{ $issueCount }}
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $dist }}</div>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $div }}</small>
                        </td>
                        <td>
                            <div class="text-dark small">{{ $thana }}</div>
                            @if($village)
                                <small class="text-muted d-block" style="font-size: 11px;">{{ Str::limit($village, 26) }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1.5">
                                <a href="tel:{{ $lm->phone }}" class="text-decoration-none fw-semibold text-dark font-monospace small">
                                    <i class="fa-solid fa-phone text-success me-1"></i> {{ $lm->phone }}
                                </a>
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $lm->phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '88' . $cleanPhone;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-success small" title="WhatsApp Message">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>
                            @if($lm->email)
                                <a href="mailto:{{ $lm->email }}" class="text-muted text-decoration-none d-block text-truncate" style="font-size: 11px; max-width: 150px;">
                                    {{ $lm->email }}
                                </a>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                                {{ $lm->registration_number }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($lm->status === 'confirmed' || $lm->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 10.5px;">
                                    Approved
                                </span>
                            @elseif($lm->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 10.5px;">
                                    Pending
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 10.5px;">
                                    {{ ucfirst($lm->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.event-campaigns.registrations.print', $lm->id) }}" target="_blank" class="btn btn-outline-dark btn-xs rounded-pill px-2.5 py-1" title="আমন্ত্রণ কার্ড / রসিদ প্রিন্ট">
                                    <i class="fa-solid fa-id-card me-1"></i> কার্ড
                                </a>
                                <button type="button" class="btn btn-outline-primary btn-xs rounded-pill px-2 py-1 ms-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $lm->id }}" title="সম্পূর্ণ তথ্য দেখুন">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-newspaper fs-1 text-secondary mb-2 d-block"></i>
                            <div class="fw-bold fs-6">কোনো লিটিলম্যাগ সম্পাদক পাওয়া যায়নি</div>
                            <small>অংশগ্রহণকারী নিবন্ধন ফরমে লিটিলম্যাগ ক্যাটাগরি বা পত্রিকার নাম পূরণ করলে স্বয়ংক্রিয়ভাবে এই তালিকায় যুক্ত হবে।</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function filterLittleMagTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.littlemag-row');
        let matchCount = 0;

        rows.forEach(r => {
            const text = r.getAttribute('data-search-text') || '';
            if (!q || text.includes(q)) {
                r.style.display = '';
                matchCount++;
            } else {
                r.style.display = 'none';
            }
        });

        const badge = document.getElementById('littleMagCountBadge');
        if (badge) {
            badge.innerText = q ? `${matchCount} টি ফলাফল` : `মোট {{ $totalLittleMagCount }} টি রেকর্ড`;
        }
    }

    function printLittleMagTable() {
        const elem = document.getElementById('littleMagPrintableWrap');
        if (!elem) return;
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>লিটিলম্যাগ সম্পাদক তালিকা — {{ $campaign->title }}</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>body{font-family: Arial, sans-serif; padding: 20px;} .btn, .btn-group{display:none !important;} h3{margin-bottom: 16px;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="text-center mb-4"><h3>{{ $campaign->title }}</h3><h4>লিটিলম্যাগাজিন সম্পাদক তালিকা ও ডিরেক্টরি</h4></div>');
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
