@extends('layouts.admin')

@section('title', 'Sitemap')
@section('heading', 'Sitemap')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">SEO</a></li>
    <li class="breadcrumb-item active">Sitemap</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold"><i class="fa-solid fa-arrow-left me-1"></i> SEO</a>
        <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-semibold" id="smPing"><i class="fa-solid fa-bolt me-1"></i> Ping</button>
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" id="smRebuild"><i class="fa-solid fa-rotate me-1"></i> Rebuild</button>
    </div>
@endsection

@push('styles')
<style>
    .sm-stat { border-radius: 16px; background: #fff; border: 1px solid #e5eaf2; padding: 16px 18px; height: 100%; }
    .sm-stat .lbl { font-size: 11px; letter-spacing: .4px; text-transform: uppercase; color: #64748b; font-weight: 700; }
    .sm-stat .num { font-size: 26px; font-weight: 800; line-height: 1.15; margin-top: 4px; }
    .sm-stat .num.flash { animation: sm-flash .5s; }
    @keyframes sm-flash { 40% { transform: scale(1.1); color: #047857; } }
    .sm-card { border-radius: 16px; background: #fff; border: 1px solid #e5eaf2; }
    .sm-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #64748b; background: #f8fafc; white-space: nowrap; }
    .sm-table td { vertical-align: middle; font-size: 13.5px; }
    .sm-loading { opacity: .5; pointer-events: none; transition: opacity .15s; }
    .sm-engine { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
    .sm-engine.ok { background: rgba(22,163,74,.12); color: #15803d; }
    .sm-engine.bad { background: rgba(220,38,38,.12); color: #b91c1c; }
    .sm-robots { background: #0f172a; color: #86efac; border-radius: 12px; padding: 14px 16px; font-size: 12.5px; line-height: 1.7; overflow-x: auto; margin: 0; }
    body.dark-mode .sm-stat, body.dark-mode .sm-card { background: #111a2c; border-color: #1f2b42; }
    body.dark-mode .sm-table th { background: #16213a; }
</style>
@endpush

@section('content')
@php
    $stats = $snapshot['stats'];
    $cards = [
        ['files', 'Files', number_format($stats['files'])],
        ['urls', 'URLs', number_format($stats['urls'])],
        ['updated', 'Updated', $stats['updated']],
    ];
@endphp
<div class="pb-4" id="smRoot"
     data-data-url="{{ route('admin.seo.sitemap.data') }}"
     data-rebuild-url="{{ route('admin.seo.sitemap.regenerate') }}"
     data-ping-url="{{ route('admin.seo.ping') }}">

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @foreach($cards as [$key, $label, $value])
            <div class="col-6 col-lg-3"><div class="sm-stat"><div class="lbl">{{ $label }}</div><div class="num" data-sm="{{ $key }}">{{ $value }}</div></div></div>
        @endforeach
        <div class="col-6 col-lg-3">
            <div class="sm-stat">
                <div class="lbl">Health</div>
                <div class="num" data-sm="health">
                    @if($stats['invalid'] > 0)<span class="text-danger">{{ $stats['invalid'] }} broken</span>
                    @elseif($stats['files'] === 0)<span class="text-warning">Empty</span>
                    @else<span class="text-success">Valid</span>@endif
                </div>
            </div>
        </div>
    </div>

    {{-- Files --}}
    <div class="sm-card mb-4 overflow-hidden">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-sitemap text-success me-2"></i>Files</h6>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ $sitemapUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open</a>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-copy="{{ $sitemapUrl }}"><i class="fa-solid fa-copy me-1"></i> Copy URL</button>
            </div>
        </div>
        <div class="table-responsive" id="smFiles">
            <table class="table table-hover align-middle mb-0 sm-table">
                <thead><tr><th class="ps-4">File</th><th>Kind</th><th class="text-end">URLs</th><th class="text-end">Size</th><th>Updated</th><th>Status</th><th class="text-end pe-4"></th></tr></thead>
                <tbody id="smRows">
                    @forelse($snapshot['files'] as $f)
                        <tr>
                            <td class="ps-4 font-monospace fw-semibold">{{ $f['name'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $f['kind'] }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($f['count']) }}</td>
                            <td class="text-end text-muted">{{ $f['size'] }} KB</td>
                            <td class="text-muted">{{ $f['modified'] }}</td>
                            <td>@if($f['valid'])<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Valid</span>@else<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Broken</span>@endif</td>
                            <td class="text-end pe-4">
                                <a href="{{ $f['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border" title="Open"><i class="fa-solid fa-eye"></i></a>
                                <button type="button" class="btn btn-sm btn-light border" data-copy="{{ $f['url'] }}" title="Copy URL"><i class="fa-solid fa-copy"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No sitemap files yet. Press <strong>Rebuild</strong>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        {{-- Ping --}}
        <div class="col-lg-5">
            <div class="sm-card p-4 h-100">
                <h6 class="fw-bold mb-1"><i class="fa-solid fa-bolt text-info me-2"></i>Ping</h6>
                <p class="text-muted small mb-3">Tell search engines the sitemap changed.</p>
                <div id="smPingResult" class="d-flex flex-wrap gap-2"><span class="text-muted small">No ping sent in this session.</span></div>

                <h6 class="fw-bold mt-4 mb-2">Submit</h6>
                <ol class="small text-muted ps-3 mb-0" style="line-height: 1.8;">
                    <li>Open <a href="https://search.google.com/search-console" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">Search Console</a> → <strong>Sitemaps</strong>.</li>
                    <li>Add <code>sitemap.xml</code> and press <strong>Submit</strong>.</li>
                    <li>Repeat in <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">Bing Webmaster</a>.</li>
                </ol>
            </div>
        </div>

        {{-- Robots --}}
        <div class="col-lg-7">
            <div class="sm-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-robot text-primary me-2"></i>Robots</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-copy-target="#smRobots"><i class="fa-solid fa-copy me-1"></i> Copy</button>
                        <a href="{{ $robotsUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open</a>
                    </div>
                </div>
                <pre class="sm-robots" id="smRobots">{{ $robotsContent }}</pre>
                <div class="d-flex flex-wrap gap-3 small mt-3">
                    <span class="{{ $stats['in_robots'] ? 'text-success' : 'text-danger' }}"><i class="fa-solid {{ $stats['in_robots'] ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>Sitemap line {{ $stats['in_robots'] ? 'present' : 'missing' }}</span>
                    <span class="text-muted"><i class="fa-solid fa-shield-halved me-1"></i>Admin, API, cart, checkout and account paths are blocked.</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.getElementById('smRoot');
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '{{ csrf_token() }}';
    var esc = function (v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };

    function toast(msg, bad) {
        var el = document.createElement('div');
        el.textContent = msg;
        el.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2000;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 12px 30px -10px rgba(0,0,0,.5);background:' + (bad ? '#dc2626' : '#0f172a');
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 2600);
    }

    function copy(text) {
        var done = function () { toast('Copied'); };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done, fallback); } else { fallback(); }
        function fallback() {
            var t = document.createElement('textarea'); t.value = text; t.style.cssText = 'position:fixed;opacity:0';
            document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) { toast('Could not copy', true); } t.remove();
        }
    }
    root.addEventListener('click', function (e) {
        var b = e.target.closest('[data-copy],[data-copy-target]'); if (!b) return;
        copy(b.dataset.copy || document.querySelector(b.dataset.copyTarget).textContent);
    });

    function render(snap) {
        var s = snap.stats;
        var set = function (k, v) {
            var el = root.querySelector('[data-sm="' + k + '"]');
            if (el && el.textContent.trim() !== String(v)) { el.textContent = v; el.classList.remove('flash'); void el.offsetWidth; el.classList.add('flash'); }
        };
        set('files', Number(s.files).toLocaleString());
        set('urls', Number(s.urls).toLocaleString());
        set('updated', s.updated);
        var h = root.querySelector('[data-sm="health"]');
        h.innerHTML = s.invalid > 0 ? '<span class="text-danger">' + s.invalid + ' broken</span>' : (s.files === 0 ? '<span class="text-warning">Empty</span>' : '<span class="text-success">Valid</span>');

        var rows = snap.files.map(function (f) {
            return '<tr><td class="ps-4 font-monospace fw-semibold">' + esc(f.name) + '</td>'
                + '<td><span class="badge bg-light text-dark border">' + esc(f.kind) + '</span></td>'
                + '<td class="text-end fw-bold">' + Number(f.count).toLocaleString() + '</td>'
                + '<td class="text-end text-muted">' + f.size + ' KB</td>'
                + '<td class="text-muted">' + esc(f.modified) + '</td>'
                + '<td>' + (f.valid ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Valid</span>' : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Broken</span>') + '</td>'
                + '<td class="text-end pe-4"><a href="' + esc(f.url) + '" target="_blank" rel="noopener" class="btn btn-sm btn-light border" title="Open"><i class="fa-solid fa-eye"></i></a> '
                + '<button type="button" class="btn btn-sm btn-light border" data-copy="' + esc(f.url) + '" title="Copy URL"><i class="fa-solid fa-copy"></i></button></td></tr>';
        }).join('');
        document.getElementById('smRows').innerHTML = rows || '<tr><td colspan="7" class="text-center py-5 text-muted">No sitemap files yet. Press <strong>Rebuild</strong>.</td></tr>';
    }

    function post(url, btn, busyHtml) {
        var original = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = busyHtml;
        return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .finally(function () { btn.disabled = false; btn.innerHTML = original; });
    }

    document.getElementById('smRebuild').addEventListener('click', function () {
        var files = document.getElementById('smFiles'); files.classList.add('sm-loading');
        post(root.dataset.rebuildUrl, this, '<i class="fa-solid fa-spinner fa-spin me-1"></i> Rebuilding').then(function (res) {
            if (res.ok && res.d.success) { render(res.d.snapshot); toast(res.d.message); } else { toast(res.d.message || 'Rebuild failed', true); }
        }).catch(function () { toast('Rebuild failed', true); }).finally(function () { files.classList.remove('sm-loading'); });
    });

    document.getElementById('smPing').addEventListener('click', function () {
        var out = document.getElementById('smPingResult');
        out.innerHTML = '<span class="text-muted small"><i class="fa-solid fa-spinner fa-spin me-1"></i>Sending…</span>';
        post(root.dataset.pingUrl, this, '<i class="fa-solid fa-spinner fa-spin me-1"></i> Pinging').then(function (res) {
            var names = { google: 'Google', bing: 'Bing', indexnow: 'IndexNow' };
            out.innerHTML = Object.keys(res.d.results || {}).map(function (k) {
                var r = res.d.results[k];
                return '<span class="sm-engine ' + (r.success ? 'ok' : 'bad') + '" title="' + esc(r.message) + '"><i class="fa-solid ' + (r.success ? 'fa-circle-check' : 'fa-circle-xmark') + '"></i>' + (names[k] || k) + ' · ' + esc(r.status) + '</span>';
            }).join('') || '<span class="text-danger small">Ping failed.</span>';
            toast(res.d.message || 'Ping finished', !res.d.success);
        }).catch(function () { out.innerHTML = '<span class="text-danger small">Ping failed.</span>'; toast('Ping failed', true); });
    });

    // keep the numbers fresh if another admin rebuilds the files
    setInterval(function () {
        if (document.hidden) return;
        fetch(root.dataset.dataUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; }).then(function (d) { if (d) render(d); }).catch(function () { });
    }, 60000);
})();
</script>
@endpush
