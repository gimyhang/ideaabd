@php
    $q = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
    $tk = fn ($v) => '৳' . number_format((float) $v, 2);
@endphp
<div class="p-3 border-bottom bg-light d-flex flex-wrap gap-3 align-items-end justify-content-between">
    <div class="d-flex flex-wrap gap-3 small">
        <div><div class="text-muted">মোট ক্রয়</div><strong class="font-monospace">{{ $q($t['in_qty']) }} {{ $material->unit }}</strong> <span class="text-muted">{{ $tk($t['in_amount']) }}</span></div>
        <div><div class="text-muted">ব্যবহার</div><strong class="font-monospace">{{ $q($t['out_qty']) }}</strong> <span class="text-muted">{{ $tk($t['out_amount']) }}</span></div>
        <div><div class="text-muted">মজুদ</div><strong class="font-monospace {{ $t['balance'] <= 0 ? 'text-danger' : 'text-success' }}">{{ $q($t['balance']) }}</strong> <span class="text-muted">{{ $tk($t['value']) }}</span></div>
        <div><div class="text-muted">গড় দর</div><strong class="font-monospace">{{ $tk($t['avg_cost']) }}</strong></div>
    </div>
    <form data-ledger-filter action="{{ route('admin.purchases.raw.ledger', $material->id) }}" class="d-flex gap-1 align-items-center">
        <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm" aria-label="From">
        <span class="text-muted">–</span>
        <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm" aria-label="To">
        <button class="btn btn-sm btn-primary rounded-pill px-3"><i class="fa-solid fa-filter"></i></button>
    </form>
</div>
<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0 small">
        <thead class="table-light">
            <tr>
                <th class="ps-3">তারিখ</th>
                <th>ধরন</th>
                <th>বিবরণ</th>
                <th class="text-end">আসা</th>
                <th class="text-end">যাওয়া</th>
                <th class="text-end">ব্যালান্স</th>
                <th class="text-end">দর</th>
                <th class="text-end">মূল্য</th>
                <th class="pe-3"></th>
            </tr>
        </thead>
        <tbody>
            @if($from)
                <tr class="table-light"><td class="ps-3" colspan="5">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} এর আগের জের</td><td class="text-end font-monospace fw-bold">{{ $q($ledger['opening']) }}</td><td colspan="3"></td></tr>
            @endif
            @forelse($ledger['rows'] as $row)
                @php $m = $row['move']; $s = $m->signedQty(); @endphp
                <tr>
                    <td class="ps-3 text-nowrap">{{ $m->moved_at?->format('d M Y') }}</td>
                    <td><span class="rm-recent__t rm-t-{{ $m->type }}">{{ \App\Models\RawMaterialMove::TYPES[$m->type] ?? $m->type }}</span></td>
                    <td>
                        @if($m->purchase)
                            <a href="{{ route('admin.purchases.show', $m->purchase_id) }}" target="_blank" rel="noopener">#{{ $m->purchase->purchase_no }}</a>
                        @endif
                        {{ $m->book?->title ?? $m->job }}
                        @if($m->note)<span class="text-muted">{{ $m->purchase ? '· ' : '' }}{{ $m->note }}</span>@endif
                        @if($m->creator)<span class="text-muted" style="font-size: 10.5px;"> · {{ $m->creator->name }}</span>@endif
                    </td>
                    <td class="text-end font-monospace text-success">{{ $s > 0 ? $q($s) : '' }}</td>
                    <td class="text-end font-monospace text-danger">{{ $s < 0 ? $q(-$s) : '' }}</td>
                    <td class="text-end font-monospace fw-bold">{{ $q($row['balance']) }}</td>
                    <td class="text-end font-monospace">{{ $tk($m->unit_cost) }}</td>
                    <td class="text-end font-monospace">{{ $tk($m->amount) }}</td>
                    <td class="pe-3 text-end">
                        @unless($m->purchase_id)
                            <form method="POST" action="{{ route('admin.purchases.raw.moves.destroy', $m->id) }}" data-confirm="এই এন্ট্রিটি মুছে ফেলবেন? মজুদ আবার হিসাব হবে।" data-confirm-icon="error">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0" title="মুছুন"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">এই সময়ে কোনো লেনদেন নেই।</td></tr>
            @endforelse
        </tbody>
        @if($ledger['rows'])
            <tfoot class="table-light fw-bold">
                <tr><td class="ps-3" colspan="5">সমাপনী মজুদ</td><td class="text-end font-monospace">{{ $q($ledger['closing']) }} {{ $material->unit }}</td><td colspan="3"></td></tr>
            </tfoot>
        @endif
    </table>
</div>
