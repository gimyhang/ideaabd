{{-- Balance, usage and the SMS transaction history --}}
@php
    $statusMap = [
        'sent'     => ['সফল', 'success'],
        'failed'   => ['ব্যর্থ', 'danger'],
        'pending'  => ['অপেক্ষমাণ', 'secondary'],
        'blocked'  => ['সীমিত', 'warning'],
        'rejected' => ['বাদ', 'dark'],
    ];
    $reasonMap = [
        'fake_number'    => 'ভুয়া নম্বর',
        'invalid_number' => 'অবৈধ নম্বর',
        'restricted'     => '২৪ ঘণ্টার সীমা',
        'daily_limit'    => 'দৈনিক সীমা',
        'welcome_sent'   => 'ওয়েলকাম আগেই গেছে',
        'order_sent'     => 'অর্ডারের মেসেজ আগেই গেছে',
        'number_blocked' => 'ওটিপি ব্লক',
        'gateway_error'  => 'গেটওয়ে সমস্যা',
    ];
    $purposeMap = ['otp' => 'ওটিপি', 'order' => 'অর্ডার', 'welcome' => 'ওয়েলকাম', 'notification' => 'নোটিফিকেশন', 'campaign' => 'ক্যাম্পেইন', 'test' => 'টেস্ট'];
    $localPhone = fn ($p) => str_starts_with($p, '880') ? '0' . substr($p, 3) : '+' . $p;
    $balance = $balanceInfo['balance'] ?? null;
    $lowBalance = $balance !== null && $balance < 100;
@endphp

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-4">
        <div class="sms-panel sms-balance-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <span class="sms-label">গেটওয়ে ব্যালান্স</span>
                <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="refreshSmsBalance()" title="রিফ্রেশ">
                    <i class="fa-solid fa-rotate-right" id="refreshIconCard"></i>
                </button>
            </div>

            @if($balance !== null)
                <div class="sms-balance-num" data-sms-balance>{{ number_format($balance) }}</div>
                <div class="small text-muted" data-sms-balance-status>
                    @if($lowBalance)
                        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> ব্যালান্স কম, শীঘ্র রিচার্জ করুন
                    @else
                        <i class="fa-solid fa-circle-check text-success me-1"></i> সক্রিয় ({{ strtoupper($credentials['provider'] ?? '') }})
                    @endif
                </div>
            @else
                <div class="sms-balance-num text-muted" data-sms-balance>—</div>
                <div class="small text-danger" data-sms-balance-status>
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $balanceInfo['error'] ?? 'ব্যালান্স পাওয়া যায়নি' }}
                </div>
            @endif

            @if(!empty($balanceInfo['checked_at']))
                <div class="small text-muted mt-2">সর্বশেষ চেক: {{ \Illuminate\Support\Carbon::parse($balanceInfo['checked_at'])->format('d M, h:i A') }}</div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="row g-3 h-100">
            @foreach([
                ['আজ পাঠানো', $stats ? number_format($stats['sent_today']) : '—', 'SMS পার্ট', 'text-primary'],
                ['এ মাসে পাঠানো', $stats ? number_format($stats['sent_month']) : '—', 'SMS পার্ট', 'text-dark'],
                ['এ মাসের খরচ', $stats ? '৳' . number_format($stats['cost_month'], 2) : '—', 'আনুমানিক (৳' . $guard['cost'] . '/SMS)', 'text-success'],
                ['আজ ব্যর্থ / বাদ', $stats ? number_format($stats['failed_today']) . ' / ' . number_format($stats['refused_today']) : '—', 'গেটওয়ে / ভুয়া-সীমিত', 'text-danger'],
            ] as [$label, $value, $hint, $tone])
                <div class="col-6 col-md-3 col-lg-6 col-xl-3">
                    <div class="sms-panel sms-kpi h-100">
                        <span class="sms-label">{{ $label }}</span>
                        <div class="sms-kpi-value {{ $tone }}">{{ $value }}</div>
                        <div class="small text-muted">{{ $hint }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@unless($tablesReady)
    <div class="alert alert-warning rounded-3">
        <i class="fa-solid fa-database me-1"></i> ট্রানজেকশন হিস্টোরির টেবিল এখনো তৈরি হয়নি। সার্ভারে <code>php artisan migrate</code> চালান।
    </div>
@else
    {{-- Transaction history --}}
    <div class="sms-panel mb-4">
        <div class="sms-panel-head">
            <div>
                <h5 class="mb-0 fw-bold">ট্রানজেকশন হিস্টোরি</h5>
                <div class="small text-muted">প্রতিটি নম্বরে পাঠানো (বা আটকানো) এসএমএসের রেকর্ড। ওটিপি কোড সংরক্ষণ করা হয় না।</div>
            </div>
            <a href="{{ route('admin.sms.transactions.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-download me-1"></i> CSV
            </a>
        </div>

        <form method="GET" action="{{ route('admin.sms.index') }}#tab-balance" class="sms-filters">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="নম্বর খুঁজুন">
            <select name="status" class="form-select form-select-sm">
                <option value="">সব স্ট্যাটাস</option>
                @foreach($statusMap as $k => [$label])
                    <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="purpose" class="form-select form-select-sm">
                <option value="">সব ধরন</option>
                @foreach($purposeMap as $k => $label)
                    <option value="{{ $k }}" @selected(($filters['purpose'] ?? '') === $k)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm" title="শুরুর তারিখ">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm" title="শেষ তারিখ">
            <button class="btn btn-sm btn-primary rounded-pill px-3" type="submit">ফিল্টার</button>
            @if(array_filter($filters ?? []))
                <a href="{{ route('admin.sms.index') }}#tab-balance" class="btn btn-sm btn-link text-muted">রিসেট</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table sms-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>সময়</th>
                        <th>নম্বর</th>
                        <th>ধরন</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">পার্ট</th>
                        <th class="text-end">খরচ</th>
                        <th>মেসেজ / ফলাফল</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php [$statusLabel, $statusTone] = $statusMap[$tx->status] ?? [$tx->status, 'secondary']; @endphp
                        <tr>
                            <td class="text-nowrap small text-muted">{{ $tx->created_at->format('d M, h:i A') }}</td>
                            <td class="font-monospace text-nowrap">{{ $localPhone($tx->phone) }}</td>
                            <td>
                                <span class="small">{{ $purposeMap[$tx->purpose] ?? $tx->purpose }}</span>
                                <span class="d-block text-muted sms-xs">{{ $tx->source === 'admin' ? 'অ্যাডমিন' : 'সাইট' }}</span>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-bg-{{ $statusTone }}">{{ $statusLabel }}</span>
                                @if($tx->reason)
                                    <span class="d-block text-muted sms-xs">{{ $reasonMap[$tx->reason] ?? $tx->reason }}</span>
                                @endif
                            </td>
                            <td class="text-end">{{ $tx->status === 'sent' ? $tx->segments : '—' }}</td>
                            <td class="text-end">{{ $tx->status === 'sent' ? '৳' . number_format($tx->cost, 2) : '—' }}</td>
                            <td class="sms-msg-cell">
                                <div class="text-truncate" title="{{ $tx->message }}">{{ $tx->message }}</div>
                                @if($tx->gateway_response && $tx->status !== 'sent')
                                    <div class="text-danger sms-xs text-truncate" title="{{ $tx->gateway_response }}">{{ $tx->gateway_response }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">কোনো রেকর্ড পাওয়া যায়নি।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-3 border-top">{{ $transactions->fragment('tab-balance')->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>

    {{-- Balance changes seen at the gateway --}}
    <div class="sms-panel">
        <div class="sms-panel-head">
            <div>
                <h5 class="mb-0 fw-bold">রিচার্জ ও ব্যালান্স পরিবর্তন</h5>
                <div class="small text-muted">গেটওয়ের ব্যালান্স বাড়লে রিচার্জ, কমলে ব্যবহার হিসেবে স্বয়ংক্রিয়ভাবে রেকর্ড হয়।</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table sms-table align-middle mb-0">
                <thead>
                    <tr><th>সময়</th><th>ধরন</th><th class="text-end">পরিবর্তন</th><th class="text-end">ব্যালান্স</th></tr>
                </thead>
                <tbody>
                    @forelse($balanceHistory as $snap)
                        <tr>
                            <td class="small text-muted text-nowrap">{{ $snap->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                @if($snap->type === 'topup')
                                    <span class="badge rounded-pill text-bg-success">রিচার্জ</span>
                                @elseif($snap->type === 'initial')
                                    <span class="badge rounded-pill text-bg-secondary">শুরুর ব্যালান্স</span>
                                @else
                                    <span class="badge rounded-pill text-bg-light border">ব্যবহার</span>
                                @endif
                            </td>
                            <td class="text-end {{ $snap->delta > 0 ? 'text-success' : 'text-muted' }}">{{ $snap->type === 'initial' ? '—' : ($snap->delta > 0 ? '+' : '') . number_format($snap->delta) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($snap->balance) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">ব্যালান্স চেক করলে এখানে ইতিহাস জমা হবে।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endunless
