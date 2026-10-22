@extends('layouts.admin')

@section('title', 'Genres')
@section('heading', 'Genres')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.books') }}">Books</a></li>
    <li class="breadcrumb-item active">Genres</li>
@endsection

@section('content')
<div id="genreRoot" class="d-flex flex-column gap-4"
     data-store="{{ route('admin.genres.store') }}"
     data-base="{{ url('/admin/genres') }}"
     data-reorder="{{ route('admin.genres.reorder') }}">

    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-0">Genre list</h5>
                <small class="text-muted">One list for the book form, the publisher portal and validation. Changes apply everywhere at once.</small>
            </div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2" id="genreCount">{{ $genres->where('is_active', true)->count() }} active</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" id="genreTable">
                <thead class="table-light">
                    <tr class="small text-uppercase text-muted">
                        <th style="width: 70px;">Order</th>
                        <th>Slug</th>
                        <th>English</th>
                        <th>Bangla</th>
                        <th class="text-center" style="width: 90px;">Books</th>
                        <th class="text-center" style="width: 90px;">Active</th>
                        <th class="text-end" style="width: 70px;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($genres as $g)
                    <tr data-id="{{ $g->id }}">
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" data-move="-1" title="Up"><i class="fa-solid fa-arrow-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary" data-move="1" title="Down"><i class="fa-solid fa-arrow-down"></i></button>
                            </div>
                        </td>
                        <td><code>{{ $g->slug }}</code></td>
                        <td><input type="text" class="form-control form-control-sm" data-field="name_en" value="{{ $g->name_en }}" maxlength="150"></td>
                        <td><input type="text" class="form-control form-control-sm" data-field="name_bn" value="{{ $g->name_bn }}" maxlength="150"></td>
                        <td class="text-center"><span class="badge bg-light text-dark border" data-books>{{ $g->books_count }}</span></td>
                        <td class="text-center"><div class="form-check form-switch d-inline-block m-0"><input class="form-check-input" type="checkbox" data-field="is_active" @checked($g->is_active)></div></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-delete title="{{ $g->books_count ? 'In use — switch off instead' : 'Delete' }}" @disabled($g->books_count > 0)><i class="fa-solid fa-trash-can"></i></button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($orphans->isNotEmpty())
            <div class="alert alert-warning rounded-3 mt-3 mb-0 small">
                <b>Not in the list:</b>
                @foreach($orphans as $slug => $n)<code>{{ $slug }}</code> ({{ $n }})@if(!$loop->last), @endif @endforeach
                — books keep these values, but they are not offered in forms. Add a genre with the same slug to manage them.
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
        <h6 class="fw-bold mb-3">Add genre</h6>
        <form id="genreAdd" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small fw-bold">Slug</label><input type="text" name="slug" class="form-control" placeholder="crime_thriller" pattern="[a-z0-9_]+" maxlength="100" required></div>
            <div class="col-md-4"><label class="form-label small fw-bold">English</label><input type="text" name="name_en" class="form-control" maxlength="150" required></div>
            <div class="col-md-3"><label class="form-label small fw-bold">Bangla</label><input type="text" name="name_bn" class="form-control" maxlength="150"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-plus me-1"></i> Add</button></div>
        </form>
        <div class="small text-muted mt-2">The slug is permanent (books store it). Lowercase letters, numbers and underscores only.</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.getElementById('genreRoot'), tbody = root.querySelector('tbody');
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '{{ csrf_token() }}';
    var H = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' };

    function toast(msg, bad) {
        var el = document.createElement('div'); el.textContent = msg;
        el.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2000;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;background:' + (bad ? '#dc2626' : '#0f172a');
        document.body.appendChild(el); setTimeout(function () { el.remove(); }, bad ? 4500 : 2200);
    }
    function call(method, url, body) {
        return fetch(url, { method: method, headers: H, body: body ? JSON.stringify(body) : undefined })
            .then(function (r) { return r.json().then(function (d) { if (!r.ok) { var first = d.errors ? Object.values(d.errors)[0][0] : d.message; throw new Error(first || 'Request failed'); } return d; }); });
    }
    function recount() { document.getElementById('genreCount').textContent = tbody.querySelectorAll('[data-field="is_active"]:checked').length + ' active'; }

    // inline edit: save a field as soon as it changes
    tbody.addEventListener('change', function (e) {
        var f = e.target.dataset.field; if (!f) return;
        var tr = e.target.closest('tr'), val = f === 'is_active' ? e.target.checked : e.target.value.trim();
        if (f === 'name_en' && !val) { toast('English name is required.', true); return; }
        call('PUT', root.dataset.base + '/' + tr.dataset.id, (function (o) { o[f] = val; return o; })({}))
            .then(function (d) { toast(d.message); recount(); })
            .catch(function (err) { toast(err.message, true); if (f === 'is_active') e.target.checked = !e.target.checked; });
    });

    // reorder
    tbody.addEventListener('click', function (e) {
        var mv = e.target.closest('[data-move]'), del = e.target.closest('[data-delete]');
        if (mv) {
            var tr = mv.closest('tr'), dir = Number(mv.dataset.move), sib = dir < 0 ? tr.previousElementSibling : tr.nextElementSibling;
            if (!sib) return;
            dir < 0 ? tbody.insertBefore(tr, sib) : tbody.insertBefore(sib, tr);
            call('POST', root.dataset.reorder, { ids: Array.prototype.map.call(tbody.children, function (r) { return Number(r.dataset.id); }) })
                .then(function (d) { toast(d.message); }).catch(function (err) { toast(err.message, true); });
        }
        if (del && !del.disabled) {
            var row = del.closest('tr');
            if (!confirm('Delete "' + row.querySelector('code').textContent + '"?')) return;
            call('DELETE', root.dataset.base + '/' + row.dataset.id)
                .then(function (d) { row.remove(); toast(d.message); recount(); })
                .catch(function (err) { toast(err.message, true); });
        }
    });

    // add
    document.getElementById('genreAdd').addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target, body = { slug: f.slug.value.trim(), name_en: f.name_en.value.trim(), name_bn: f.name_bn.value.trim() || null };
        call('POST', root.dataset.store, body).then(function (d) { toast(d.message); setTimeout(function () { location.reload(); }, 500); })
            .catch(function (err) { toast(err.message, true); });
    });
})();
</script>
@endpush
