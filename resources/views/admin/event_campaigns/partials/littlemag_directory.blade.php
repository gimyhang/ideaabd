{{-- Little Magazine Editors Directory Partial (লিটিলম্যাগ সম্পাদক তালিকা ও ডিরেক্টরি) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    {{-- Header Banner --}}
    <div class="p-4 bg-gradient" style="background: linear-gradient(135deg, #78350f 0%, #92400e 50%, #b45309 100%); color: #ffffff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="fw-bold mb-1 text-white">
                    লিটিলম্যাগাজিন ডিরেক্টরি
                </h4>
                <p class="text-white-50 small mb-0">
                    {{ $campaign->title }}
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'littlemag']) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                    এক্সপোর্ট
                </a>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-bold" onclick="printLittleMagTable()">
                    প্রিন্ট
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-0 border-bottom bg-light">
        <div class="col-6 col-md-4 border-end p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">মোট সম্পাদক</small>
            <div class="fs-4 fw-bold text-dark">{{ $totalLittleMagCount }} <span class="fs-6 text-muted fw-normal">জন</span></div>
        </div>
        <div class="col-6 col-md-4 border-end p-3 text-center">
            @php
                $lmDistrictsCount = $littleMagList->pluck('resolved_district')->unique()->filter(fn($d) => $d !== 'অনির্ধারিত জেলা')->count();
            @endphp
            <small class="text-muted d-block fw-semibold mb-1">জেলা সংখ্যা</small>
            <div class="fs-4 fw-bold text-success">{{ $lmDistrictsCount }} <span class="fs-6 text-muted fw-normal">টি জেলা</span></div>
        </div>
        <div class="col-12 col-md-4 p-3 text-center">
            <small class="text-muted d-block fw-semibold mb-1">সর্টিং</small>
            <div class="fs-6 fw-bold text-primary mt-1">
                বর্ণানুক্রমিক
            </div>
        </div>
    </div>

    {{-- Search & Controls --}}
    <div class="p-3 bg-white border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="input-group" style="max-width: 380px;">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="littleMagSearchInput" class="form-control bg-light border-start-0" placeholder="অনুসন্ধান..." oninput="filterLittleMagTable(this.value)">
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
                    <th style="min-width: 110px;">আইডি</th>
                    <th style="min-width: 200px;">অংশগ্রহণকারী</th>
                    <th style="min-width: 130px;">যোগাযোগ</th>
                    <th style="min-width: 140px;">ঠিকানা</th>
                    <th style="min-width: 75px;">ফি</th>
                    <th style="min-width: 85px;">পেমেন্ট</th>
                    <th style="min-width: 90px;" class="text-center">স্ট্যাটাস</th>
                    <th style="min-width: 105px;">তারিখ</th>
                    <th style="min-width: 90px;" class="text-end pe-3">অ্যাকশন</th>
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
                        {{-- আইডি --}}
                        <td>
                            <span class="badge bg-light text-primary border font-monospace px-2 py-1 fw-bold">
                                {{ $lm->registration_number }}
                            </span>
                        </td>

                        {{-- অংশগ্রহণকারী --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($lmPhoto)
                                    <img src="{{ asset('storage/' . $lmPhoto) }}" alt="{{ $lm->name }}" class="rounded-circle border" style="width: 34px; height: 34px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-warning bg-opacity-15 text-warning-emphasis d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 13px;">
                                        {{ mb_substr($lm->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $lm->name }}</div>
                                    <div class="d-flex align-items-center gap-1 mt-0.5">
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0 px-1">{{ $magName }}</span>
                                        @if($issueCount !== '-')
                                            <span class="badge bg-light text-muted border py-0 px-1 font-monospace" style="font-size: 10px;">{{ $issueCount }} সংখ্যা</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- যোগাযোগ --}}
                        <td>
                            <div class="d-flex align-items-center gap-1.5">
                                <a href="tel:{{ $lm->phone }}" class="text-decoration-none fw-semibold text-dark font-monospace small">
                                    {{ $lm->phone }}
                                </a>
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $lm->phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '88' . $cleanPhone;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-success small" title="হোয়াটসঅ্যাপ">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>
                            @if($lm->email)
                                <small class="text-muted d-block text-truncate" style="font-size: 11px; max-width: 140px;" title="{{ $lm->email }}">
                                    {{ $lm->email }}
                                </small>
                            @endif
                        </td>

                        {{-- ঠিকানা --}}
                        <td>
                            <div class="fw-semibold text-dark small">{{ $dist }}</div>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $thana !== '-' ? $thana : $div }}</small>
                        </td>

                        {{-- ফি --}}
                        <td>
                            @if($campaign->has_fee_or_donation)
                                <div class="fw-bold text-dark">৳{{ number_format($lm->amount_paid) }}</div>
                            @else
                                <span class="badge bg-light text-muted border">বিনামূল্যে</span>
                            @endif
                        </td>

                        {{-- পেমেন্ট --}}
                        <td>
                            @if($campaign->has_fee_or_donation)
                                <span class="badge {{ $lm->payment_status === 'verified' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-0.5" style="font-size: 10.5px;">
                                    {{ $lm->payment_status === 'verified' ? 'যাচাইকৃত' : ($lm->payment_status === 'free' ? 'বিনামূল্যে' : 'পেন্ডিং') }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted">-</span>
                            @endif
                        </td>

                        {{-- স্ট্যাটাস --}}
                        <td class="text-center">
                            @if($lm->status === 'confirmed' || $lm->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                    অনুমোদিত
                                </span>
                            @elseif($lm->status === 'selected')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                    নির্বাচিত
                                </span>
                            @elseif($lm->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                    পেন্ডিং
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 10.5px;">
                                    {{ ucfirst($lm->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- তারিখ --}}
                        <td>
                            <span class="text-muted small">{{ $lm->created_at->format('d M, Y') }}</span>
                        </td>

                        {{-- অ্যাকশন --}}
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.event-campaigns.registrations.print', $lm->id) }}" target="_blank" class="btn btn-outline-dark btn-xs rounded-pill px-2 py-0.5" title="প্রিন্ট">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <button type="button" class="btn btn-outline-primary btn-xs rounded-pill px-2 py-0.5 ms-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $lm->id }}" title="বিস্তারিত">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
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
