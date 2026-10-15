{{-- Little Magazine Editors Directory Partial (লিটিলম্যাগ সম্পাদক তালিকা ও ডিরেক্টরি) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    {{-- Header Banner --}}
    <div class="p-4 bg-gradient" style="background: linear-gradient(135deg, #78350f 0%, #92400e 50%, #b45309 100%); color: #ffffff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="fw-bold mb-1 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-newspaper text-warning"></i> লিটিলম্যাগাজিন ডিরেক্টরি ও সম্পাদক তালিকা
                </h4>
                <p class="text-white-50 small mb-0 font-monospace">
                    {{ $campaign->title }} &bull; মোট নিবন্ধিত লিটিলম্যাগ সম্পাদক: {{ $totalLittleMagCount }} জন
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.event-campaigns.print-all', ['campaign' => $campaign->id, 'type' => 'littlemag']) }}" target="_blank" class="btn btn-sm btn-light text-dark rounded-pill px-3 py-1.5 fw-bold shadow-sm" title="এক ক্লিকে সকল লিটিলম্যাগ সম্পাদকের পূর্ণ তালিকা প্রিন্ট করুন">
                    <i class="fa-solid fa-print me-1 text-danger"></i> সকল সম্পাদক প্রিন্ট (Print All)
                </a>
                <a href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'littlemag']) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> এক্সপোর্ট (CSV)
                </a>
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
            <small class="text-muted d-block fw-semibold mb-1">সর্টিং ক্রম</small>
            <div class="fs-6 fw-bold text-primary mt-1">
                <i class="fa-solid fa-arrow-down-a-z me-1"></i> পত্রিকার নামানুসারে (A-Z)
            </div>
        </div>
    </div>

    {{-- Search & Controls --}}
    <div class="p-3 bg-white border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="input-group" style="max-width: 420px;">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="littleMagSearchInput" class="form-control bg-light border-start-0" placeholder="নাম, পত্রিকা, ফোন, জেলা, রেজি নং..." oninput="filterLittleMagTable(this.value)">
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border font-monospace px-3 py-2" id="littleMagCountBadge">
                    মোট {{ $totalLittleMagCount }} টি রেকর্ড প্রদর্শিত
                </span>
            </div>
        </div>
    </div>

    {{-- Little Mag Table Content --}}
    <div class="table-responsive" id="littleMagPrintableWrap">
        <table class="table aec-table align-middle mb-0" id="littleMagTable">
            <thead>
                <tr>
                    <th style="min-width: 100px;">আইডি</th>
                    <th style="min-width: 200px;">অংশগ্রহণকারী</th>
                    <th style="min-width: 130px;">যোগাযোগ</th>
                    <th style="min-width: 140px;">ঠিকানা</th>
                    <th style="min-width: 75px;">ফি</th>
                    <th style="min-width: 85px;">পেমেন্ট</th>
                    <th style="min-width: 90px;" class="text-center">স্ট্যাটাস</th>
                    <th style="min-width: 105px;">তারিখ</th>
                    <th style="min-width: 190px;" class="text-end pe-3">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($littleMagList as $lm)
                    @php
                        $lmPhoto = $lm->form_data['student_photo'] ?? ($lm->form_data['photo'] ?? ($lm->form_data['author_photo'] ?? ($lm->photo ?? null)));
                        $photoSrcLm = null;
                        if (!empty($lmPhoto)) {
                            $photoSrcLm = \Illuminate\Support\Str::startsWith($lmPhoto, ['http://', 'https://'])
                                ? $lmPhoto
                                : asset('storage/' . ltrim($lmPhoto, '/'));
                        }
                        $magName = $lm->magazine_name ?: 'পত্রিকার নাম উল্লেখ নেই';
                        $issueCount = $lm->magazine_issue_count ?: '-';
                        $div = $lm->resolved_division;
                        $dist = $lm->resolved_district;
                        $thana = $lm->resolved_thana ?: '-';
                        $cleanPhoneLm = preg_replace('/[^0-9]/', '', $lm->phone);
                        if (str_starts_with($cleanPhoneLm, '0')) {
                            $cleanPhoneLm = '88' . $cleanPhoneLm;
                        }
                    @endphp
                    <tr class="littlemag-row" id="lmRow{{ $lm->id }}" data-search-text="{{ strtolower($magName . ' ' . $lm->name . ' ' . $lm->phone . ' ' . $dist . ' ' . $div . ' ' . $lm->registration_number . ' ' . $thana . ' ' . ($lm->email ?? '')) }}">
                        {{-- আইডি --}}
                        <td>
                            <span class="badge bg-light text-primary border font-monospace px-2 py-1 fw-bold copy-btn" data-copy="{{ $lm->registration_number }}" role="button" title="ক্লিক করে রেজি নং কপি করুন">
                                {{ $lm->registration_number }}
                            </span>
                        </td>

                        {{-- অংশগ্রহণকারী --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($photoSrcLm)
                                    <img src="{{ $photoSrcLm }}" alt="{{ $lm->name }}" class="aec-avatar" onerror="this.style.display='none'">
                                @else
                                    <div class="aec-avatar-placeholder">
                                        {{ mb_substr($lm->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $lm->name }}</div>
                                    <div class="d-flex align-items-center gap-1 mt-0.5 flex-wrap">
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0.5 px-1.5" style="font-size: 10.5px;">
                                            <i class="fa-solid fa-book-open me-0.5"></i> {{ $magName }}
                                        </span>
                                        @if($issueCount !== '-')
                                            <span class="badge bg-light text-muted border py-0.5 px-1.5 font-monospace" style="font-size: 10px;">{{ $issueCount }} সংখ্যা</span>
                                        @endif
                                    </div>
                                    @if(!empty($lm->form_data['pen_name']))
                                        <small class="text-muted d-block" style="font-size: 10.5px;">কলমী নাম: {{ $lm->form_data['pen_name'] }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- যোগাযোগ --}}
                        <td>
                            <div class="d-flex align-items-center gap-1.5">
                                <a href="tel:{{ $lm->phone }}" class="text-decoration-none fw-semibold text-dark font-monospace small">
                                    {{ $lm->phone }}
                                </a>
                                <a href="https://wa.me/{{ $cleanPhoneLm }}" target="_blank" class="text-success small" title="হোয়াটসঅ্যাপ মেসেজ পাঠান">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>
                            @if($lm->email)
                                <small class="text-muted d-block text-truncate" style="font-size: 11px; max-width: 130px;" title="{{ $lm->email }}">
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
                                @if($lm->transaction_id)
                                    <small class="text-muted font-monospace d-block copy-btn" data-copy="{{ $lm->transaction_id }}" role="button" title="Copy TrxID">
                                        Trx: {{ $lm->transaction_id }}
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-light text-muted border">বিনামূল্যে</span>
                            @endif
                        </td>

                        {{-- পেমেন্ট --}}
                        <td>
                            @if($campaign->has_fee_or_donation)
                                <span class="badge {{ $lm->payment_status === 'verified' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-0.5" style="font-size: 10.5px;">
                                    {{ $lm->payment_status === 'verified' ? 'Verified' : ucfirst($lm->payment_status) }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted">-</span>
                            @endif
                        </td>

                        {{-- স্ট্যাটাস --}}
                        <td class="text-center" id="statusBadgeWrapLm_{{ $lm->id }}">
                            @if($lm->status === 'confirmed' || $lm->status === 'approved')
                                <span class="badge badge-status-confirmed px-2 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> Approved
                                </span>
                            @elseif($lm->status === 'selected')
                                <span class="badge badge-status-selected px-2 py-1">
                                    <i class="fa-solid fa-award me-1"></i> Selected
                                </span>
                            @elseif($lm->status === 'pending')
                                <span class="badge badge-status-pending px-2 py-1">
                                    <i class="fa-solid fa-clock me-1"></i> Pending
                                </span>
                            @elseif($lm->status === 'attended')
                                <span class="badge badge-status-attended px-2 py-1">
                                    <i class="fa-solid fa-signature me-1"></i> Attended
                                </span>
                            @else
                                <span class="badge badge-status-rejected px-2 py-1">
                                    {{ ucfirst($lm->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- তারিখ --}}
                        <td>
                            <span class="text-muted small">{{ $lm->created_at->format('d M, Y') }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">{{ $lm->created_at->format('h:i A') }}</small>
                        </td>

                        {{-- অ্যাকশন --}}
                        <td class="text-end pe-3">
                            <div class="aec-action-group justify-content-end" id="quickStatusActionsWrapLm_{{ $lm->id }}">
                                {{-- Quick Approve Button --}}
                                <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $lm->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $lm->id }}" data-status-action="lm-approve">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ in_array($lm->status, ['confirmed', 'approved', 'selected']) ? 'pending' : 'confirmed' }}">
                                    @if(in_array($lm->status, ['confirmed', 'approved', 'selected']))
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2 py-0.5 text-nowrap shadow-xs" style="font-size: 10.5px;" title="Approved (ক্লিক করে পেন্ডিং করুন)">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill px-2 py-0.5 text-nowrap shadow-xs" style="font-size: 10.5px;" title="এপ্রুভ করুন (স্বাগত ও কার্ড ডাউনলোড এসএমএস যাবে)">
                                            <i class="fa-solid fa-check"></i> এপ্রুভ
                                        </button>
                                    @endif
                                </form>

                                {{-- Quick Reject Button --}}
                                <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $lm->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $lm->id }}" data-status-action="lm-reject">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $lm->status === 'rejected' ? 'pending' : 'rejected' }}">
                                    @if($lm->status === 'rejected')
                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-2 py-0.5 text-nowrap shadow-xs" style="font-size: 10.5px;" title="Rejected (ক্লিক করে পেন্ডিং করুন)">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-danger bg-white rounded-pill px-2 py-0.5 text-nowrap shadow-xs" style="font-size: 10.5px;" title="আবেদন বাতিল করুন (নোটিশ এসএমএস যাবে)">
                                            <i class="fa-solid fa-xmark"></i> বাতিল
                                        </button>
                                    @endif
                                </form>

                                {{-- Preview / View Details Trigger --}}
                                <button type="button" class="btn-action-icon btn-action-preview" data-bs-toggle="modal" data-bs-target="#viewDetailsModalLm{{ $lm->id }}" title="পূর্ণ বিবরণ প্রিভিউ করুন">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Edit Modal Trigger --}}
                                <button type="button" class="btn-action-icon btn-action-edit" data-bs-toggle="modal" data-bs-target="#editRegModalLm{{ $lm->id }}" title="অংশগ্রহণকারী সম্পাদনা করুন (ক্যাটাগরি পরিবর্তন)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                {{-- Instant Convert to Writer Button --}}
                                <button type="button" class="btn-action-icon btn-action-convert" onclick="quickConvertToWriter({{ $lm->id }}, '{{ addslashes($lm->name) }}')" title="এক ক্লিকে সাধারণ লেখক ক্যাটাগরিতে স্থানান্তর করুন">
                                    <i class="fa-solid fa-feather-pointed"></i>
                                </button>

                                {{-- SMS Modal Trigger --}}
                                <button type="button" class="btn-action-icon btn-action-sms" data-bs-toggle="modal" data-bs-target="#smsModalLm{{ $lm->id }}" title="মেসেজ পাঠান (কার্ড ডাউনলোড লিংক / নোটিশ)">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>

                                {{-- Print Application Pass / Card --}}
                                <a href="{{ route('admin.event-campaigns.registrations.print', $lm->id) }}" target="_blank" class="btn-action-icon btn-action-print" title="আমন্ত্রণপত্র / কার্ড প্রিন্ট">
                                    <i class="fa-solid fa-print"></i>
                                </a>

                                {{-- PDF Download --}}
                                <a href="{{ route('admin.event-campaigns.registrations.pdf', $lm->id) }}" class="btn-action-icon btn-action-pdf" title="PDF ফরম ডাউনলোড">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                {{-- Delete --}}
                                <button type="button" class="btn-action-icon btn-action-delete btn-delete-reg" data-id="{{ $lm->id }}" data-name="{{ $lm->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $lm->id) }}" title="নিবন্ধন ডিলিট করুন">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>

                            {{-- MODAL 1: VIEW DETAILS MODAL FOR LITTLE MAG EDITOR --}}
                            <div class="modal fade" id="viewDetailsModalLm{{ $lm->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl text-start" style="max-width: 1000px;">
                                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                        <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #ffffff;">
                                            <div class="d-flex align-items-center gap-3">
                                                @if($photoSrcLm)
                                                    <img src="{{ $photoSrcLm }}" alt="{{ $lm->name }}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff;">
                                                @else
                                                    <div class="aec-avatar-placeholder" style="width: 50px; height: 50px; font-size: 20px;">
                                                        {{ mb_substr($lm->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <h5 class="modal-title fw-bold mb-0 text-white">{{ $lm->name }}</h5>
                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                        <span class="badge bg-warning text-dark font-monospace"><i class="fa-solid fa-newspaper me-1"></i> {{ $magName }}</span>
                                                        <span class="badge bg-info text-dark font-monospace">Reg #: {{ $lm->registration_number }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4 bg-light">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-white rounded-3 border">
                                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa-solid fa-id-card text-primary me-1"></i> ব্যক্তিগত ও যোগাযোগ তথ্য</h6>
                                                        <div class="small mb-1"><strong>পূর্ণ নাম:</strong> {{ $lm->name }}</div>
                                                        <div class="small mb-1"><strong>মোবাইল নম্বর:</strong> <a href="tel:{{ $lm->phone }}">{{ $lm->phone }}</a></div>
                                                        <div class="small mb-1"><strong>ইমেইল:</strong> {{ $lm->email ?: 'উল্লেখ নেই' }}</div>
                                                        <div class="small mb-1"><strong>কলমী নাম:</strong> {{ $lm->form_data['pen_name'] ?? '—' }}</div>
                                                        <div class="small"><strong>ঠিকানা:</strong> {{ implode(', ', array_filter([$lm->address, $lm->thana, $lm->district])) ?: '—' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-white rounded-3 border">
                                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa-solid fa-book-open text-warning me-1"></i> লিটিলম্যাগাজিন ও সাহিত্যিক তথ্য</h6>
                                                        <div class="small mb-1"><strong>পত্রিকার নাম:</strong> <span class="badge bg-warning-subtle text-dark border">{{ $magName }}</span></div>
                                                        <div class="small mb-1"><strong>প্রকাশিত সংখ্যা:</strong> {{ $issueCount }}</div>
                                                        <div class="small mb-1"><strong>ক্যাটাগরি:</strong> {{ $lm->form_data['author_category'] ?? 'লিটিলম্যাগাজিন সম্পাদক' }}</div>
                                                        <div class="small mb-1"><strong>প্রকাশিত বই:</strong> {{ $lm->form_data['published_books_count'] ?? ($lm->form_data['published_book_count'] ?? '—') }}</div>
                                                        <div class="small"><strong>সংগঠন:</strong> {{ $lm->institution_or_org ?: '—' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="p-3 bg-white rounded-3 border">
                                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa-solid fa-receipt text-success me-1"></i> পেমেন্ট ও নিবন্ধন স্থিতি</h6>
                                                        <div class="d-flex flex-wrap gap-4 small">
                                                            <div><strong>স্ট্যাটাস:</strong> {{ ucfirst($lm->status) }}</div>
                                                            <div><strong>পরিশোধিত ফি:</strong> ৳{{ number_format($lm->amount_paid) }}</div>
                                                            <div><strong>পেমেন্ট স্ট্যাটাস:</strong> {{ ucfirst($lm->payment_status) }}</div>
                                                            <div><strong>TrxID:</strong> <code>{{ $lm->transaction_id ?: '—' }}</code></div>
                                                            <div><strong>পেমেন্ট মাধ্যম:</strong> {{ $lm->payment_method ?: '—' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2.5 px-4 bg-white border-top d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editRegModalLm{{ $lm->id }}">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> তথ্য এডিট করুন
                                            </button>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.event-campaigns.registrations.print', $lm->id) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">
                                                    <i class="fa-solid fa-print me-1"></i> প্রিন্ট কার্ড
                                                </a>
                                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL 2: FULL EDIT PARTICIPANT MODAL FOR LITTLE MAG EDITOR --}}
                            <div class="modal fade" id="editRegModalLm{{ $lm->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                        <form action="{{ route('admin.event-campaigns.registrations.update', $lm->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header py-3 bg-light border-bottom">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="p-2 bg-warning-subtle text-warning rounded-circle" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fa-solid fa-user-pen"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="modal-title fw-bold mb-0">সম্পাদক / লেখক তথ্য সম্পাদনা (Edit Participant)</h6>
                                                        <small class="text-muted font-monospace">Reg #: {{ $lm->registration_number }} &bull; {{ $lm->name }}</small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    {{-- Photo --}}
                                                    <div class="col-12 col-md-3 text-center">
                                                        <div class="p-3 bg-light rounded-3 border">
                                                            <label class="form-label small fw-bold text-dark d-block mb-2">প্রোফাইল ছবি</label>
                                                            <div class="mb-2">
                                                                @if($photoSrcLm)
                                                                    <img src="{{ $photoSrcLm }}" alt="{{ $lm->name }}" id="editPhotoPreviewLm{{ $lm->id }}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #0284c7;">
                                                                @else
                                                                    <div id="editPhotoPreviewLm{{ $lm->id }}" class="aec-avatar-placeholder mx-auto" style="width: 80px; height: 80px; font-size: 28px;">
                                                                        {{ mb_substr($lm->name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <input type="file" name="photo" id="editPhotoInputLm{{ $lm->id }}" class="d-none" accept="image/*" onchange="initParticipantPhotoAdjust(this, {{ $lm->id }}, 'editPhotoPreviewLm{{ $lm->id }}')">
                                                            <input type="hidden" name="cropped_photo_data" id="croppedPhotoData_editPhotoPreviewLm{{ $lm->id }}" value="">
                                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1 w-100 fw-semibold" style="font-size: 11px;" onclick="document.getElementById('editPhotoInputLm{{ $lm->id }}').click()">
                                                                <i class="fa-solid fa-camera me-1"></i> ছবি পরিবর্তন
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Core Inputs --}}
                                                    <div class="col-12 col-md-9">
                                                        <div class="row g-2">
                                                            <div class="col-12 col-sm-6">
                                                                <label class="form-label small fw-bold text-dark mb-1">পূর্ণ নাম <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $lm->name }}" required>
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label class="form-label small fw-bold text-dark mb-1">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                                                <input type="text" name="phone" class="form-control form-control-sm font-monospace" value="{{ $lm->phone }}" required>
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label class="form-label small fw-bold text-dark mb-1">ইমেইল</label>
                                                                <input type="email" name="email" class="form-control form-control-sm" value="{{ $lm->email }}">
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <label class="form-label small fw-bold text-dark mb-1">কলমী নাম (Pen Name)</label>
                                                                <input type="text" name="pen_name" class="form-control form-control-sm" value="{{ $lm->form_data['pen_name'] ?? '' }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Category Selector (Crucial for moving to Writer!) --}}
                                                    <div class="col-12">
                                                        <div class="p-3 bg-white rounded-3 border shadow-xs">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="form-label small fw-bold text-dark mb-0">
                                                                    <i class="fa-solid fa-tags text-primary me-1"></i> সাহিত্যিক ক্যাটাগরি (লেখক / সম্পাদক নির্বাচন) <span class="text-danger">*</span>
                                                                </label>
                                                                <span class="badge bg-info-subtle text-info border" style="font-size: 10px;">
                                                                    'সম্পাদক' পরিবর্তন করে 'লেখক' নির্বাচন করলে স্বয়ংক্রিয়ভাবে লেখক তালিকায় যুক্ত হবে
                                                                </span>
                                                            </div>
                                                            @php
                                                                $curLmCat = $lm->form_data['author_category'] ?? ($lm->form_data['author_categories'][0] ?? 'লিটিলম্যাগাজিন সম্পাদক');
                                                                $allCategoryOptions = [
                                                                    'লিটিলম্যাগাজিন সম্পাদক', 'সম্পাদক', 'লেখক', 'কবি', 'কথাসাহিত্যিক',
                                                                    'ছড়াকার', 'প্রাবন্ধিক', 'গবেষক', 'অনুবাদক', 'প্রকাশক', 'নাট্যকার',
                                                                    'শিল্পী / সংস্কৃতিকর্মী', 'অন্যান্য / প্রতিনিধি'
                                                                ];
                                                            @endphp
                                                            <div class="row g-2">
                                                                <div class="col-12 col-sm-6">
                                                                    <select name="author_category" class="form-select form-select-sm fw-bold">
                                                                        @foreach($allCategoryOptions as $optCat)
                                                                            <option value="{{ $optCat }}" {{ $curLmCat === $optCat ? 'selected' : '' }}>
                                                                                {{ $optCat }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <input type="text" name="magazine_name" class="form-control form-control-sm" value="{{ $lm->form_data['magazine_name'] ?? '' }}" placeholder="লিটিলম্যাগ / পত্রিকার নাম (লেখক হলে খালি রাখুন)">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Location --}}
                                                    <div class="col-12 col-sm-4">
                                                        <label class="form-label small fw-bold text-dark mb-1">জেলা (District)</label>
                                                        <input type="text" name="district" class="form-control form-control-sm" value="{{ $lm->district }}" placeholder="যেমন: রংপুর">
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <label class="form-label small fw-bold text-dark mb-1">উপজেলা / থানা (Thana)</label>
                                                        <input type="text" name="thana" class="form-control form-control-sm" value="{{ $lm->thana }}" placeholder="যেমন: সদর">
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <label class="form-label small fw-bold text-dark mb-1">ঠিকানা / এলাকা</label>
                                                        <input type="text" name="address" class="form-control form-control-sm" value="{{ $lm->address }}" placeholder="গ্রাম / রোড / এলাকা">
                                                    </div>

                                                    {{-- Status, Payment --}}
                                                    <div class="col-12 col-sm-4">
                                                        <label class="form-label small fw-bold text-dark mb-1">নিবন্ধন স্ট্যাটাস</label>
                                                        <select name="status" class="form-select form-select-sm fw-bold">
                                                            <option value="confirmed" {{ $lm->status === 'confirmed' ? 'selected' : '' }}>✓ Approved / Confirmed</option>
                                                            <option value="selected" {{ $lm->status === 'selected' ? 'selected' : '' }}>★ Selected</option>
                                                            <option value="attended" {{ $lm->status === 'attended' ? 'selected' : '' }}>🪪 Attended</option>
                                                            <option value="pending" {{ $lm->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                                            <option value="rejected" {{ $lm->status === 'rejected' ? 'selected' : '' }}>✕ Rejected</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <label class="form-label small fw-bold text-dark mb-1">ফি (Amount ৳)</label>
                                                        <input type="number" step="1" min="0" name="amount_paid" class="form-control form-control-sm font-monospace" value="{{ $lm->amount_paid }}">
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <label class="form-label small fw-bold text-dark mb-1">পেমেন্ট স্ট্যাটাস</label>
                                                        <select name="payment_status" class="form-select form-select-sm">
                                                            <option value="verified" {{ $lm->payment_status === 'verified' ? 'selected' : '' }}>Verified Paid</option>
                                                            <option value="pending" {{ $lm->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="free" {{ $lm->payment_status === 'free' ? 'selected' : '' }}>Free</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label class="form-label small fw-bold text-dark mb-1">TrxID</label>
                                                        <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace" value="{{ $lm->transaction_id }}" placeholder="Bkash/Nagad TrxID">
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <label class="form-label small fw-bold text-dark mb-1">এডমিন নোট</label>
                                                        <input type="text" name="admin_notes" class="form-control form-control-sm" value="{{ $lm->admin_notes }}" placeholder="অভ্যন্তরীণ মন্তব্য...">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top py-2.5 bg-light d-flex justify-content-between">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 btn-delete-reg" data-id="{{ $lm->id }}" data-name="{{ $lm->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $lm->id) }}">
                                                    <i class="fa-solid fa-trash me-1"></i> ডিলিট
                                                </button>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> পরিবর্তন সংরক্ষণ
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL 3: SMS DISPATCH MODAL FOR LITTLE MAG EDITOR --}}
                            @include('admin.event_campaigns.partials.sms_modal', ['item' => $lm, 'prefix' => 'Lm'])

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
    // Live Search Filter for Little Mag Table
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
            badge.innerText = q ? `${matchCount} টি ফলাফল পাওয়া গেছে` : `মোট {{ $totalLittleMagCount }} টি রেকর্ড প্রদর্শিত`;
        }
    }

    // 1-Click Convert Little Mag Editor to General Writer (লেখক)
    function quickConvertToWriter(regId, regName) {
        if (!confirm(`আপনি কি "${regName}"-কে লিটিলম্যাগ সম্পাদক থেকে সাধারণ "লেখক" ক্যাটাগরিতে স্থানান্তর করতে চান?`)) {
            return;
        }

        const url = `/admin/event-campaigns/registrations/${regId}/convert-to-writer`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ category: 'লেখক' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Visual feedback: animate row out of Little Mag table
                const row = document.getElementById('lmRow' + regId);
                if (row) {
                    row.style.transition = 'all 0.4s ease';
                    row.style.backgroundColor = '#dcfce7';
                    setTimeout(() => {
                        row.remove();
                        filterLittleMagTable('');
                    }, 500);
                }

                if (window.showToast) {
                    window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'লেখক ক্যাটাগরিতে স্থানান্তর সম্পন্ন!'));
                } else {
                    alert(data.message || 'লেখক ক্যাটাগরিতে স্থানান্তর সম্পন্ন!');
                }
            } else {
                alert(data.message || 'স্থানান্তর ব্যর্থ হয়েছে।');
            }
        })
        .catch(err => {
            console.error('Convert to writer error:', err);
            alert('সার্ভার যোগাযোগে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        });
    }
</script>
