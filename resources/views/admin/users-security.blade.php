@extends('layouts.admin')

@section('title', 'Security')
@section('heading', 'Security')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Users</a></li>
    <li class="breadcrumb-item active">Security</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-security.css') }}?v={{ @filemtime(public_path('css/admin-security.css')) }}">
@endpush

@section('actions')
    <button type="button" class="sec-btn danger" data-bs-toggle="modal" data-bs-target="#manualBlockIpModal"><i class="fa-solid fa-ban"></i> Block IP</button>
    <button type="button" class="sec-btn" data-bs-toggle="modal" data-bs-target="#forceLogoutModal"><i class="fa-solid fa-right-from-bracket"></i> Force logout</button>
    <button type="button" class="sec-btn" data-bs-toggle="modal" data-bs-target="#quickOtpModal"><i class="fa-solid fa-shield-halved"></i> One-time password</button>
    <button type="button" class="sec-btn primary" onclick="openAutoPasswordModal()"><i class="fa-solid fa-key"></i> Password generator</button>
@endsection

@section('content')
@php
    $circ = 2 * M_PI * 54;
    $threatTotal = max(1, array_sum($threats));
    $tabs = [
        'requests' => ['label' => 'Reset requests', 'icon' => 'fa-key', 'badge' => $stats['pending_requests']],
        'ips'      => ['label' => 'IP addresses', 'icon' => 'fa-network-wired', 'badge' => $stats['total_blocked_ips']],
        'issues'   => ['label' => 'Flagged', 'icon' => 'fa-triangle-exclamation', 'badge' => $stats['security_issues']],
        'activity' => ['label' => 'Activity', 'icon' => 'fa-clock-rotate-left', 'badge' => 0],
    ];
    $activeTab = $tab === 'blocked' ? 'ips' : $tab;
    $iconFor = fn ($t) => match (true) {
        str_contains($t, 'unblock') => ['fa-lock-open', 'ok'],
        str_contains($t, 'block') => ['fa-ban', 'danger'],
        in_array($t, ['terminate_sessions', 'force_logout'], true) => ['fa-right-from-bracket', 'warn'],
        default => ['fa-key', ''],
    };
    $gradeLabel = ['strong' => 'Strong protection', 'fair' => 'Fairly protected', 'weak' => 'At risk'];
@endphp

<div class="sec d-flex flex-column gap-4" id="secRoot" data-live-url="{{ route('admin.users.security.live-stats') }}">

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-0" role="alert"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-0" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-0"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- One-time secrets --}}
    @if(session('success_generated_password'))
        @php $pData = session('success_generated_password'); @endphp
        <div class="sec-secret">
            <div>
                <div class="fw-bold mb-1"><i class="fa-solid fa-shield-check me-1 text-success"></i> New password generated</div>
                <div class="small opacity-75">{{ $pData['user_name'] }} · {{ $pData['user_email'] ?: $pData['user_phone'] }}@if($pData['force_change']) · must change at first login @endif</div>
                <div class="small mt-1 text-warning"><i class="fa-solid fa-eye-slash me-1"></i> Shown only once — copy it now</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <code>{{ $pData['password'] }}</code>
                <button type="button" class="sec-btn" onclick="copyText(@js($pData['password']), 'Password copied!')"><i class="fa-solid fa-copy"></i> Copy</button>
                @if(!empty($pData['user_phone']))
                    <a class="sec-btn ok" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pData['user_phone']) }}?text={{ urlencode('Hello ' . $pData['user_name'] . ', your new Idea Prokashon password is: ' . $pData['password'] . ' — please change it after signing in.') }}"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                @endif
            </div>
        </div>
    @endif

    @if(session('success_otp'))
        @php $otpData = session('success_otp'); @endphp
        <div class="sec-secret" style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
            <div>
                <div class="fw-bold mb-1"><i class="fa-solid fa-shield-halved me-1 text-warning"></i> One-time password generated</div>
                <div class="small opacity-75">{{ $otpData['user_name'] }} · {{ $otpData['user_email'] ?: $otpData['user_phone'] }} · expires {{ $otpData['expires_at'] }}</div>
                <div class="small mt-1 text-warning"><i class="fa-solid fa-eye-slash me-1"></i> Shown only once — copy it now</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <code>{{ $otpData['otp'] }}</code>
                <button type="button" class="sec-btn" onclick="copyText(@js($otpData['otp']), 'OTP copied!')"><i class="fa-solid fa-copy"></i> Copy</button>
                @if(!empty($otpData['user_phone']))
                    <a class="sec-btn ok" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $otpData['user_phone']) }}?text={{ urlencode('Hello ' . $otpData['user_name'] . ', your one-time password is: ' . $otpData['otp'] . ' (valid for 24 hours)') }}"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                @endif
            </div>
        </div>
    @endif

    {{-- Hero: score + checks --}}
    <div class="sec-hero">
        <div class="sec-card sec-card-pad sec-score">
            <div class="sec-ring" data-score="{{ $health['score'] }}" data-grade="{{ $health['grade'] }}">
                <svg viewBox="0 0 120 120" aria-hidden="true">
                    <circle class="track" cx="60" cy="60" r="54" fill="none" stroke-width="10"/>
                    <circle class="bar" cx="60" cy="60" r="54" fill="none" stroke-width="10" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}"/>
                </svg>
                <div class="sec-ring-num"><b>{{ $health['score'] }}</b><small>/ 100</small></div>
            </div>
            <div>
                <span class="sec-grade {{ $health['grade'] }}">
                    <i class="fa-solid {{ $health['grade'] === 'strong' ? 'fa-shield-check' : ($health['grade'] === 'fair' ? 'fa-shield-halved' : 'fa-shield-exclamation') }}"></i>
                    {{ $gradeLabel[$health['grade']] }}
                </span>
                <h5 class="fw-bold mb-1">Security score</h5>
                <p class="muted small mb-2">Calculated from site configuration and admin account checks.</p>
                <div class="sec-live" id="secLive"><span>Live</span></div>
            </div>
        </div>

        <div class="sec-card sec-card-pad">
            <div class="fw-bold mb-3">Protection checklist</div>
            <div class="sec-checks">
                @foreach($health['checks'] as $c)
                    <div class="sec-check {{ $c['state'] }}">
                        <div class="sec-dot"><i class="fa-solid {{ $c['state'] === 'ok' ? 'fa-check' : ($c['state'] === 'warn' ? 'fa-exclamation' : 'fa-xmark') }}"></i></div>
                        <div><b>{{ $c['label'] }}</b><span>{{ $c['detail'] }}</span></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="sec-kpis">
        @foreach([
            ['pending_requests', 'Pending requests', 'fa-envelope-open-text', '#d97706', 'Password reset help'],
            ['security_issues', 'Flagged', 'fa-triangle-exclamation', '#dc2626', '3+ failed attempts'],
            ['total_blocked_ips', 'Blocked IPs', 'fa-ban', '#7f1d1d', 'Auto or manual block'],
            ['locked_10min_ips', 'Temporarily locked', 'fa-clock', '#0891b2', '10-minute cooldown'],
            ['forced_changes', 'Password change due', 'fa-key', '#2563eb', 'Force-change enabled'],
        ] as [$key, $label, $icon, $color, $sub])
            <div class="sec-card sec-kpi" style="--kpi: {{ $color }}">
                <div class="lbl"><i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}</div>
                <div class="num" data-stat="{{ $key }}">{{ number_format($stats[$key]) }}</div>
                <div class="sub">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    {{-- Threat distribution --}}
    <div class="sec-card sec-card-pad">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold">IPs by threat level</div>
            <span class="muted small">{{ array_sum($threats) }} tracked</span>
        </div>
        <div class="sec-threat">
            <i class="c" style="width: {{ $threats['critical'] / $threatTotal * 100 }}%"></i>
            <i class="h" style="width: {{ $threats['high'] / $threatTotal * 100 }}%"></i>
            <i class="m" style="width: {{ $threats['medium'] / $threatTotal * 100 }}%"></i>
            <i class="l" style="width: {{ $threats['low'] / $threatTotal * 100 }}%"></i>
        </div>
        <div class="sec-legend">
            <span>● Critical <b data-threat="critical">{{ $threats['critical'] }}</b></span>
            <span>● High <b data-threat="high">{{ $threats['high'] }}</b></span>
            <span>● Medium <b data-threat="medium">{{ $threats['medium'] }}</b></span>
            <span>● Low <b data-threat="low">{{ $threats['low'] }}</b></span>
        </div>
    </div>

    {{-- Main panel --}}
    <div class="sec-card" style="overflow:hidden">
        <div class="sec-bar">
            <div class="sec-tabs">
                @foreach($tabs as $key => $t)
                    <a class="sec-tab {{ $activeTab === $key ? 'active' : '' }}" href="{{ route('admin.users.security.index', ['tab' => $key]) }}">
                        <i class="fa-solid {{ $t['icon'] }}"></i> {{ $t['label'] }}
                        @if($t['badge'] > 0)<span class="pill">{{ $t['badge'] }}</span>@endif
                    </a>
                @endforeach
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(in_array($tab, ['ips', 'issues', 'blocked'], true))
                    <a class="sec-btn" href="{{ route('admin.users.security.export', array_filter(['tab' => $tab, 'q' => $q, 'status' => $status])) }}"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
                @endif
                <form action="{{ route('admin.users.security.clean-expired') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="sec-btn" title="Delete expired temporary locks"><i class="fa-solid fa-broom"></i> Clean expired locks</button>
                </form>
            </div>
        </div>

        <form method="GET" id="secFilters" class="sec-bar" style="border-bottom:1px solid var(--sec-line)">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="sec-filters">
                <div class="sec-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $q }}" class="sec-input" placeholder="{{ $tab === 'requests' ? 'Name, email, phone or IP' : ($tab === 'activity' ? 'Description or IP' : 'IP or username') }}" autocomplete="off">
                </div>
                @if($tab === 'requests')
                    <select name="status" class="sec-select">
                        <option value="">All statuses</option>
                        @foreach(['pending' => 'Pending', 'resolved' => 'Resolved', 'rejected' => 'Rejected'] as $v => $l)
                            <option value="{{ $v }}" @selected($status === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                @elseif($tab !== 'activity')
                    <select name="status" class="sec-select">
                        <option value="">All states</option>
                        @foreach(['blocked' => 'Blocked', 'locked' => 'Locked', 'issue' => 'Flagged', 'critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $v => $l)
                            <option value="{{ $v }}" @selected($status === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                @endif
                @if($q !== '' || $status !== '')
                    <a href="{{ route('admin.users.security.index', ['tab' => $tab]) }}" class="sec-btn sm"><i class="fa-solid fa-xmark"></i> Reset</a>
                @endif
            </div>
            <span class="muted small">Your IP: <span class="mono">{{ $myIp }}</span></span>
        </form>

        {{-- ============ REQUESTS ============ --}}
        @if($tab === 'requests')
            <div class="sec-table-wrap">
                <table class="sec-table">
                    <thead><tr><th>User</th><th>Identity</th><th>Message</th><th>IP &amp; time</th><th class="c">Status</th><th class="r">Actions</th></tr></thead>
                    <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="sec-avatar">{{ mb_substr($req->user_name ?? ($req->user?->name ?? 'U'), 0, 1) }}</div>
                                    <div>
                                        <div class="fw-bold">{{ $req->user_name ?: ($req->user?->name ?? 'Unknown') }}</div>
                                        @if($req->user)<span class="sec-chip mute">{{ $req->user->role }}</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="mono fw-semibold">{{ $req->identity }}</td>
                            <td style="max-width:260px"><div class="muted small">{{ $req->reason_notes ?: 'Could not reset the password by link' }}</div>
                                @if($req->otp_code)<span class="sec-chip info mono mt-1">OTP {{ $req->otp_code }}</span>@endif
                            </td>
                            <td><div class="mono small">{{ $req->user_ip ?: '—' }}</div><div class="muted" style="font-size:11px" title="{{ $req->created_at }}">{{ $req->created_at->locale('en')->diffForHumans() }}</div></td>
                            <td class="c">
                                @if($req->status === 'resolved')<span class="sec-chip ok"><i class="fa-solid fa-circle-check"></i> OTP sent</span>
                                @elseif($req->status === 'rejected')<span class="sec-chip bad">Rejected</span>
                                @else<span class="sec-chip warn"><i class="fa-solid fa-hourglass-half"></i> Pending</span>@endif
                            </td>
                            <td class="r">
                                <div class="d-inline-flex gap-1">
                                    <form action="{{ route('admin.users.security.generate-otp') }}" method="POST">@csrf
                                        <input type="hidden" name="request_id" value="{{ $req->id }}">
                                        <button class="sec-btn sm"><i class="fa-solid fa-key"></i> OTP</button>
                                    </form>
                                    @if($req->user)
                                        <button type="button" class="sec-btn sm primary" onclick='openAutoPasswordModal(@json($req->user->id), @json($req->user->name), @json($req->identity))'><i class="fa-solid fa-lock"></i> Password</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="sec-empty"><i class="fa-solid fa-inbox"></i>No requests found.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())<div class="p-3 d-flex justify-content-between align-items-center"><span class="muted small">{{ $requests->total() }} total</span>{{ $requests->links() }}</div>@endif

        {{-- ============ ACTIVITY ============ --}}
        @elseif($tab === 'activity')
            @if($activity && $activity->count())
                <ul class="sec-timeline">
                    @foreach($activity as $a)
                        @php [$ic, $tone] = $iconFor($a->action_type); @endphp
                        <li>
                            <span class="dot {{ $tone }}"><i class="fa-solid {{ $ic }}"></i></span>
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <div>{{ $a->description }}</div>
                                <span class="muted small" title="{{ $a->created_at }}">{{ $a->created_at->locale('en')->diffForHumans() }}</span>
                            </div>
                            <div class="muted small mt-1"><i class="fa-solid fa-user-shield me-1"></i>{{ $a->user?->name ?? 'System' }} · <span class="mono">{{ $a->ip_address ?: '—' }}</span></div>
                        </li>
                    @endforeach
                </ul>
                @if($activity->hasPages())<div class="p-3">{{ $activity->links() }}</div>@endif
            @else
                <div class="sec-empty"><i class="fa-solid fa-clock-rotate-left" style="color:var(--sec-muted)"></i>No security activity yet.</div>
            @endif

        {{-- ============ IPS ============ --}}
        @else
            <div class="sec-table-wrap">
                <table class="sec-table">
                    <thead><tr>
                        <th style="width:36px"><input type="checkbox" id="secCheckAll" class="form-check-input" aria-label="Select all"></th>
                        <th>IP</th><th>Last username</th><th class="c">Failed attempts</th><th class="c">State</th><th>Last attempt</th><th class="r">Actions</th>
                    </tr></thead>
                    <tbody>
                    @forelse($blockedLogs as $log)
                        @php
                            $locked = !$log->is_blocked && $log->locked_until && \Carbon\Carbon::now()->lt($log->locked_until);
                            $att = (int) $log->attempt_count;
                            $lvl = $log->threat_level ?: 'low';
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="form-check-input sec-row-check" value="{{ $log->ip_address }}" @disabled($log->ip_address === $myIp) aria-label="{{ $log->ip_address }}"></td>
                            <td>
                                <div class="mono fw-bold">{{ $log->ip_address }}@if($log->ip_address === $myIp) <span class="sec-chip info">You</span>@endif</div>
                                @if($log->is_blocked && $log->block_reason)<div class="muted" style="font-size:11.5px;max-width:240px">{{ $log->block_reason }}</div>@endif
                            </td>
                            <td class="fw-semibold">{{ $log->last_username ?: '—' }}</td>
                            <td class="c">
                                <span class="sec-attempts" title="{{ $att }}">@for($i = 1; $i <= 5; $i++)<i class="{{ $i <= $att ? ($att >= 5 ? 'hot' : 'on') : '' }}"></i>@endfor</span>
                                <div class="muted" style="font-size:11px">{{ $att }}</div>
                            </td>
                            <td class="c">
                                @if($log->is_blocked)<span class="sec-chip bad"><i class="fa-solid fa-ban"></i> Blocked</span>
                                @elseif($locked)<span class="sec-chip warn"><i class="fa-solid fa-clock"></i> Locked · {{ $log->locked_until->locale('en')->diffForHumans(null, true) }}</span>
                                @elseif($log->is_security_issue)<span class="sec-chip bad"><i class="fa-solid fa-shield-halved"></i> Flagged</span>
                                @else<span class="sec-chip mute">Normal</span>@endif
                                <div class="mt-1"><span class="sec-chip {{ in_array($lvl, ['critical', 'high']) ? 'bad' : ($lvl === 'medium' ? 'warn' : 'mute') }}" style="font-size:10.5px">{{ strtoupper($lvl) }}</span></div>
                            </td>
                            <td><div class="small">{{ $log->updated_at->format('d M, h:i A') }}</div><div class="muted" style="font-size:11px">{{ $log->updated_at->locale('en')->diffForHumans() }}</div></td>
                            <td class="r">
                                <div class="d-inline-flex gap-1">
                                    @if($log->is_blocked || $locked || $log->is_security_issue)
                                        <form action="{{ route('admin.users.security.unblock-ip') }}" method="POST"
                                              data-confirm="Unblock and clean {{ $log->ip_address }}?" data-confirm-title="Unblock IP" data-confirm-icon="question"
                                              data-confirm-btn="Yes, unblock" data-cancel-btn="Cancel">@csrf
                                            <input type="hidden" name="ip_address" value="{{ $log->ip_address }}">
                                            <button class="sec-btn sm ok"><i class="fa-solid fa-lock-open"></i> Unblock</button>
                                        </form>
                                    @endif
                                    @if(!$log->is_blocked && $log->ip_address !== $myIp)
                                        <form action="{{ route('admin.users.security.block-ip') }}" method="POST"
                                              data-confirm="Block {{ $log->ip_address }}?" data-confirm-title="Block IP" data-confirm-icon="warning"
                                              data-confirm-btn="Yes, block" data-cancel-btn="Cancel">@csrf
                                            <input type="hidden" name="ip_address" value="{{ $log->ip_address }}">
                                            <button class="sec-btn sm danger icon" title="Block"><i class="fa-solid fa-ban"></i></button>
                                        </form>
                                    @endif
                                    @if($log->last_username)
                                        <button type="button" class="sec-btn sm icon" title="Password generator" onclick='openAutoPasswordModal("", "", @json($log->last_username))'><i class="fa-solid fa-key"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="sec-empty"><i class="fa-solid fa-shield-check"></i>No IP addresses found — all clear.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($blockedLogs->hasPages())<div class="p-3 d-flex justify-content-between align-items-center"><span class="muted small">{{ $blockedLogs->total() }} total</span>{{ $blockedLogs->links() }}</div>@endif
        @endif
    </div>

    <form id="secBulkForm" action="{{ route('admin.users.security.bulk') }}" method="POST" class="d-none">@csrf<input type="hidden" name="action" value=""></form>
    <div class="sec-bulk" id="secBulk">
        <b><span id="secBulkCount">0</span> selected</b>
        <button type="button" class="sec-btn sm ok" data-bulk="unblock"><i class="fa-solid fa-lock-open"></i> Unblock</button>
        <button type="button" class="sec-btn sm danger" data-bulk="block"><i class="fa-solid fa-ban"></i> Block</button>
    </div>

    {{-- ===== Modals ===== --}}
    <div class="modal fade" id="autoPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fa-solid fa-key text-warning me-2"></i>Password generator</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form action="{{ route('admin.users.security.auto-generate-password') }}" method="POST" autocomplete="off">@csrf
                    <input type="hidden" name="user_id" id="modalAutoPassUserId" data-pick-id>
                    <div class="modal-body">
                        <label class="form-label small fw-bold">User (name, email or phone)</label>
                        <div class="sec-pick mb-3">
                            <input type="text" name="identity" id="modalAutoPassIdentity" class="form-control mono" data-sec-pick="{{ route('admin.users.security.lookup') }}" placeholder="Start typing to search…" required>
                            <div class="sec-pick-list"></div>
                        </div>
                        <div class="d-flex justify-content-between"><label class="form-label small fw-bold mb-1">New password</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="generateRandomPassString()"><i class="fa-solid fa-rotate me-1"></i>Regenerate</button></div>
                        <div class="input-group">
                            <input type="text" name="custom_password" id="modalAutoPassString" class="form-control mono fw-bold" minlength="10" maxlength="100">
                            <button type="button" class="btn btn-outline-secondary" title="Copy" onclick="copyText(document.getElementById('modalAutoPassString').value, 'Password copied!')"><i class="fa-solid fa-copy"></i></button>
                        </div>
                        <div class="sec-meter" id="modalPassMeter"><i></i></div>
                        <div class="d-flex justify-content-between small muted mt-1"><span>At least 10 characters, with letters and numbers</span><b id="modalPassMeterLbl"></b></div>
                        <div class="form-check form-switch mt-3 p-3 rounded-3" style="background:var(--sec-soft)">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="force_change" value="1" id="forceChangeToggle" checked>
                            <label class="form-check-label small fw-semibold" for="forceChangeToggle">Require a password change at first login</label>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="sec-btn" data-bs-dismiss="modal">Cancel</button><button class="sec-btn primary"><i class="fa-solid fa-check"></i> Set password &amp; unblock</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickOtpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i>One-time password</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('admin.users.security.generate-otp') }}" method="POST" autocomplete="off">@csrf
                <input type="hidden" name="user_id" data-pick-id>
                <div class="modal-body">
                    <p class="muted small">Creates an OTP valid for 24 hours. The user must set a new password at their next login.</p>
                    <div class="sec-pick"><input type="text" name="identity" class="form-control mono" data-sec-pick="{{ route('admin.users.security.lookup') }}" placeholder="Email, phone or name" required><div class="sec-pick-list"></div></div>
                </div>
                <div class="modal-footer"><button type="button" class="sec-btn" data-bs-dismiss="modal">Cancel</button><button class="sec-btn primary">Generate OTP</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="forceLogoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fa-solid fa-right-from-bracket text-warning me-2"></i>Force logout</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('admin.users.security.force-logout') }}" method="POST" autocomplete="off">@csrf
                <div class="modal-body">
                    <p class="muted small">Signs the selected user out of every device and "remember me" session. Use it if an account may be compromised.</p>
                    <div class="sec-pick"><input type="text" class="form-control" data-sec-pick="{{ route('admin.users.security.lookup') }}" placeholder="Search and pick a user" required><input type="hidden" name="user_id" data-pick-id><div class="sec-pick-list"></div></div>
                    <small class="muted">Pick the user from the list.</small>
                </div>
                <div class="modal-footer"><button type="button" class="sec-btn" data-bs-dismiss="modal">Cancel</button><button class="sec-btn danger"><i class="fa-solid fa-right-from-bracket"></i> Sign out everywhere</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="manualBlockIpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><h6 class="modal-title fw-bold text-danger"><i class="fa-solid fa-ban me-2"></i>Block IP</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('admin.users.security.block-ip') }}" method="POST">@csrf
                <div class="modal-body">
                    <label class="form-label small fw-bold">IP address (IPv4 or IPv6)</label>
                    <input type="text" name="ip_address" class="form-control mono mb-3" placeholder="103.205.71.12" required>
                    <label class="form-label small fw-bold">Reason (optional)</label>
                    <input type="text" name="block_reason" class="form-control" maxlength="255" placeholder="Suspicious activity">
                    <div class="small muted mt-2"><i class="fa-solid fa-circle-info me-1"></i> Your current IP ({{ $myIp }}) cannot be blocked.</div>
                </div>
                <div class="modal-footer"><button type="button" class="sec-btn" data-bs-dismiss="modal">Cancel</button><button class="sec-btn danger">Block IP</button></div>
            </form>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-security.js') }}?v={{ @filemtime(public_path('js/admin-security.js')) }}" defer></script>
@endpush
