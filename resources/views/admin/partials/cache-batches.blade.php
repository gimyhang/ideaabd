{{-- Quarantined image batches. Needs $batches (ImageCleanupService::batches()). --}}
@forelse ($batches as $b)
    <div class="ck-batch" data-batch="{{ $b['batch'] }}">
        <div>
            <div class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($b['created_at'])->format('d M Y, h:i A') }}</div>
            <div class="ck-sub">{{ $b['count'] }}টি ছবি · {{ number_format($b['bytes'] / 1048576, 1) }} MB @if (!empty($b['by']))· {{ $b['by'] }} @endif · {{ $b['expires'] }} তারিখে স্থায়ীভাবে মুছবে</div>
        </div>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-success" data-ck-restore title="সব ছবি আগের জায়গায় ফেরত আনুন">ফেরত আনুন</button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-ck-delete-batch title="স্থায়ীভাবে মুছুন">মুছুন</button>
        </div>
    </div>
@empty
    <div class="ck-sub">কোয়ারেন্টাইনে কিছু নেই।</div>
@endforelse
