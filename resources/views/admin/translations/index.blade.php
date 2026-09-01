@extends('layouts.admin')

@section('title', 'Multi-Language Translations & i18n')
@section('heading', 'Multi-Language Translations & Localization')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Translations & i18n</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addTranslationModal">
        <i class="fas fa-plus me-1"></i> Add Translation Key
    </button>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- KPI Summary Hero Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-language"></i></div>
                <p class="kpi__label">Total Translation Strings</p>
                <h3 class="kpi__value text-dark">{{ number_format($totalKeysCount) }}</h3>
                <p class="kpi__foot text-muted">Across all UI groups</p>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #16a34a;">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                <p class="kpi__label">English Translated</p>
                <h3 class="kpi__value text-dark">{{ number_format($translatedEnCount) }}</h3>
                <p class="kpi__foot text-muted">Global audience ready</p>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-percent"></i></div>
                <p class="kpi__label">Localization Completion</p>
                <h3 class="kpi__value text-dark">{{ $completionRate }}%</h3>
                <p class="kpi__foot text-muted">Translation coverage</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="adm-card p-3 bg-white">
        <form action="{{ route('admin.translations.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <select name="group" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Translation Groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>Group: {{ strtoupper($g) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search string key or translation text...">
                </div>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold">Filter</button>
                <a href="{{ route('admin.translations.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <!-- Translations Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-font me-2 text-primary"></i> Site Interface Strings</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Key / Identifier</th>
                            <th>Group</th>
                            <th>Bengali (বাংলা)</th>
                            <th>English (Global)</th>
                            <th>Arabic (العربية)</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($translations as $tr)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary small">{{ $tr->key }}</td>
                                <td><span class="badge bg-light text-dark border text-uppercase small">{{ $tr->group }}</span></td>
                                <td class="fw-semibold text-dark">{{ $tr->text_bn ?? '—' }}</td>
                                <td class="text-dark">{{ $tr->text_en ?? '—' }}</td>
                                <td class="text-muted small" dir="rtl">{{ $tr->text_ar ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5"
                                            onclick="openEditTranslationModal({{ $tr->id }}, '{{ $tr->key }}', '{{ addslashes($tr->text_bn ?? '') }}', '{{ addslashes($tr->text_en ?? '') }}', '{{ addslashes($tr->text_ar ?? '') }}')">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted small">No translation strings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="adm-card__foot p-3">
            {{ $translations->links() }}
        </div>
    </div>

</div>

<!-- Modal: Add Translation -->
<div class="modal fade" id="addTranslationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-plus-circle me-1.5"></i> Add Translation Key</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.translations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Group</label>
                            <input type="text" name="group" class="form-control" placeholder="e.g. site, checkout, reader" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Unique Key</label>
                            <input type="text" name="key" class="form-control font-monospace" placeholder="e.g. button_buy_now" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bengali (বাংলা)</label>
                        <input type="text" id="addTextBn" name="text_bn" class="form-control" placeholder="e.g. এখনই কিনুন" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">English (Global)</label>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 small" onclick="triggerAiTranslate('addTextBn', 'addTextEn')">
                                <i class="fas fa-wand-magic-sparkles me-1"></i> AI Auto-Translate
                            </button>
                        </div>
                        <input type="text" id="addTextEn" name="text_en" class="form-control" placeholder="e.g. Buy Now">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Arabic (العربية) (Optional)</label>
                        <input type="text" name="text_ar" class="form-control text-end" dir="rtl" placeholder="اشتري الآن">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Translation -->
<div class="modal fade" id="editTranslationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-edit me-1.5"></i> Edit Translation Text</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTranslationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Key</label>
                        <input type="text" id="editKey" class="form-control font-monospace bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bengali (বাংলা)</label>
                        <input type="text" id="editTextBn" name="text_bn" class="form-control">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">English (Global)</label>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 small" onclick="triggerAiTranslate('editTextBn', 'editTextEn')">
                                <i class="fas fa-wand-magic-sparkles me-1"></i> AI Auto-Translate
                            </button>
                        </div>
                        <input type="text" id="editTextEn" name="text_en" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Arabic (العربية)</label>
                        <input type="text" id="editTextAr" name="text_ar" class="form-control text-end" dir="rtl">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Text</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditTranslationModal(id, key, textBn, textEn, textAr) {
    document.getElementById('editTranslationForm').action = "/admin/translations/" + id;
    document.getElementById('editKey').value = key;
    document.getElementById('editTextBn').value = textBn;
    document.getElementById('editTextEn').value = textEn;
    document.getElementById('editTextAr').value = textAr;

    new bootstrap.Modal(document.getElementById('editTranslationModal')).show();
}

function triggerAiTranslate(sourceId, targetId) {
    const textBn = document.getElementById(sourceId).value;
    if (!textBn) {
        SwalToast('warning', 'অনুবাদ করতে প্রথমে বাংলা টেক্সট লিখুন!');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.translations.auto-translate') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ text_bn: textBn })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById(targetId).value = data.text_en;
            SwalToast('success', 'AI অনুবাদ সফলভাবে তৈরি হয়েছে (' + data.confidence + ')');
        }
    })
    .catch(() => SwalToast('error', 'AI translation error occurred'));
}
</script>
@endpush
@endsection
