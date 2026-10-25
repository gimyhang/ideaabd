{{-- Purchase orders table. $rows, $empty; optional $items (list the books), $ledger (show paid and due). --}}
@php
    $showItems  = $items ?? false;
    $showLedger = $ledger ?? false;
@endphp

@if(count($rows) === 0)
    <div class="sp-empty"><i class="fas fa-receipt fs-3 d-block mb-2 opacity-50"></i>{{ $empty }}</div>
@else
    <div class="table-responsive">
        <table class="table sp-table align-middle mb-0">
            <thead>
                <tr>
                    <th>চালান</th>
                    <th>তারিখ</th>
                    @if($showItems)<th>বই</th>@endif
                    <th class="text-end">কপি</th>
                    <th class="text-end">মোট</th>
                    @if($showLedger)<th class="text-end">পরিশোধিত</th><th class="text-end">বাকি</th>@endif
                    <th>অবস্থা</th>
                    <th class="text-end">চালান</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $p)
                    @php
                        $grand = (float) ($p->grand_total > 0 ? $p->grand_total : $p->total_amount);
                        $due   = max(0, $grand - (float) $p->paid_amount);
                        $state = $due <= 0 ? 'paid' : ((float) $p->paid_amount > 0 ? 'partial' : 'due');
                        [$tone, $label] = match ($state) { 'paid' => ['success', 'পরিশোধিত'], 'partial' => ['warning', 'আংশিক'], default => ['danger', 'বকেয়া'] };
                    @endphp
                    <tr>
                        <td>
                            <span class="fw-semibold font-monospace">#{{ $p->purchase_no ?? $p->id }}</span>
                            @if($p->publisher_memo_no)<small class="d-block text-muted">মেমো {{ $p->publisher_memo_no }}</small>@endif
                        </td>
                        <td class="text-nowrap text-muted">{{ $p->purchase_date?->format('d M Y') ?? '—' }}</td>
                        @if($showItems)
                            <td>
                                <div style="max-width: 300px">
                                    @foreach($p->items->take(3) as $item)
                                        <div class="d-flex justify-content-between gap-2 small">
                                            <span class="text-truncate">{{ $item->book_title ?? ($item->book->title ?? 'বই') }}</span>
                                            <span class="text-muted flex-shrink-0">× @bn($item->quantity)</span>
                                        </div>
                                    @endforeach
                                    @if($p->items->count() > 3)<small class="text-muted">আরও @bn($p->items->count() - 3)টি</small>@endif
                                </div>
                            </td>
                        @endif
                        <td class="text-end">@bn($p->items->sum('quantity'))</td>
                        <td class="text-end fw-semibold text-nowrap">৳@bn($grand, 2)</td>
                        @if($showLedger)
                            <td class="text-end text-success text-nowrap">৳@bn($p->paid_amount, 2)</td>
                            <td class="text-end text-nowrap {{ $due > 0 ? 'text-danger' : 'text-muted' }}">৳@bn($due, 2)</td>
                        @endif
                        <td><span class="badge rounded-pill text-bg-{{ $tone }}">{{ $label }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('publisher.purchases.challan', $p->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-print me-1"></i>প্রিন্ট</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
