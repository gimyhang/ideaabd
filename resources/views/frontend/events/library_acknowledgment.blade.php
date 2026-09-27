@extends('layouts.app')

@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $libName = $registration->institution_or_org ?: ($formData['library_name'] ?? 'Library');
    $allocated = intval($formData['books_allocated'] ?? 0);
    $dispatchedDate = $formData['dispatched_date'] ?? null;
    $receivedCount = intval($formData['received_books_count'] ?? 0);
    $receivedDate = $formData['received_date'] ?? null;
    $isAck = $registration->isAcknowledged();
@endphp

@section('title', 'Book Grant Receipt Acknowledgment — ' . $libName)

@push('styles')
<style>
    .ack-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .ack-header {
        background: linear-gradient(135deg, #064e3b 0%, #047857 60%, #059669 100%);
        color: #ffffff;
        padding: 32px 28px;
        text-align: center;
    }
    .ack-body {
        padding: 32px 28px;
    }
    .info-strip {
        background: #ecfdf5;
        border: 1.5px dashed #059669;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }
    .btn-ack-submit {
        background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 1.15rem;
        padding: 14px 36px;
        border-radius: 50px;
        border: none;
        box-shadow: 0 8px 24px rgba(4, 120, 87, 0.35);
        transition: all 0.25s ease;
        width: 100%;
    }
    .btn-ack-submit:hover {
        transform: translateY(-2px);
        color: #ffffff;
        box-shadow: 0 12px 28px rgba(4, 120, 87, 0.45);
    }
</style>
@endpush

@section('content')
<div class="container py-4 py-md-5" style="max-width: 760px;">

    @if(session('success'))
        <div class="alert alert-success rounded-4 shadow-sm border-0 p-3 mb-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-4 text-success"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
        </div>
    @endif

    <div class="ack-card">
        <div class="ack-header">
            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2">Book Distribution & Grant</span>
            <h2 class="fw-bold mb-1">Book Grant Receipt Acknowledgment</h2>
            <p class="mb-0 text-white-75">Idea Prokashon & Books of Idea CSR Initiative</p>
        </div>

        <div class="ack-body">
            <div class="info-strip">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="small text-muted">Library / Institution:</div>
                        <div class="fw-bold fs-6 text-dark">{{ $libName }}</div>
                        <div class="small text-muted">Representative: {{ $registration->name }} ({{ $registration->phone }})</div>
                    </div>
                    <div class="text-md-end">
                        <div class="small text-muted">Token ID:</div>
                        <span class="badge bg-success font-monospace px-2 py-1 fs-6">#{{ $registration->registration_number }}</span>
                    </div>
                </div>

                <hr class="my-2 text-success opacity-25">

                <div class="row g-2 text-dark small">
                    <div class="col-6">
                        <strong>Allocated Books:</strong> <span class="text-success fw-bold">{{ $allocated > 0 ? $allocated . ' Books' : 'Pending' }}</span>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Dispatch Date:</strong> <span>{{ $dispatchedDate ? date('d M, Y', strtotime($dispatchedDate)) : '—' }}</span>
                    </div>
                </div>

                @if(!empty($formData['allocated_books_list']) && is_array($formData['allocated_books_list']))
                    <div class="mt-2.5 pt-2 border-top border-success-subtle">
                        <div class="fw-bold text-success small mb-1.5"><i class="fa-solid fa-boxes-stacked me-1"></i> Allocated Books Breakdown Table:</div>
                        <div class="table-responsive bg-white rounded-3 border shadow-xs">
                            <table class="table table-sm table-striped mb-0 align-middle" style="font-size: 12.5px;">
                                <thead class="bg-light border-bottom text-muted">
                                    <tr>
                                        <th class="ps-2.5" style="width: 35px;">#</th>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th class="text-center" style="width: 75px;">Copies</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formData['allocated_books_list'] as $idx => $bItem)
                                        <tr>
                                            <td class="ps-2.5 text-muted fw-semibold">{{ $idx + 1 }}</td>
                                            <td class="fw-semibold text-dark">{{ $bItem['title'] ?? '—' }}</td>
                                            <td class="text-muted">{{ $bItem['author'] ?? '—' }}</td>
                                            <td class="text-muted">{{ $bItem['category'] ?? 'General' }}</td>
                                            <td class="text-center fw-bold text-success">{{ $bItem['copies'] ?? 1 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            @if($isAck)
                <div class="card bg-light border-success p-4 rounded-4 text-center mb-4">
                    <i class="fa-solid fa-circle-check fs-1 text-success mb-2"></i>
                    <h4 class="fw-bold text-success mb-1">Receipt Acknowledgment Confirmed!</h4>
                    <p class="text-muted small mb-3">
                        Your library has successfully confirmed receipt of <strong>{{ $receivedCount ?: $allocated }} Books</strong> (Date: {{ $receivedDate ? date('d M, Y', strtotime($receivedDate)) : '—' }}).
                    </p>
                    @if(!empty($formData['acknowledgment_notes']))
                        <div class="bg-white p-3 rounded-3 border text-start small text-dark mb-3">
                            <strong>Your Remarks:</strong> "{{ $formData['acknowledgment_notes'] }}"
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('admin.libraries.print', $registration->id) }}" target="_blank" class="btn btn-outline-success rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-print me-1"></i> Print Receipt Token
                        </a>
                    </div>
                </div>
            @else
                <form action="{{ url('/pathagar/acknowledgment/' . $registration->registration_number) }}" method="POST" enctype="multipart/form-data" id="userAckForm">
                    @csrf
                    <input type="hidden" name="optimized_photo_data" id="ack_photo_data">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Received Book Count <span class="text-danger">*</span></label>
                            <input type="number" name="received_books_count" class="form-control rounded-3" placeholder="e.g. 50" value="{{ old('received_books_count', $allocated > 0 ? $allocated : '') }}" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Received Date <span class="text-danger">*</span></label>
                            <input type="date" name="received_date" class="form-control rounded-3" value="{{ old('received_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Acknowledgment Notes / Feedback</label>
                        <textarea name="acknowledgment_notes" rows="3" class="form-control rounded-3" placeholder="Received books in good condition. Thank you on behalf of our readers...">{{ old('acknowledgment_notes') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark small">Handover / Library Photo (Optional)</label>
                        <input type="file" name="receipt_photo" accept="image/*" class="form-control rounded-3" onchange="handleAckPhoto(this)">
                        <small class="text-muted" style="font-size: 11px;">You may attach a photo of the received books or library.</small>
                    </div>

                    <button type="submit" class="btn btn-ack-submit" id="submitAckBtn">
                        <i class="fa-solid fa-signature me-2"></i> Submit Acknowledgment
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function handleAckPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const maxDim = 900;
            let width = img.width;
            let height = img.height;
            if (width > height && width > maxDim) {
                height = Math.round((height * maxDim) / width);
                width = maxDim;
            } else if (height > maxDim) {
                width = Math.round((width * maxDim) / height);
                height = maxDim;
            }
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            document.getElementById('ack_photo_data').value = canvas.toDataURL('image/jpeg', 0.85);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
@endsection
