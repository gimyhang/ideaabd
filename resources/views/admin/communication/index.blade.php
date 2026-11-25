@extends('layouts.admin')

@section('title', 'Inquiries')
@section('heading', 'যোগাযোগ বার্তা')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Inquiries</li>
@endsection

@section('actions')
    <a href="{{ route('admin.sms.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
        <i class="fa-solid fa-paper-plane me-1"></i> SMS / ইমেইল পাঠান
    </a>
@endsection

@php
    $P = \App\Http\Controllers\Admin\CommunicationAdminController::class;
    $tabs = ['' => ['সব', $counts['all']], 'new' => ['নতুন', $counts['new']], 'read' => ['পড়া হয়েছে', $counts['read']], 'replied' => ['উত্তর দেওয়া', $counts['replied']]];
    $badge = ['new' => ['iq-st--new', 'নতুন'], 'read' => ['iq-st--read', 'পড়া হয়েছে'], 'replied' => ['iq-st--done', 'উত্তর দেওয়া']];
    $wa = function (?string $p) { $d = preg_replace('/\D+/', '', (string) $p); if ($d === '') return null; if (str_starts_with($d, '01')) $d = '88' . $d; return 'https://wa.me/' . $d; };
@endphp

@section('content')
<div class="iq">
    <div class="row g-3 mb-3">
        @foreach([['fa-inbox', 'মোট বার্তা', $counts['all'], 'iq-k--a'], ['fa-envelope', 'নতুন', $counts['new'], 'iq-k--n'], ['fa-reply', 'উত্তর দেওয়া', $counts['replied'], 'iq-k--d'], ['fa-calendar-week', 'গত ৭ দিনে', $counts['week'], 'iq-k--w']] as [$ic, $lb, $v, $cls])
            <div class="col-6 col-lg-3">
                <div class="iq-kpi {{ $cls }}"><span class="iq-kpi__ic"><i class="fa-solid {{ $ic }}"></i></span>
                    <div><div class="iq-kpi__lb">{{ $lb }}</div><div class="iq-kpi__v">{{ number_format($v) }}</div></div></div>
            </div>
        @endforeach
    </div>

    <div class="iq-card mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="d-flex flex-wrap gap-1.5">
                @foreach($tabs as $k => [$lb, $n])
                    <a href="{{ route('admin.communication.index', array_filter(['status' => $k ?: null, 'search' => $search ?: null])) }}" class="iq-chip {{ ($status ?? '') === $k ? 'is-on' : '' }}">{{ $lb }} <span>{{ $n }}</span></a>
                @endforeach
            </div>
            <form method="GET" class="iq-search" role="search">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="নাম, ফোন, ইমেইল বা বার্তা…" aria-label="খুঁজুন">
            </form>
        </div>
    </div>

    @if(!$ready)
        <div class="alert alert-warning rounded-4">বার্তার টেবিল এখনো তৈরি হয়নি।</div>
    @elseif($inquiries->isEmpty())
        <div class="iq-card text-center py-5">
            <div class="iq-empty mb-3"><i class="fa-regular fa-envelope-open"></i></div>
            <h6 class="fw-bold mb-1">কোনো বার্তা নেই</h6>
            <p class="text-muted small mb-0">ওয়েবসাইটের "যোগাযোগ" ফর্ম থেকে কেউ বার্তা পাঠালে এখানে আসবে।</p>
        </div>
    @else
        <div class="iq-list">
            @foreach($inquiries as $log)
                @php $m = $P::parse($log); [$stCls, $stLb] = $badge[$m['status']] ?? ['iq-st--read', $m['status']]; $waUrl = $wa($m['phone']); @endphp
                <article class="iq-item {{ $m['status'] === 'new' ? 'is-new' : '' }}">
                    <div class="iq-item__av">{{ mb_substr($m['name'], 0, 1) }}</div>
                    <div class="iq-item__body">
                        <div class="iq-item__top">
                            <strong class="iq-item__name">{{ $m['name'] }}</strong>
                            <span class="iq-st {{ $stCls }}">{{ $stLb }}</span>
                            <time class="iq-item__time" datetime="{{ $log->created_at?->toIso8601String() }}" title="{{ $log->created_at?->format('d M Y, h:i A') }}">{{ $log->created_at?->diffForHumans() }}</time>
                        </div>
                        <div class="iq-item__subj">{{ $m['subject'] }}</div>
                        <p class="iq-item__msg" data-iq-msg>{{ $m['message'] }}</p>
                        <div class="iq-item__meta">
                            @if($m['phone'])<a href="tel:{{ $m['phone'] }}"><i class="fa-solid fa-phone"></i> {{ $m['phone'] }}</a>@endif
                            @if($m['email'])<a href="mailto:{{ $m['email'] }}?subject={{ rawurlencode('Re: ' . $m['subject']) }}"><i class="fa-regular fa-envelope"></i> {{ $m['email'] }}</a>@endif
                        </div>
                    </div>
                    <div class="iq-item__act">
                        @if($waUrl)<a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-pill px-2.5" title="WhatsApp-এ উত্তর"><i class="fa-brands fa-whatsapp"></i></a>@endif
                        @if($m['phone'])<a href="tel:{{ $m['phone'] }}" class="btn btn-sm btn-light border rounded-pill px-2.5" title="কল করুন"><i class="fa-solid fa-phone"></i></a>@endif
                        <form method="POST" action="{{ route('admin.communication.status', $log->id) }}" class="d-inline">
                            @csrf @method('PATCH')
                            @if($m['status'] === 'replied')
                                <input type="hidden" name="status" value="read"><button class="btn btn-sm btn-light border rounded-pill px-2.5" title="উত্তর-চিহ্ন সরান"><i class="fa-solid fa-rotate-left"></i></button>
                            @else
                                <input type="hidden" name="status" value="replied"><button class="btn btn-sm btn-primary rounded-pill px-2.5" title="উত্তর দেওয়া হয়েছে"><i class="fa-solid fa-check-double"></i><span class="d-none d-md-inline ms-1">উত্তর দেওয়া</span></button>
                            @endif
                        </form>
                        @if($m['status'] === 'new')
                            <form method="POST" action="{{ route('admin.communication.status', $log->id) }}" class="d-inline">
                                @csrf @method('PATCH')<input type="hidden" name="status" value="read">
                                <button class="btn btn-sm btn-light border rounded-pill px-2.5" title="পড়া হয়েছে"><i class="fa-regular fa-eye"></i></button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.communication.destroy', $log->id) }}" class="d-inline" data-confirm="এই বার্তাটি মুছে ফেলবেন?" data-confirm-icon="error">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light border rounded-pill px-2.5 text-danger" title="মুছুন"><i class="fa-regular fa-trash-can"></i></button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
        @if($inquiries->hasPages())
            <div class="mt-3">{{ $inquiries->links('pagination::bootstrap-5') }}</div>
        @endif
    @endif
</div>
@endsection

@push('styles')
<style>
.iq-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.08); padding: 14px 16px; }
.iq-kpi { display: flex; gap: 12px; align-items: center; background: #fff; border-radius: 16px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(15,23,42,.08); height: 100%; }
.iq-kpi__ic { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; font-size: 18px; flex-shrink: 0; }
.iq-k--a .iq-kpi__ic { background: #eff6ff; color: #2563eb; } .iq-k--n .iq-kpi__ic { background: #fef3c7; color: #b45309; }
.iq-k--d .iq-kpi__ic { background: #dcfce7; color: #16a34a; } .iq-k--w .iq-kpi__ic { background: #f3e8ff; color: #7c3aed; }
.iq-kpi__lb { font-size: 12px; color: #64748b; font-weight: 600; } .iq-kpi__v { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.iq-chip { display: inline-flex; gap: 6px; align-items: center; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 999px; border: 1px solid #e2e8f0; color: #475569; text-decoration: none; transition: all .15s; }
.iq-chip span { font-size: 11px; background: #f1f5f9; color: #475569; border-radius: 999px; padding: 0 7px; }
.iq-chip:hover { border-color: #cbd5e1; color: #0f172a; }
.iq-chip.is-on { background: #0f172a; border-color: #0f172a; color: #fff; } .iq-chip.is-on span { background: rgba(255,255,255,.18); color: #fff; }
.iq-search { position: relative; min-width: 260px; flex: 0 1 340px; }
.iq-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; }
.iq-search input { width: 100%; border: 1px solid #e2e8f0; border-radius: 999px; padding: 7px 14px 7px 34px; font-size: 14px; outline: none; }
.iq-search input:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
.iq-list { display: flex; flex-direction: column; gap: 10px; }
.iq-item { display: flex; gap: 14px; background: #fff; border-radius: 16px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(15,23,42,.08); border-left: 4px solid transparent; transition: box-shadow .15s, transform .15s; }
.iq-item:hover { box-shadow: 0 8px 22px rgba(15,23,42,.08); transform: translateY(-1px); }
.iq-item.is-new { border-left-color: #f59e0b; background: linear-gradient(90deg, #fffbeb 0, #fff 140px); }
.iq-item__av { width: 42px; height: 42px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: grid; place-items: center; font-weight: 800; flex-shrink: 0; }
.iq-item__body { flex: 1; min-width: 0; }
.iq-item__top { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.iq-item__name { color: #0f172a; }
.iq-item__time { margin-left: auto; font-size: 12px; color: #94a3b8; }
.iq-item__subj { font-weight: 600; font-size: 13.5px; color: #334155; margin-top: 2px; }
.iq-item__msg { margin: 4px 0 6px; font-size: 14px; color: #475569; white-space: pre-line; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer; }
.iq-item__msg.is-open { -webkit-line-clamp: unset; }
.iq-item__meta { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12.5px; }
.iq-item__meta a { color: #2563eb; text-decoration: none; } .iq-item__meta i { margin-right: 3px; }
.iq-item__act { display: flex; gap: 6px; align-items: flex-start; flex-shrink: 0; }
.iq-st { font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 999px; }
.iq-st--new { background: #fef3c7; color: #92400e; } .iq-st--read { background: #f1f5f9; color: #475569; } .iq-st--done { background: #dcfce7; color: #166534; }
.iq-empty { width: 72px; height: 72px; border-radius: 50%; background: #f1f5f9; display: inline-grid; place-items: center; font-size: 28px; color: #94a3b8; }
@media (max-width: 767.98px) {
    .iq-item { flex-wrap: wrap; }
    .iq-item__act { width: 100%; justify-content: flex-end; }
    .iq-search { min-width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('[data-iq-msg]').forEach(function (p) { p.addEventListener('click', function () { p.classList.toggle('is-open'); }); });
</script>
@endpush
