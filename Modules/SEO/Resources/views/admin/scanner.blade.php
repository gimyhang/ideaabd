@extends('layouts.admin')

@section('title', 'SEO Scanner')
@section('heading', 'Scanner')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">SEO</a></li>
    <li class="breadcrumb-item active">Scanner</li>
@endsection

@section('actions')
    <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Back</a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <form id="scanForm" class="card border-0 shadow-sm rounded-4 p-4" data-url="{{ route('admin.seo.scan-ajax') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold" for="scanType">Type</label>
                <select id="scanType" name="type" class="form-select">
                    @foreach (['book' => 'Book', 'blog' => 'Blog', 'author' => 'Author', 'publisher' => 'Publisher', 'ebook' => 'E-Book', 'webzine' => 'Webzine', 'url' => 'URL'] as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3" id="idBox">
                <label class="form-label fw-semibold" for="scanId">Record ID</label>
                <input id="scanId" name="id" type="number" min="1" class="form-control">
            </div>
            <div class="mb-3" id="pathBox" hidden>
                <label class="form-label fw-semibold" for="scanPath">Path</label>
                <input id="scanPath" name="url_path" class="form-control" value="/" placeholder="/books">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="scanKeyword">Focus keyword</label>
                <input id="scanKeyword" name="focus_keyword" class="form-control" maxlength="150">
            </div>
            <button class="btn btn-primary rounded-pill px-4" id="scanBtn">Scan</button>
        </form>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4" id="scanResult">
            <div class="text-muted">Pick a record or a URL and press Scan. The score and the generated meta data appear here.</div>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('scanForm');
    var out = document.getElementById('scanResult');
    var type = document.getElementById('scanType');
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };

    type.addEventListener('change', function () {
        var isUrl = type.value === 'url';
        document.getElementById('pathBox').hidden = !isUrl;
        document.getElementById('idBox').hidden = isUrl;
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('scanBtn');
        btn.disabled = true;
        out.innerHTML = '<div class="text-muted">Scanning…</div>';

        fetch(form.dataset.url, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (!res.ok || !res.j.success) {
                    out.innerHTML = '<div class="alert alert-danger mb-0">' + esc(res.j.message || 'Scan failed') + '</div>';
                    return;
                }
                var s = res.j.seo || {};
                var score = Number(s.seo_score || 0);
                var tone = score >= 80 ? 'success' : (score >= 50 ? 'warning' : 'danger');
                var rows = ['meta_title', 'meta_description', 'focus_keyword', 'canonical_url', 'og_title', 'og_description'].filter(function (k) { return s[k]; })
                    .map(function (k) { return '<tr><th class="text-muted fw-semibold" style="width: 38%">' + esc(k.replace(/_/g, ' ')) + '</th><td>' + esc(s[k]) + '</td></tr>'; }).join('');
                out.innerHTML = '<div class="d-flex align-items-center gap-3 mb-3"><div class="display-5 fw-bold text-' + tone + '">' + score + '</div><div class="text-muted">/ 100<br>' + esc(res.j.message || '') + '</div></div>' +
                    '<div class="table-responsive"><table class="table table-sm mb-0"><tbody>' + rows + '</tbody></table></div>';
            })
            .catch(function () { out.innerHTML = '<div class="alert alert-danger mb-0">Request failed</div>'; })
            .then(function () { btn.disabled = false; });
    });
})();
</script>
@endsection
