@extends('layouts.admin')

@section('title', '৪০৪ ব্রোকেন লিংক মনিটর — Idea প্রকাশন')
@section('heading', '৪০৪ ব্রোকেন লিংক মনিটর')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">এসইও ম্যানেজার</a></li>
    <li class="breadcrumb-item active">৪০৪ ব্রোকেন লিংকস</li>
@endsection

@section('actions')
    <a href="{{ route('admin.seo.redirects') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i>৩০১ রিডাইরেক্ট</a>
    <a href="{{ route('admin.seo.index') }}" class="btn btn-light btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-left me-1"></i>এসইও</a>
@endsection

@push('styles')
<style>
    .bl-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .bl-stat { background: #fff; border: 1px solid #e6eaf2; border-radius: 14px; padding: 12px 14px; text-decoration: none; color: #0f172a; display: block; }
    .bl-stat:hover { border-color: #94a3b8; color: #0f172a; }
    .bl-stat.is-on { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .bl-stat span { display: block; font-size: 12px; font-weight: 600; color: #64748b; }
    .bl-stat strong { font-size: 22px; font-weight: 800; font-variant-numeric: tabular-nums; }
    .bl-card { background: #fff; border: 1px solid #e6eaf2; border-radius: 14px; overflow: hidden; }
    .bl-toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding: 12px 14px; border-bottom: 1px solid #eef1f7; }
    .bl-toolbar .form-control, .bl-toolbar .form-select { height: 36px; font-size: 13px; }
    .bl-tabs { display: inline-flex; gap: 2px; padding: 3px; background: #f1f5f9; border-radius: 10px; }
    .bl-tabs a { padding: 5px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #475569; text-decoration: none; }
    .bl-tabs a.is-on { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
    .bl-table { margin: 0; font-size: 13px; }
    .bl-table thead th { background: #f8fafc; color: #64748b; font-size: 11.5px; font-weight: 700; white-space: nowrap; padding: 9px 12px; border-bottom: 1px solid #e6eaf2; }
    .bl-table td { padding: 10px 12px; vertical-align: middle; border-color: #eef1f7; }
    .bl-url { font-family: Consolas, Menlo, monospace; font-size: 12.5px; color: #b91c1c; word-break: break-all; line-height: 1.4; }
    .bl-url a { color: inherit; text-decoration: none; }
    .bl-url a:hover { text-decoration: underline; }
    .bl-kind { font-size: 10.5px; font-weight: 700; padding: 1px 7px; border-radius: 999px; border: 1px solid; white-space: nowrap; }
    .bl-kind.k-page { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
    .bl-kind.k-image { color: #7c3aed; border-color: #ddd6fe; background: #f5f3ff; }
    .bl-kind.k-asset { color: #475569; border-color: #e2e8f0; background: #f8fafc; }
    .bl-hits { font-weight: 800; font-variant-numeric: tabular-nums; }
    .bl-hits.hot { color: #dc2626; }
    .bl-ref { color: #64748b; font-size: 12px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bl-fix { display: flex; gap: 6px; align-items: center; justify-content: flex-end; }
    .bl-fix .form-control { height: 32px; font-size: 12.5px; font-family: Consolas, Menlo, monospace; width: 210px; }
    .bl-fix .btn { height: 32px; font-size: 12.5px; white-space: nowrap; }
    .bl-hint { font-size: 11px; color: #059669; text-align: right; margin-top: 3px; }
    .bl-hint.muted { color: #94a3b8; }
    .bl-bulk { display: none; align-items: center; gap: 8px; padding: 8px 14px; background: #eff6ff; border-bottom: 1px solid #dbeafe; font-size: 13px; }
    .bl-bulk.is-on { display: flex; }
    @media (max-width: 991.98px) {
        .bl-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .bl-fix { flex-wrap: wrap; justify-content: flex-start; }
        .bl-fix .form-control { width: 100%; }
        .bl-hint { text-align: left; }
    }
    @media (max-width: 575.98px) { .bl-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    body.dark-mode .bl-stat, body.dark-mode .bl-card { background: #111a2c; border-color: #1f2a40; color: #e8edf6; }
    body.dark-mode .bl-table thead th { background: #0e1626; border-color: #1f2a40; }
    body.dark-mode .bl-tabs { background: #0e1626; }
    body.dark-mode .bl-tabs a.is-on { background: #1f2a40; color: #e8edf6; }
</style>
@endpush

@section('content')
@php
    $qsBase = array_filter(['tab' => $tab === 'resolved' ? 'resolved' : null, 'q' => $q ?: null, 'sort' => $sort === 'hits' ? 'hits' : null]);
    $kindUrl = fn ($k) => route('admin.seo.broken-links', array_filter($qsBase + ['kind' => $k ?: null]));
@endphp

<div class="bl-stats">
    <a href="{{ $kindUrl('') }}" class="bl-stat {{ $kind === '' ? 'is-on' : '' }}"><span>সক্রিয় ভাঙা লিংক</span><strong class="{{ $totalBroken ? 'text-danger' : 'text-success' }}">{{ number_format($totalBroken) }}</strong></a>
    <a href="{{ $kindUrl('page') }}" class="bl-stat {{ $kind === 'page' ? 'is-on' : '' }}"><span><i class="fa-regular fa-file-lines me-1"></i>পেজ</span><strong>{{ number_format($counts['page'] ?? 0) }}</strong></a>
    <a href="{{ $kindUrl('image') }}" class="bl-stat {{ $kind === 'image' ? 'is-on' : '' }}"><span><i class="fa-regular fa-image me-1"></i>ছবি</span><strong>{{ number_format($counts['image'] ?? 0) }}</strong></a>
    <a href="{{ $kindUrl('asset') }}" class="bl-stat {{ $kind === 'asset' ? 'is-on' : '' }}"><span><i class="fa-regular fa-file me-1"></i>অন্যান্য ফাইল</span><strong>{{ number_format($counts['asset'] ?? 0) }}</strong></a>
    <div class="bl-stat"><span>মোট ব্যর্থ ভিজিট</span><strong>{{ number_format($totalHits) }}</strong></div>
</div>

<div class="bl-card">
    <form method="GET" class="bl-toolbar">
        <div class="bl-tabs" role="tablist">
            <a href="{{ route('admin.seo.broken-links', array_filter(['kind' => $kind ?: null])) }}" class="{{ $tab === 'open' ? 'is-on' : '' }}">সক্রিয় ({{ number_format($counts['open']) }})</a>
            <a href="{{ route('admin.seo.broken-links', ['tab' => 'resolved']) }}" class="{{ $tab === 'resolved' ? 'is-on' : '' }}">সমাধানকৃত ({{ number_format($counts['resolved']) }})</a>
        </div>
        @if ($tab === 'resolved')<input type="hidden" name="tab" value="resolved">@endif
        @if ($kind)<input type="hidden" name="kind" value="{{ $kind }}">@endif
        <input type="search" name="q" value="{{ $q }}" class="form-control" style="max-width: 260px;" placeholder="লিংক বা রেফারার খুঁজুন">
        <select name="sort" class="form-select" style="max-width: 190px;" onchange="this.form.submit()">
            <option value="recent" @selected($sort === 'recent')>সর্বশেষ আগে</option>
            <option value="hits" @selected($sort === 'hits')>বেশি ভিজিট আগে</option>
        </select>
        <button class="btn btn-primary btn-sm px-3">খুঁজুন</button>
        @if ($q || $kind || $sort === 'hits')<a href="{{ route('admin.seo.broken-links', array_filter(['tab' => $tab === 'resolved' ? 'resolved' : null])) }}" class="btn btn-light btn-sm">রিসেট</a>@endif
        <span class="ms-auto small text-muted">ঠিকানা না মিললে ভিজিটর ৪০৪ পায়। ৩০১ দিলে সঠিক পেজে চলে যাবে।</span>
    </form>

    <form method="POST" action="{{ route('admin.seo.broken-links.bulk') }}" id="blBulkForm">
        @csrf
        <div class="bl-bulk" id="blBulk">
            <strong><span id="blCount">0</span>টি নির্বাচিত</strong>
            @if ($tab === 'open')
                <button name="action" value="ignore" class="btn btn-sm btn-outline-secondary">উপেক্ষা করুন</button>
            @else
                <button name="action" value="reopen" class="btn btn-sm btn-outline-primary">আবার খুলুন</button>
            @endif
            <button name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('নির্বাচিত লিংকগুলো মুছে ফেলবেন?')">মুছুন</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table bl-table align-middle">
            <thead>
                <tr>
                    <th style="width: 34px;"><input type="checkbox" class="form-check-input" id="blAll" aria-label="সব নির্বাচন"></th>
                    <th>ভাঙা লিংক</th>
                    <th class="text-end">ভিজিট</th>
                    <th>কোথা থেকে এসেছে</th>
                    <th>সর্বশেষ</th>
                    <th class="text-end" style="min-width: 330px;">{{ $tab === 'open' ? 'সমাধান' : 'অবস্থা' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brokenLinks as $link)
                    @php
                        $suggest = $tab === 'open' ? $link->suggestion() : null;
                        $kindKey = $link->kind;
                        $refHost = $link->referer ? (parse_url($link->referer, PHP_URL_HOST) ?: '') : '';
                        $refPath = $link->referer ? rawurldecode((string) (parse_url($link->referer, PHP_URL_PATH) ?: '/')) : '';
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check-input bl-pick" name="ids[]" value="{{ $link->id }}" form="blBulkForm" aria-label="নির্বাচন"></td>
                        <td>
                            <div class="bl-url"><a href="{{ url($link->url) }}" target="_blank" rel="noopener" title="নতুন ট্যাবে খুলে দেখুন">{{ \Illuminate\Support\Str::limit($link->display_url, 110) }}</a></div>
                            <span class="bl-kind k-{{ $kindKey }}">{{ $kinds[$kindKey] ?? $kindKey }}</span>
                        </td>
                        <td class="text-end"><span class="bl-hits {{ $link->hits_count >= 50 ? 'hot' : '' }}">{{ number_format($link->hits_count) }}</span></td>
                        <td>
                            @if ($link->referer)
                                <div class="bl-ref" title="{{ $link->referer }}">
                                    <a href="{{ $link->referer }}" target="_blank" rel="noopener noreferrer" class="text-reset">{{ $refHost }}{{ $refPath !== '/' ? $refPath : '' }}</a>
                                </div>
                            @else
                                <span class="bl-ref">সরাসরি / বট</span>
                            @endif
                        </td>
                        <td class="small text-muted text-nowrap" title="{{ $link->last_hit_at?->format('d M Y, h:i A') }}">{{ $link->last_hit_at?->diffForHumans() ?? '—' }}</td>
                        <td>
                            @if ($tab === 'open')
                                <form method="POST" action="{{ route('admin.seo.broken-links.resolve', $link->id) }}" class="bl-fix">
                                    @csrf
                                    <input type="text" name="target_url" class="form-control" list="blTargets" value="{{ $suggest }}"
                                           placeholder="{{ $kindKey === 'page' ? 'সঠিক লিংক, যেমন /books' : 'বিকল্প ফাইলের লিংক' }}" aria-label="সঠিক লিংক">
                                    <button class="btn btn-success btn-sm px-3" title="এই লিংকে আসা ভিজিটরকে সঠিক পেজে পাঠান (৩০১)"><i class="fa-solid fa-check me-1"></i>৩০১ ফিক্স</button>
                                    <button name="action" value="ignore" class="btn btn-outline-secondary btn-sm" formnovalidate title="রিডাইরেক্ট ছাড়া তালিকা থেকে সরান">উপেক্ষা</button>
                                </form>
                                @if ($suggest)
                                    <div class="bl-hint"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>প্রস্তাবিত লিংক বসানো আছে — যাচাই করে ফিক্স দিন</div>
                                @elseif ($kindKey !== 'page')
                                    <div class="bl-hint muted">ফাইলটি সার্ভারে নেই — ফাইল আপলোড করলে এটি নিজে থেকেই সমাধান হবে</div>
                                @endif
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">সমাধানকৃত</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-circle-check fs-2 text-success d-block mb-2 opacity-50"></i>
                            {{ $q || $kind ? 'এই ফিল্টারে কিছু পাওয়া যায়নি।' : ($tab === 'open' ? 'চমৎকার! এখন কোনো সক্রিয় ভাঙা লিংক নেই।' : 'এখনো কোনো লিংক সমাধান করা হয়নি।') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($brokenLinks->hasPages())
        <div class="border-top p-3 d-flex justify-content-center">{{ $brokenLinks->links() }}</div>
    @endif
</div>

<datalist id="blTargets">@foreach ($targets as $t)<option value="{{ $t }}">@endforeach</datalist>
@endsection

@push('scripts')
<script>
(function () {
    var all = document.getElementById('blAll');
    var bar = document.getElementById('blBulk');
    var count = document.getElementById('blCount');
    function picks() { return Array.prototype.slice.call(document.querySelectorAll('.bl-pick')); }
    function sync() {
        var n = picks().filter(function (c) { return c.checked; }).length;
        count.textContent = n;
        bar.classList.toggle('is-on', n > 0);
        if (all) all.checked = n > 0 && n === picks().length;
    }
    if (all) all.addEventListener('change', function () { picks().forEach(function (c) { c.checked = all.checked; }); sync(); });
    document.addEventListener('change', function (e) { if (e.target.classList && e.target.classList.contains('bl-pick')) sync(); });
    // A fix form must not be sent empty (the "ignore" button skips this).
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f.classList || !f.classList.contains('bl-fix')) return;
        var ignoring = e.submitter && e.submitter.value === 'ignore';
        var input = f.querySelector('[name="target_url"]');
        if (!ignoring && input && !input.value.trim()) { e.preventDefault(); input.focus(); input.classList.add('is-invalid'); }
    });
})();
</script>
@endpush
