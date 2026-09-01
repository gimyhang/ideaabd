@extends('layouts.admin')

@section('title', 'Global Communication & Automation')
@section('heading', 'Omnichannel Communication & Automated Dispatch')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Communication Hub</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- KPI Summary Hero Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-paper-plane"></i></div>
                <p class="kpi__label">Messages Dispatched</p>
                <h3 class="kpi__value text-dark">{{ number_format($totalSentCount + $totalDeliveredCount) }}</h3>
                <p class="kpi__foot text-muted">Worldwide emails & WhatsApp</p>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #16a34a;">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                <p class="kpi__label">Delivery Success Rate</p>
                <h3 class="kpi__value text-dark">99.4%</h3>
                <p class="kpi__foot text-muted">Amazon SES / Cloudflare / Meta</p>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-cart-shopping"></i></div>
                <p class="kpi__label">Abandoned Cart Recoveries</p>
                <h3 class="kpi__value text-dark">28.5%</h3>
                <p class="kpi__foot text-muted">Automated 24h win-back rate</p>
            </div>
        </div>
    </div>

    <!-- Active Communication Templates -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-robot me-2 text-primary"></i> Automated Transactional & Recovery Triggers</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Trigger Event</th>
                            <th>Channel</th>
                            <th>Template Name & Subject</th>
                            <th>Content Preview</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $tmpl)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border font-monospace small">
                                        {{ $tmpl->trigger_event }}
                                    </span>
                                </td>
                                <td>
                                    @if($tmpl->channel === 'email')
                                        <span class="badge bg-primary-subtle text-primary border"><i class="fas fa-envelope me-1"></i> Email</span>
                                    @elseif($tmpl->channel === 'whatsapp')
                                        <span class="badge bg-success-subtle text-success border"><i class="fab fa-whatsapp me-1"></i> WhatsApp</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-bell me-1"></i> Push</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tmpl->name }}</div>
                                    <small class="text-muted">{{ $tmpl->subject ?? 'No Subject Line' }}</small>
                                </td>
                                <td class="small text-muted text-truncate" style="max-width: 250px;">
                                    {{ $tmpl->content_template }}
                                </td>
                                <td>
                                    @if($tmpl->is_active)
                                        <span class="pill pill--ok"><i class="fas fa-check"></i> Live</span>
                                    @else
                                        <span class="pill pill--pending">Paused</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary rounded-pill px-2.5 py-0.5"
                                                onclick="openEditTemplateModal({{ $tmpl->id }}, '{{ addslashes($tmpl->name) }}', '{{ addslashes($tmpl->subject ?? '') }}', '{{ addslashes($tmpl->content_template) }}', {{ $tmpl->is_active ? 1 : 0 }})">
                                            <i class="fas fa-pen"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-2.5 py-0.5 ms-1"
                                                onclick="openTestSendModal({{ $tmpl->id }}, '{{ addslashes($tmpl->name) }}', '{{ $tmpl->channel }}')">
                                            <i class="fas fa-paper-plane"></i> Test
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Dispatch Logs -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-clock-rotate-left me-2 text-primary"></i> Live Dispatch Logs</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Recipient</th>
                            <th>Channel</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-3 fw-semibold text-dark">{{ $log->recipient }}</td>
                                <td><span class="badge bg-light text-dark border text-uppercase">{{ $log->channel }}</span></td>
                                <td class="font-monospace small">{{ $log->trigger_event }}</td>
                                <td>
                                    <span class="pill {{ $log->status === 'delivered' ? 'pill--ok' : 'pill--info' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted small">No message logs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Edit Template -->
<div class="modal fade" id="editTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-edit me-1.5"></i> Edit Automation Template</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTemplateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Template Name</label>
                        <h6 class="fw-bold text-dark" id="editTmplName">—</h6>
                    </div>
                    <div class="mb-3" id="subjectFieldWrapper">
                        <label class="form-label small fw-semibold">Email Subject Line</label>
                        <input type="text" id="editTmplSubject" name="subject" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Content Template (Supports @{{placeholders}})</label>
                        <textarea id="editTmplContent" name="content_template" rows="4" class="form-control font-monospace small" required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editTmplActive">
                        <label class="form-check-label small" for="editTmplActive">Trigger active for live customer events</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Test Send -->
<div class="modal fade" id="testSendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-paper-plane me-1.5"></i> Send Test Message</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.communication.test-send') }}" method="POST">
                @csrf
                <input type="hidden" id="testTmplId" name="template_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Testing Template</label>
                        <h6 class="fw-bold text-dark" id="testTmplName">—</h6>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" id="testRecipientLabel">Recipient (Email or Phone)</label>
                        <input type="text" name="recipient" class="form-control" placeholder="e.g. user@example.com or +8801558712810" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Dispatch Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditTemplateModal(id, name, subject, content, isActive) {
    document.getElementById('editTemplateForm').action = "/admin/communication/templates/" + id;
    document.getElementById('editTmplName').textContent = name;
    document.getElementById('editTmplSubject').value = subject;
    document.getElementById('editTmplContent').value = content;
    document.getElementById('editTmplActive').checked = isActive === 1;

    new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
}

function openTestSendModal(id, name, channel) {
    document.getElementById('testTmplId').value = id;
    document.getElementById('testTmplName').textContent = name;
    document.getElementById('testRecipientLabel').textContent = channel === 'whatsapp' ? 'Recipient WhatsApp Number (with Country Code)' : 'Recipient Email Address';

    new bootstrap.Modal(document.getElementById('testSendModal')).show();
}
</script>
@endpush
@endsection
