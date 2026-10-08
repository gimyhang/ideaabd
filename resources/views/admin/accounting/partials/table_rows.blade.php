@forelse ($records as $item)
    @php
        $hasDue = (float)$item->due_amount > 0.001;
        $isPaid = ($item->payment_status === 'paid');
        $isPartial = ($item->payment_status === 'partial');
    @endphp
    <tr class="align-middle record-row {{ $hasDue ? 'has-due' : '' }}" id="row-{{ $item->global_id }}" style="border-bottom: 1px solid #f1f5f9 !important;">
        {{-- 1. Date & Channel --}}
        <td class="ps-4 py-3.5" style="width: 140px;">
            <div class="fw-bold text-dark font-monospace" style="font-size: 13px;">
                {{ $item->date ? $item->date->format('d M, Y') : '—' }}
            </div>
            <div class="mt-1 d-flex align-items-center gap-1.5 flex-wrap">
                <span class="badge {{ $item->channel_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 10.5px; border: 1px solid rgba(0,0,0,0.06);">
                    <i class="{{ $item->doc_icon }} me-1"></i>{{ $item->channel_label }}
                </span>
            </div>
        </td>

        {{-- 2. Document # & Ref --}}
        <td class="py-3.5 px-3" style="width: 175px;">
            <div class="d-flex align-items-center gap-1.5">
                @if($item->view_url)
                    <a href="{{ $item->view_url }}" class="fw-bold text-primary font-monospace text-decoration-none hover-underline" style="font-size: 13px;" title="View Details">
                        {{ $item->doc_no }}
                    </a>
                @else
                    <span class="fw-bold text-dark font-monospace" style="font-size: 13px;">{{ $item->doc_no }}</span>
                @endif
            </div>

            <div class="small text-muted mt-0.5 d-flex align-items-center gap-1 flex-wrap" style="font-size: 11px;">
                <span class="badge {{ $item->stream_badge_class }} px-1.5 py-0.5 rounded-pill fw-semibold" style="border: 1px solid rgba(0,0,0,0.06);">{{ $item->stream_label }}</span>
                @if($item->reference_no)
                    <span class="text-truncate d-inline-block align-middle" style="max-width: 110px;" title="Ref: {{ $item->reference_no }}">
                        Ref: {{ $item->reference_no }}
                    </span>
                @endif
            </div>

            {{-- Synced Online Order Badge --}}
            @if($item->is_online_order && $item->is_synced && $item->stream !== 'online')
                <div class="mt-1">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; border: 1px solid #bbf7d0 !important;" title="Online Order Synced">
                        <i class="fa-solid fa-link me-1"></i>Order #{{ $item->online_order_number }}
                    </span>
                </div>
            @endif
        </td>

        {{-- 3. Client / Party / Stakeholder --}}
        <td class="py-3.5 px-3" style="min-width: 200px;">
            <div class="fw-bold text-dark fs-6">
                {{ $item->party_name }}
            </div>
            @if($item->party_org)
                <div class="text-muted small text-truncate mt-0.5" style="font-size: 11.5px; max-width: 220px;" title="{{ $item->party_org }}">
                    <i class="fa-regular fa-building me-1 opacity-75"></i>{{ $item->party_org }}
                </div>
            @endif
            @if($item->party_phone)
                <div class="text-muted small font-monospace mt-0.5" style="font-size: 11.5px;">
                    <i class="fa-solid fa-phone me-1 text-success opacity-75"></i><a href="tel:{{ $item->party_phone }}" class="text-muted text-decoration-none">{{ $item->party_phone }}</a>
                </div>
            @endif
        </td>

        {{-- 4. Description / Item Summary --}}
        <td class="py-3.5 px-3" style="min-width: 180px;">
            <div class="text-dark small fw-medium d-flex align-items-center gap-1.5" style="font-size: 12.5px;">
                <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5" style="font-size: 10px; border: 1px solid #cbd5e1 !important;">
                    {{ $item->category_label }}
                </span>
                <span class="text-muted" style="font-size: 11px;">({{ $item->items_count }} {{ Str::plural('item', $item->items_count) }})</span>
            </div>
            <div class="text-muted small text-truncate mt-1" style="max-width: 240px; font-size: 11.5px;" title="{{ $item->items_summary }}">
                {{ $item->items_summary }}
            </div>
            @if($item->notes)
                <div class="text-secondary small fst-italic text-truncate mt-0.5" style="max-width: 240px; font-size: 10.5px;" title="{{ $item->notes }}">
                    <i class="fa-regular fa-comment-dots me-1"></i>{{ $item->notes }}
                </div>
            @endif
        </td>

        {{-- 5. Grand Total (৳) --}}
        <td class="py-3.5 px-3 text-end font-monospace fw-bold fs-6 {{ $item->stream === 'expense' ? 'text-danger' : 'text-dark' }}" style="width: 120px;">
            <span class="currency-symbol">৳</span>{{ number_format($item->total_amount, 2) }}
        </td>

        {{-- 6. Paid (৳) --}}
        <td class="py-3.5 px-3 text-end font-monospace fw-bold text-success" style="width: 115px;" id="paid-{{ $item->global_id }}">
            <span class="currency-symbol">৳</span>{{ number_format($item->paid_amount, 2) }}
        </td>

        {{-- 7. Due (৳) --}}
        <td class="py-3.5 px-3 text-end font-monospace fw-bold {{ $hasDue ? 'text-danger' : 'text-muted' }}" style="width: 115px;" id="due-{{ $item->global_id }}">
            @if($hasDue)
                <span class="currency-symbol">৳</span>{{ number_format($item->due_amount, 2) }}
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>

        {{-- 8. Payment Status & Method --}}
        <td class="py-3.5 px-3 text-center" style="width: 130px;" id="status-{{ $item->global_id }}">
            @if($isPaid)
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="border: 1px solid #bbf7d0 !important;">
                    <i class="fa-solid fa-circle-check me-1"></i>Paid
                </span>
            @elseif($isPartial)
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1" style="border: 1px solid #fed7aa !important;">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i>Partial
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="border: 1px solid #fecdd3 !important;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>Unpaid
                </span>
            @endif
            @php
                $methodKey = strtolower((string) $item->payment_method);
                $methodEn = match($methodKey) {
                    'cash' => 'Cash',
                    'bkash' => 'bKash',
                    'nagad' => 'Nagad',
                    'bank', 'bank transfer' => 'Bank Transfer',
                    'cod' => 'COD',
                    'cheque' => 'Cheque',
                    default => ($item->payment_method ?: 'Cash'),
                };
            @endphp
            <div class="small text-muted mt-1 font-monospace" style="font-size: 11px;">
                {{ $methodEn }}
            </div>
        </td>

        {{-- 9. Actions --}}
        <td class="py-3.5 pe-4 text-center" style="width: 140px;">
            <div class="btn-group btn-group-sm rounded-pill p-0.5 bg-light border shadow-2xs" style="border: 1px solid #e2e8f0 !important;">
                {{-- Quick Pay Trigger (if due > 0) --}}
                @if($hasDue && ($item->channel === 'institutional' || $item->channel === 'offline_bill' || $item->channel === 'online'))
                    @php
                        $targetType = ($item->channel === 'offline_bill') ? 'bill' : (($item->channel === 'online') ? 'order' : 'invoice');
                    @endphp
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" 
                            onclick="openUniversalQuickPayModal('{{ $targetType }}', {{ $item->id }}, '{{ $item->doc_no }}', {{ $item->due_amount }}, '{{ addslashes($item->party_name) }}')" 
                            title="Receive Payment" style="border: 1px solid #86efac;">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </button>
                @endif

                {{-- Sync to Invoice for Unsynced Online Order --}}
                @if($item->can_sync_invoice && $item->sync_url)
                    <form action="{{ $item->sync_url }}" method="POST" class="d-inline" onsubmit="return confirm('Do you want to create an invoice for this online order?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Create Invoice" style="border: 1px solid #93c5fd;">
                            <i class="fa-solid fa-file-circle-plus text-primary"></i>
                        </button>
                    </form>
                @endif

                {{-- View Details --}}
                @if($item->view_url)
                    <a href="{{ $item->view_url }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="View Details" style="border: 1px solid #93c5fd;">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                @endif

                {{-- Universal Slip --}}
                @if(!empty($item->slip_url))
                    <a href="{{ $item->slip_url }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="View Slip" style="border: 1px solid #cbd5e1;">
                        <i class="fa-solid fa-receipt"></i>
                    </a>
                @endif

                {{-- Print / Document View --}}
                @if(!empty($item->print_url) && $item->print_url !== ($item->slip_url ?? null))
                    <a href="{{ $item->print_url }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Print" style="border: 1px solid #cbd5e1;">
                        <i class="fa-solid fa-print"></i>
                    </a>
                @endif

                {{-- Delete (for ledger entries) --}}
                @if($item->channel === 'ledger' && $item->delete_url)
                    <form action="{{ $item->delete_url }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Delete" style="border: 1px solid #fca5a5;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 75px; height: 75px; border: 1px solid #e2e8f0;">
                <i class="fa-solid fa-magnifying-glass fs-2 text-muted opacity-50"></i>
            </div>
            <h6 class="fw-bold text-dark mb-0">No records found</h6>
        </td>
    </tr>
@endforelse
