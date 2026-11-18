{{-- VAT / Tax rows for printed / public invoices. Needs $invoice. --}}
@php
    $tm = $invoice->tax_meta ?? null;
    $tmMode = $tm['mode'] ?? ((float) $invoice->tax > 0 ? 'manual' : 'none');
    $pctTxt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    $lines = [];
    if (in_array($tmMode, ['exclusive', 'inclusive'], true)) {
        if ((float) ($tm['vat_rate'] ?? 0) > 0) $lines[] = ['VAT (' . $pctTxt($tm['vat_rate']) . '%)', (float) ($tm['vat_amount'] ?? 0)];
        if ((float) ($tm['tax_rate'] ?? 0) > 0) $lines[] = ['Tax (' . $pctTxt($tm['tax_rate']) . '%)', (float) ($tm['tax_amount'] ?? 0)];
    } elseif ($tmMode === 'manual' && (float) $invoice->tax > 0) {
        $lines[] = ['VAT / Tax', (float) $invoice->tax];
    }
@endphp
@if ($tmMode === 'inclusive' && $lines)
    <tr class="border-bottom border-light">
        <td class="text-muted fw-semibold">Net (excl. VAT/Tax):</td>
        <td class="text-end text-muted fw-semibold font-monospace">৳{{ number_format((float) ($tm['net'] ?? 0), 2) }}</td>
    </tr>
@endif
@foreach ($lines as [$label, $amount])
    <tr class="border-bottom border-light">
        <td class="text-muted fw-semibold">{{ $label }}@if ($tmMode === 'inclusive') <span class="badge bg-success-subtle text-success border px-1 py-0" style="font-size: 8.5px;">Included</span>@endif:</td>
        <td class="text-end text-muted fw-semibold font-monospace">{{ $tmMode === 'inclusive' ? '' : '+ ' }}৳{{ number_format($amount, 2) }}</td>
    </tr>
@endforeach
