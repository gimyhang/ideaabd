@extends('layouts.admin')

@section('title', 'Messaging')
@section('heading', 'SMS ও ইমেইল')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-sms.css') }}?v={{ @filemtime(public_path('css/admin-sms.css')) ?: 1 }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Messaging</li>
@endsection


@section('content')
<div class="container-fluid px-0">

    @include('admin.sms.partials.health')

    {{-- Bulk SMS campaigns: live progress (filled and refreshed by admin-sms.js) --}}
    <div id="smsCampaigns" class="sms-campaigns mb-4" data-tick-url="{{ route('admin.sms.campaigns.tick') }}" data-action-url="{{ url('admin/sms/campaigns/__ID__/action') }}" @if(empty($liveCampaigns)) hidden @endif>
        <div class="sms-campaigns-head">
            <strong><i class="fa-solid fa-satellite-dish me-1"></i> Campaigns</strong>
            <small class="text-muted">১০০ জন করে পাঠানো হয়; পেজ খোলা থাকলে দ্রুত চলে। বন্ধ করলেও পরে বাকি অংশ থেকে চলবে।</small>
        </div>
        <div class="sms-campaigns-list" data-campaign-list></div>
    </div>

    {{-- Main tabs: one word each --}}
    @php
        $hubTabs = [
            ['send',      'fa-paper-plane',       'Send'],
            ['test',      'fa-vial',              'Test'],
            ['logs',      'fa-clock-rotate-left', 'History'],
            ['balance',   'fa-wallet',            'Balance'],
            ['templates', 'fa-bookmark',          'Templates'],
            ['guard',     'fa-shield-halved',     'Guard'],
            ['settings',  'fa-gear',              'Settings'],
        ];
        $groupsUi = [
            'customers'  => ['গ্রাহক', 'fa-users'],
            'authors'    => ['লেখক', 'fa-pen-nib'],
            'publishers' => ['প্রকাশক', 'fa-building'],
            'sellers'    => ['বিক্রেতা', 'fa-store'],
            'libraries'  => ['পাঠাগার', 'fa-book-open-reader'],
            'all'        => ['সবাই', 'fa-globe'],
            'custom'     => ['নিজের তালিকা', 'fa-list'],
        ];
        $oldChannel = old('channel', 'sms');
        $oldGroup = old('target_group', 'customers');
    @endphp
    <div class="sms-tabs-wrap mb-4">
        <ul class="nav sms-tabs" id="messagingHubTabs" role="tablist">
            @foreach($hubTabs as $i => [$key, $icon, $label])
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $i === 0 ? 'active' : '' }}" id="tab-{{ $key }}-btn"
                            data-bs-toggle="pill" data-bs-target="#tab-{{ $key }}" type="button" role="tab"
                            aria-controls="tab-{{ $key }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                        <i class="fa-solid {{ $icon }}"></i>
                        <span>{{ $label }}</span>
                        @if($key === 'guard' && $restrictions->count() > 0)
                            <span class="sms-count">{{ $restrictions->count() }}</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content" id="messagingHubContent">

        {{-- ================= SEND: one composer for SMS, e-mail or both ================= --}}
        <div class="tab-pane fade show active" id="tab-send" role="tabpanel" aria-labelledby="tab-send-btn">
            <form action="{{ route('admin.sms.campaign') }}" method="POST" id="composeForm" class="row g-4" novalidate>
                @csrf
                <div class="col-12 col-xl-7">
                    <div class="sms-panel sms-compose">
                        <div class="sms-step">
                            <div class="sms-step__label"><span>১</span> মাধ্যম</div>
                            <div class="sms-seg" role="radiogroup" aria-label="মাধ্যম">
                                @foreach(['sms' => ['SMS', 'fa-comment-sms'], 'email' => ['ইমেইল', 'fa-envelope'], 'both' => ['দুটোই', 'fa-layer-group']] as $ck => [$cl, $ci])
                                    <input type="radio" class="btn-check" name="channel" id="ch-{{ $ck }}" value="{{ $ck }}" @checked($oldChannel === $ck)>
                                    <label for="ch-{{ $ck }}"><i class="fa-solid {{ $ci }}"></i> {{ $cl }}</label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sms-step">
                            <div class="sms-step__label"><span>২</span> প্রাপক</div>
                            <div class="sms-aud" role="radiogroup" aria-label="প্রাপক">
                                @foreach($groupsUi as $gk => [$gl, $gi])
                                    <input type="radio" class="btn-check" name="target_group" id="tg-{{ $gk }}" value="{{ $gk }}" @checked($oldGroup === $gk)>
                                    <label for="tg-{{ $gk }}">
                                        <i class="fa-solid {{ $gi }}"></i>
                                        <span class="sms-aud__name">{{ $gl }}</span>
                                        @if($gk !== 'custom')<span class="sms-aud__n" data-aud-count="{{ $gk }}">—</span>@endif
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-2" id="customListBox" @if($oldGroup !== 'custom') hidden @endif>
                                <textarea name="custom_list" rows="4" class="form-control sms-form-control font-monospace" placeholder="01712345678 | রহিম | লেখক&#10;rahim@example.com | রহিম&#10;01812345678, 01912345678">{{ old('custom_list') }}</textarea>
                                <div class="form-text">প্রতি লাইনে একজন: <code>নম্বর বা ইমেইল | নাম | পরিচয়</code>। নাম ছাড়া কমা দিয়ে অনেকগুলো দেওয়া যায়।</div>
                            </div>
                        </div>

                        <div class="sms-step">
                            <div class="sms-step__label d-flex justify-content-between align-items-center"><span class="d-inline-flex align-items-center gap-2"><span>৩</span> বার্তা</span>
                                <div class="d-flex gap-1 align-items-center flex-wrap">
                                    @foreach(['{name}' => 'নাম', '{role}' => 'পরিচয়', '{phone}' => 'নম্বর'] as $sc => $sl)
                                        <button type="button" class="shortcode-chip" data-insert="{{ $sc }}" title="{{ $sl }} বসান">{{ $sc }}</button>
                                    @endforeach
                                    <div class="dropdown">
                                        <button class="btn btn-xs btn-outline-primary rounded-pill dropdown-toggle px-2.5" type="button" data-bs-toggle="dropdown" aria-expanded="false" @disabled(empty($templates))>
                                            <i class="fa-solid fa-bookmark me-1"></i> টেমপ্লেট
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="max-height: 300px; overflow-y: auto;">
                                            @foreach($templates as $t)
                                                <li><button type="button" class="dropdown-item small" data-use-template="{{ $t['id'] }}">{{ $t['title'] }} <span class="text-muted">· {{ strtoupper($t['channel'] ?? 'both') }}</span></button></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2" data-mail-only>
                                <input type="text" name="subject" id="composeSubject" class="form-control sms-form-control" maxlength="255" placeholder="ইমেইলের বিষয় (subject)" value="{{ old('subject') }}">
                            </div>
                            <textarea name="message" id="composeMessage" rows="6" maxlength="1000" class="form-control sms-form-control" required placeholder="সম্মানিত {name}, আইডিয়া প্রকাশনে…">{{ old('message') }}</textarea>
                            <div class="sms-counter-bar"><div id="composeProgress" class="sms-counter-progress"></div></div>
                            <div class="d-flex justify-content-between flex-wrap gap-2 mt-1 small text-muted">
                                <span id="composeCounter">০ অক্ষর</span>
                                <span id="composePersonal" class="text-success fw-semibold" hidden><i class="fa-solid fa-user-check me-1"></i> প্রত্যেকে নিজের নামে বার্তা পাবেন</span>
                            </div>
                            <div class="row g-2 mt-1" data-mail-only>
                                <div class="col-6"><input type="text" name="action_text" id="composeActionText" class="form-control form-control-sm" maxlength="100" placeholder="ইমেইলের বোতাম (ঐচ্ছিক)" value="{{ old('action_text') }}"></div>
                                <div class="col-6"><input type="url" name="action_url" id="composeActionUrl" class="form-control form-control-sm" maxlength="255" placeholder="https://… (বোতামের লিংক)" value="{{ old('action_url') }}"></div>
                            </div>
                        </div>

                        <div class="sms-summary" id="composeSummary" aria-live="polite">
                            <div><span>প্রাপক</span><strong data-sum="people">—</strong></div>
                            <div data-sms-only><span>SMS ক্রেডিট</span><strong data-sum="credits">—</strong></div>
                            <div data-sms-only><span>আনুমানিক খরচ</span><strong data-sum="cost">—</strong></div>
                            <div data-mail-only><span>ইমেইল</span><strong data-sum="emails">—</strong></div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold mt-3" id="composeSend">
                            <i class="fa-solid fa-paper-plane me-1"></i> <span>পাঠান</span>
                        </button>
                        @if(($health['kind'] ?? null) === 'key' && ($health['state'] ?? '') === 'error')
                            <div class="small text-danger mt-2 text-center"><i class="fa-solid fa-triangle-exclamation me-1"></i> API key ঠিক না হওয়া পর্যন্ত SMS যাবে না; ইমেইল যাবে।</div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="sms-preview-stack">
                        <div class="sms-preview" data-sms-only>
                            <span class="sms-label">SMS প্রিভিউ</span>
                            <div class="sms-preview-sender"><i class="fa-solid fa-tower-broadcast me-1"></i> {{ $credentials['sender_id'] ?? 'IDEA' }}</div>
                            <div class="phone-sms-bubble brand-sender" id="previewSms">বার্তা লিখলে এখানে দেখাবে…</div>
                        </div>
                        <div class="sms-mail-preview" data-mail-only>
                            <span class="sms-label">ইমেইল প্রিভিউ</span>
                            <div class="sms-mail-preview__card">
                                <div class="sms-mail-preview__from"><i class="fa-solid fa-envelope me-1"></i> {{ $smtpSettings['from_name'] ?? 'IDEA Prokashon' }} &lt;{{ $smtpSettings['from_address'] ?? '' }}&gt;</div>
                                <div class="sms-mail-preview__subj" id="previewSubject">বিষয়</div>
                                <div class="sms-mail-preview__body" id="previewBody">বার্তা লিখলে এখানে দেখাবে…</div>
                                <span class="sms-mail-preview__btn" id="previewBtn" hidden></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ================= TEST: one place to try SMS, e-mail and OTP ================= --}}
        <div class="tab-pane fade" id="tab-test" role="tabpanel" aria-labelledby="tab-test-btn">
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="sms-panel p-4">
                        <div class="sms-seg mb-4" role="tablist" aria-label="টেস্টের ধরন">
                            <input type="radio" class="btn-check" name="test_kind" id="tk-sms" value="sms" checked><label for="tk-sms"><i class="fa-solid fa-comment-sms"></i> SMS</label>
                            <input type="radio" class="btn-check" name="test_kind" id="tk-email" value="email"><label for="tk-email"><i class="fa-solid fa-envelope"></i> ইমেইল</label>
                            <input type="radio" class="btn-check" name="test_kind" id="tk-otp" value="otp"><label for="tk-otp"><i class="fa-solid fa-key"></i> OTP</label>
                        </div>

                        <form id="quickTestSmsForm" data-test="sms" action="{{ route('admin.sms.send-test') }}" method="POST">
                            @csrf
                            <label class="form-label small fw-semibold">মোবাইল নম্বর</label>
                            <input type="tel" name="phone" class="form-control sms-form-control mb-3" placeholder="01712345678" value="{{ auth()->user()->phone ?? '' }}" required>
                            <label class="form-label small fw-semibold">বার্তা</label>
                            <textarea name="message" id="testMessage" rows="3" maxlength="500" class="form-control sms-form-control mb-3" required>আইডিয়া প্রকাশন: টেস্ট SMS — গেটওয়ে সচল আছে।</textarea>
                            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-paper-plane me-1"></i> টেস্ট SMS পাঠান</button>
                        </form>

                        <form id="testEmailForm" data-test="email" action="{{ route('admin.sms.send-email-test') }}" method="POST" hidden>
                            @csrf
                            <label class="form-label small fw-semibold">ইমেইল</label>
                            <input type="email" name="recipient_email" class="form-control sms-form-control mb-3" placeholder="user@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                            <label class="form-label small fw-semibold">বিষয়</label>
                            <input type="text" name="subject" class="form-control sms-form-control mb-3" value="আইডিয়া প্রকাশন — টেস্ট ইমেইল" required>
                            <label class="form-label small fw-semibold">বার্তা</label>
                            <textarea name="body_content" rows="3" class="form-control sms-form-control mb-3" required>ইমেইল সার্ভার সচল আছে।</textarea>
                            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-paper-plane me-1"></i> টেস্ট ইমেইল পাঠান</button>
                        </form>

                        <form id="testOtpForm" data-test="otp" action="{{ route('admin.sms.send-otp-test') }}" method="POST" hidden>
                            @csrf
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">OTP-র ধরন</label>
                                    <select name="otp_type" class="form-select sms-form-control" required>
                                        <option value="registration">নিবন্ধন</option>
                                        <option value="password_reset">পাসওয়ার্ড রিসেট</option>
                                        <option value="login_2fa">লগিন</option>
                                        <option value="order_confirmation">অর্ডার যাচাই</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">মাধ্যম</label>
                                    <select name="channel" class="form-select sms-form-control" required>
                                        <option value="sms">SMS</option>
                                        <option value="email">ইমেইল</option>
                                        <option value="both">দুটোই</option>
                                    </select>
                                </div>
                                <div class="col-6"><label class="form-label small fw-semibold">মোবাইল</label><input type="tel" name="phone" class="form-control sms-form-control" value="{{ auth()->user()->phone ?? '' }}"></div>
                                <div class="col-6"><label class="form-label small fw-semibold">ইমেইল</label><input type="email" name="email" class="form-control sms-form-control" value="{{ auth()->user()->email ?? '' }}"></div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-key me-1"></i> টেস্ট OTP পাঠান</button>
                        </form>

                        <div id="testResult" class="sms-result-box mt-3" hidden aria-live="polite"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="sms-panel p-4 h-100">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-circle-info text-primary me-1"></i> টেস্ট কেন</div>
                        <ul class="small text-muted ps-3 mb-0 d-flex flex-column gap-2">
                            <li><b>SMS:</b> গেটওয়ে, API key ও সেন্ডার আইডি ঠিক আছে কিনা দেখায়; ভুল হলে কারণসহ জানায়।</li>
                            <li><b>ইমেইল:</b> SMTP সেটিংস ঠিক আছে কিনা দেখায়।</li>
                            <li><b>OTP:</b> নিবন্ধন / লগিন / পাসওয়ার্ড রিসেটে গ্রাহক যে বার্তা পান, ঠিক সেটিই পাঠায়।</li>
                            <li>প্রতিটি টেস্ট "History" ও "Balance" ট্যাবে রেকর্ড হয়।</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= HISTORY: campaigns ================= --}}
        <div class="tab-pane fade" id="tab-logs" role="tabpanel" aria-labelledby="tab-logs-btn">
            @php
                $totalAll = array_sum(array_column($campaignLogs, 'total'));
                $sentAll = array_sum(array_column($campaignLogs, 'sent'));
                $rate = $totalAll > 0 ? round(($sentAll / $totalAll) * 100, 1) : null;
            @endphp
            <div class="row g-3 mb-4">
                @foreach([['ক্যাম্পেইন', number_format(count($campaignLogs)), 'text-dark'], ['সফল', number_format($sentAll), 'text-success'], ['ব্যর্থ', number_format(array_sum(array_column($campaignLogs, 'failed'))), 'text-danger'], ['সাফল্যের হার', $rate === null ? '—' : $rate . '%', 'text-primary']] as [$lb, $val, $tone])
                    <div class="col-6 col-md-3"><div class="sms-panel sms-kpi"><span class="sms-label">{{ $lb }}</span><div class="sms-kpi-value {{ $tone }}">{{ $val }}</div></div></div>
                @endforeach
            </div>

            <div class="sms-panel mb-4">
                <div class="sms-panel-head flex-wrap">
                    <div>
                        <h5 class="mb-0 fw-bold">ক্যাম্পেইনের ইতিহাস</h5>
                        <div class="small text-muted">বাল্ক SMS ও ইমেইলের রেকর্ড। প্রতিটি নম্বরের হিসাব "Balance" ট্যাবে।</div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <input type="search" id="campaignLogSearch" class="form-control form-control-sm" style="width: 200px;" placeholder="খুঁজুন…" aria-label="খুঁজুন">
                        <select id="campaignLogChannel" class="form-select form-select-sm" style="width: 120px;" aria-label="মাধ্যম">
                            <option value="all">সব</option>
                            <option value="sms">SMS</option>
                            <option value="email">ইমেইল</option>
                            <option value="both">দুটোই</option>
                        </select>
                        <a href="{{ route('admin.sms.logs.export') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-download me-1"></i> CSV</a>
                        @if(count($campaignLogs))
                            <form action="{{ route('admin.sms.logs.clear') }}" method="POST" class="d-inline" data-confirm="সব ক্যাম্পেইনের ইতিহাস মুছে ফেলবেন? (প্রতিটি নম্বরের রেকর্ড Balance ট্যাবে থেকে যাবে)" data-confirm-icon="error">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="fa-solid fa-trash-can me-1"></i> মুছুন</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table sms-table sms-table--logs align-middle mb-0">
                        <thead><tr><th>সময়</th><th>মাধ্যম</th><th>শিরোনাম / বার্তা</th><th>প্রাপক</th><th>ফল</th><th>প্রেরক</th><th></th></tr></thead>
                        <tbody id="campaignLogsTableBody">
                            @forelse($campaignLogs as $log)
                                @php $ch = strtolower($log['channel'] ?? 'sms'); $ch = in_array($ch, ['sms', 'email'], true) ? $ch : 'both'; @endphp
                                <tr class="log-row" data-channel="{{ $ch }}">
                                    <td class="small text-muted text-nowrap" data-l="সময়">{{ $log['created_at'] ?? '' }}</td>
                                    <td data-l="মাধ্যম"><span class="sms-ch sms-ch--{{ $ch }}">{{ ['sms' => 'SMS', 'email' => 'ইমেইল', 'both' => 'দুটোই'][$ch] }}</span></td>
                                    <td class="sms-msg-cell">
                                        <div class="fw-semibold text-truncate">{{ $log['title'] ?? 'ক্যাম্পেইন' }}</div>
                                        <div class="small text-muted text-truncate">{{ $log['preview'] ?? '' }}</div>
                                    </td>
                                    <td data-l="প্রাপক"><span class="fw-bold">{{ number_format($log['total'] ?? 0) }}</span> <span class="small text-muted">{{ $groupsUi[$log['target_group'] ?? '']['0'] ?? ($log['target_group'] ?? '') }}</span></td>
                                    <td data-l="ফল">
                                        <span class="text-success fw-semibold">{{ number_format($log['sent'] ?? 0) }}</span>
                                        @if(($log['failed'] ?? 0) > 0)<span class="text-danger small">· {{ number_format($log['failed']) }} ব্যর্থ</span>@endif
                                        @if(!empty($log['refused']))<span class="text-muted small">· {{ number_format($log['refused']) }} বাদ</span>@endif
                                    </td>
                                    <td class="small text-muted" data-l="প্রেরক">{{ $log['admin_name'] ?? 'Admin' }}</td>
                                    <td class="text-end"><button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" data-log='@json($log)' title="বিস্তারিত"><i class="fa-solid fa-eye"></i></button></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fa-solid fa-clipboard-list fs-2 opacity-25 d-block mb-2"></i> এখনো কোনো ক্যাম্পেইন পাঠানো হয়নি।</td></tr>
                            @endforelse
                            <tr id="campaignLogsNoMatch" hidden><td colspan="7" class="text-center py-4 text-muted">মিল পাওয়া যায়নি।</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= BALANCE & per-number history ================= --}}
        <div class="tab-pane fade" id="tab-balance" role="tabpanel" aria-labelledby="tab-balance-btn">
            @include('admin.sms.partials.balance')
        </div>

        {{-- ================= TEMPLATES ================= --}}
        <div class="tab-pane fade" id="tab-templates" role="tabpanel" aria-labelledby="tab-templates-btn">
            <div class="sms-panel mb-4">
                <div class="sms-panel-head">
                    <div>
                        <h5 class="mb-0 fw-bold">টেমপ্লেট</h5>
                        <div class="small text-muted">বারবার লেখা বার্তা সংরক্ষণ করে রাখুন; "ব্যবহার" চাপলে Send-এ বসে যায়।</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-tpl-new><i class="fa-solid fa-plus me-1"></i> নতুন টেমপ্লেট</button>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        @forelse($templates as $tpl)
                            <div class="col-12 col-md-6 col-xxl-4">
                                <div class="template-item-card h-100 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $tpl['title'] }}</h6>
                                        <span class="sms-ch sms-ch--{{ in_array($tpl['channel'] ?? 'both', ['sms', 'email'], true) ? $tpl['channel'] : 'both' }}">{{ ['sms' => 'SMS', 'email' => 'ইমেইল'][$tpl['channel'] ?? ''] ?? 'দুটোই' }}</span>
                                    </div>
                                    @if(!empty($tpl['subject']))<div class="small text-muted mb-1"><b>বিষয়:</b> {{ $tpl['subject'] }}</div>@endif
                                    <div class="sms-tpl-body mb-3">{{ $tpl['body'] }}</div>
                                    <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-use-template="{{ $tpl['id'] }}"><i class="fa-solid fa-arrow-right-to-bracket me-1"></i> ব্যবহার</button>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" data-tpl-edit='@json($tpl)' title="সম্পাদনা"><i class="fa-solid fa-pen"></i></button>
                                            <form action="{{ route('admin.sms.templates.delete', $tpl['id']) }}" method="POST" class="d-inline" data-confirm="“{{ $tpl['title'] }}” টেমপ্লেটটি মুছবেন?" data-confirm-icon="error">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 text-danger" title="মুছুন"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted"><i class="fa-solid fa-bookmark fs-2 opacity-25 d-block mb-2"></i> কোনো টেমপ্লেট নেই।</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= GUARD ================= --}}
        <div class="tab-pane fade" id="tab-guard" role="tabpanel" aria-labelledby="tab-guard-btn">
            @include('admin.sms.partials.guard')
        </div>

        {{-- ================= SETTINGS: SMS gateway + e-mail server ================= --}}
        <div class="tab-pane fade" id="tab-settings" role="tabpanel" aria-labelledby="tab-settings-btn">
            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="sms-panel p-4 h-100">
                        <h5 class="fw-bold mb-1"><i class="fa-solid fa-tower-broadcast text-primary me-1"></i> SMS গেটওয়ে</h5>
                        <p class="text-muted small mb-4">BulkSMSBD প্যানেলের API key ও সেন্ডার আইডি।</p>
                        @if($usingDefaultKey)
                            <div class="alert alert-warning small"><i class="fa-solid fa-triangle-exclamation me-1"></i> কোডে থাকা পুরনো ডিফল্ট key ব্যবহার হচ্ছে — নিচে নিজের key দিন।</div>
                        @endif
                        <form action="{{ route('admin.sms.settings') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-5">
                                    <label class="form-label fw-semibold small">প্রোভাইডার</label>
                                    <select name="provider" class="form-select sms-form-control" required>
                                        @foreach(['bulksmsbd' => 'BulkSMSBD', 'alaapcloud' => 'AlaapCloud', 'sms4bd' => 'SMS4BD', 'greenweb' => 'GreenWeb', 'alphasms' => 'AlphaSMS', 'generic' => 'অন্য (HTTP API)'] as $pv => $pl)
                                            <option value="{{ $pv }}" @selected(($credentials['provider'] ?? 'bulksmsbd') === $pv)>{{ $pl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-7">
                                    <label class="form-label fw-semibold small">সেন্ডার আইডি</label>
                                    <input type="text" name="sender_id" class="form-control sms-form-control font-monospace" value="{{ $credentials['sender_id'] ?? '' }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">API key</label>
                                    <div class="input-group">
                                        <input type="password" name="api_key" id="smsApiKeyInput" class="form-control sms-form-control font-monospace" autocomplete="new-password" placeholder="{{ $credentials['api_key_masked'] ?: 'API key দিন' }}">
                                        <button type="button" class="btn btn-outline-secondary" data-sms-toggle-key title="দেখুন / লুকান" aria-label="দেখুন / লুকান"><i class="fa-regular fa-eye"></i></button>
                                        <button type="button" class="btn btn-outline-primary fw-semibold" id="smsVerifyKey" data-url="{{ route('admin.sms.settings.verify') }}"><i class="fa-solid fa-plug-circle-check me-1"></i>যাচাই</button>
                                    </div>
                                    <div class="sms-verify-out small mt-2" id="smsVerifyOut" aria-live="polite"></div>
                                    <div class="form-text">খালি রাখলে সংরক্ষিত key থাকে। নতুন key সংরক্ষণের আগে যাচাই হয়; ভুল হলে পুরনোটাই থাকে।</div>
                                </div>
                                <div class="col-12">
                                    <details class="sms-adv">
                                        <summary>উন্নত: API URL</summary>
                                        <input type="url" name="url" class="form-control sms-form-control font-monospace mt-2" value="{{ $credentials['url'] ?? 'https://bulksmsbd.net/api/smsapi' }}" pattern="https://.*" required>
                                    </details>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 mt-4"><i class="fa-solid fa-floppy-disk me-1"></i> যাচাই ও সংরক্ষণ</button>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="sms-panel p-4 h-100">
                        <h5 class="fw-bold mb-1"><i class="fa-solid fa-envelope text-danger me-1"></i> ইমেইল সার্ভার (SMTP)</h5>
                        <p class="text-muted small mb-4">যে ঠিকানা থেকে ইমেইল যাবে।</p>
                        <form action="{{ route('admin.sms.smtp-settings') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small">প্রেরকের ইমেইল</label>
                                    <input type="email" name="from_address" class="form-control sms-form-control" value="{{ $smtpSettings['from_address'] ?? '' }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small">প্রেরকের নাম</label>
                                    <input type="text" name="from_name" class="form-control sms-form-control" value="{{ $smtpSettings['from_name'] ?? 'IDEA Prokashon' }}" required>
                                </div>
                                <div class="col-8">
                                    <label class="form-label fw-semibold small">SMTP host</label>
                                    <input type="text" name="host" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['host'] ?? '' }}" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold small">Port</label>
                                    <input type="number" name="port" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['port'] ?? 587 }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small">ইউজারনেম</label>
                                    <input type="text" name="username" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['username'] ?? '' }}" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small">পাসওয়ার্ড</label>
                                    <input type="password" name="password" class="form-control sms-form-control font-monospace" autocomplete="new-password" placeholder="{{ $smtpHasPassword ? '•••••••• (সংরক্ষিত)' : '' }}">
                                </div>
                                <div class="col-12">
                                    <details class="sms-adv">
                                        <summary>উন্নত: মেইলার ও এনক্রিপশন</summary>
                                        <div class="row g-2 mt-1">
                                            <div class="col-6">
                                                <select name="mailer" class="form-select sms-form-control" required aria-label="মেইলার">
                                                    @foreach(['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log (টেস্ট)'] as $mv => $ml)
                                                        <option value="{{ $mv }}" @selected(($smtpSettings['mailer'] ?? 'smtp') === $mv)>{{ $ml }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="encryption" class="form-select sms-form-control" required aria-label="এনক্রিপশন">
                                                    @foreach(['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None'] as $ev => $el)
                                                        <option value="{{ $ev }}" @selected(($smtpSettings['encryption'] ?? 'tls') === $ev)>{{ $el }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 mt-4"><i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- MODAL 1: Create / Edit Template Modal --}}
<div class="modal fade" id="templateFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.sms.templates.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="templateId">

                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark">মেসেজ ও ইমেইল টেমপ্লেট</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold text-dark small">টেমপ্লেটের নাম / শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="templateTitle" class="form-control sms-form-control" required placeholder="যেমন: ঈদ অফার ডিসকাউন্ট">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small">ট্যাগ / ক্যাটাগরি</label>
                            <input type="text" name="tag" id="templateTag" class="form-control sms-form-control" placeholder="যেমন: Marketing, Billing, Grant">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">চ্যানেল <span class="text-danger">*</span></label>
                            <select name="channel" id="templateChannel" class="form-select sms-form-control" required>
                                <option value="both">উভয় মাধ্যম (SMS + Email)</option>
                                <option value="sms">শুধুমাত্র এসএমএস (SMS Only)</option>
                                <option value="email">শুধুমাত্র ইমেইল (Email Only)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">ইমেইল সাবজেক্ট (Email এর জন্য)</label>
                            <input type="text" name="subject" id="templateSubject" class="form-control sms-form-control" placeholder="ইমেইল সাবজেক্ট...">
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-dark small mb-0">মেসেজ কন্টেন্ট <span class="text-danger">*</span></label>
                                <div class="d-flex gap-1">
                                    <span class="shortcode-chip" onclick="insertShortcode('{name}', 'templateBody')">{name}</span>
                                    <span class="shortcode-chip" onclick="insertShortcode('{phone}', 'templateBody')">{phone}</span>
                                    <span class="shortcode-chip" onclick="insertShortcode('{role}', 'templateBody')">{role}</span>
                                </div>
                            </div>
                            <textarea name="body" id="templateBody" rows="5" class="form-control sms-form-control" required placeholder="টেমপ্লেট বার্তা লিখুন..."></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">বাটন টেক্সট (ঐচ্ছিক)</label>
                            <input type="text" name="action_text" id="templateActionText" class="form-control sms-form-control" placeholder="যেমন: বিস্তারিত দেখুন">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">বাটন লিংক (ঐচ্ছিক)</label>
                            <input type="url" name="action_url" id="templateActionUrl" class="form-control sms-form-control" placeholder="https://ideaabd.com/books">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: View Log Details Modal --}}
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark">ক্যাম্পেইন বিস্তারিত রেকর্ড</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-2.5">
                    <div class="d-flex justify-content-between"><span class="text-muted small">ক্যাম্পেইন আইডি:</span> <span class="font-monospace fw-bold" id="logDetailId"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">তারিখ ও সময়:</span> <span class="small font-monospace" id="logDetailDate"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">চ্যানেল:</span> <span class="badge bg-primary" id="logDetailChannel"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">টার্গেট গ্রুপ:</span> <span class="badge bg-light text-dark border" id="logDetailTarget"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">পরিসংখ্যান:</span> <span class="fw-semibold text-dark small" id="logDetailStats"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">প্রেরক অ্যাডমিন:</span> <span class="text-dark fw-medium small" id="logDetailAdmin"></span></div>
                    <hr class="my-1">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">ক্যাম্পেইন সাবজেক্ট / টাইটেল:</div>
                        <div class="fw-bold text-dark small" id="logDetailTitle"></div>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold mb-1">বার্তা প্রিভিউ:</div>
                        <div class="p-2.5 rounded-3 bg-light border small font-monospace" id="logDetailPreview" style="white-space: pre-line;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Initialize Global Recipient Counts and Templates Cache
    window.messagingCounts = @json($counts);
    window.emailCounts = @json($emailCounts);
    window.savedTemplates = @json($templates);
    window.smsCostPerSms = {{ (float) $guard['cost'] }};
    window.smsCampaigns = @json($liveCampaigns);
</script>
<script src="{{ asset('js/admin-sms.js') }}?v={{ @filemtime(public_path('js/admin-sms.js')) ?: 1 }}"></script>
@endpush
