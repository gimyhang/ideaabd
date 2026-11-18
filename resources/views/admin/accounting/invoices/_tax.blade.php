{{--
    Dynamic VAT / Tax block for the invoice summary table (create + edit).
    Modes: none | exclusive (added on top) | inclusive (already inside the price) | manual (fixed amount).
    Exposes window.invoiceTax(base) → { mode, vat, tax, total, added, net } used by calcTotals().
    The server recomputes the same figures in IdeaAccountingController::documentTotals().
--}}
@php
    $taxSettings = \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $presetList = fn ($s) => collect(explode(',', (string) $s))->map(fn ($v) => trim($v))->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v)->unique()->values();
    $vatPresets = $presetList($taxSettings['vat_presets'] ?? '0, 5, 7.5, 10, 15');
    $taxPresets = $presetList($taxSettings['tax_presets'] ?? '0, 2, 3, 5, 7, 10');
    $defaultVat = (float) ($taxSettings['default_vat_rate'] ?? 7.5);
    $defaultTax = (float) ($taxSettings['default_tax_rate'] ?? 0);

    $meta = isset($invoice) && $invoice ? ($invoice->tax_meta ?? null) : null;
    $savedTax = (float) old('tax', isset($invoice) && $invoice ? $invoice->tax : 0);
    $taxMode = old('tax_mode', $meta['mode'] ?? ($savedTax > 0 ? 'manual' : 'none'));
    $vatRate = old('vat_rate', $meta['vat_rate'] ?? 0);
    $taxRate = old('tax_rate', $meta['tax_rate'] ?? 0);
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    $modes = [
        'none'      => ['নেই', 'fa-ban'],
        'exclusive' => ['যোগ হবে', 'fa-plus'],
        'inclusive' => ['অন্তর্ভুক্ত', 'fa-circle-check'],
        'manual'    => ['নির্দিষ্ট টাকা', 'fa-pen'],
    ];
@endphp

<tr id="taxConfigRow">
    <td colspan="2" class="py-1">
        <div class="inv-tax" id="invTax" data-default-vat="{{ $defaultVat }}" data-default-tax="{{ $defaultTax }}">
            <div class="inv-tax-head">
                <span class="fw-semibold text-muted">VAT / Tax:</span>
                <div class="inv-tax-modes" role="radiogroup" aria-label="VAT / Tax পদ্ধতি">
                    @foreach ($modes as $key => [$label, $icon])
                        <label class="inv-tax-mode" title="{{ ['none' => 'কোনো ভ্যাট/ট্যাক্স নেই', 'exclusive' => 'মূল্যের উপর ভ্যাট/ট্যাক্স যোগ হবে', 'inclusive' => 'মূল্যের মধ্যেই ভ্যাট/ট্যাক্স ধরা আছে — মোট বদলাবে না', 'manual' => 'নির্দিষ্ট টাকার অংক লিখুন'][$key] }}">
                            <input type="radio" name="tax_mode" value="{{ $key }}" @checked($taxMode === $key)>
                            <span><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="inv-tax-rates" data-tax-show="exclusive inclusive">
                <div class="inv-tax-rate">
                    <label for="vatRateInput">VAT %</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" min="0" max="100" name="vat_rate" id="vatRateInput" class="form-control form-control-sm font-monospace text-end" value="{{ $fmt($vatRate) }}" inputmode="decimal">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="inv-tax-chips">
                        @foreach ($vatPresets as $p)<button type="button" class="inv-tax-chip" data-rate-for="vatRateInput" data-rate="{{ $p }}">{{ $fmt($p) }}%</button>@endforeach
                    </div>
                </div>
                <div class="inv-tax-rate">
                    <label for="taxRateInput">Tax %</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="taxRateInput" class="form-control form-control-sm font-monospace text-end" value="{{ $fmt($taxRate) }}" inputmode="decimal">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="inv-tax-chips">
                        @foreach ($taxPresets as $p)<button type="button" class="inv-tax-chip" data-rate-for="taxRateInput" data-rate="{{ $p }}">{{ $fmt($p) }}%</button>@endforeach
                    </div>
                </div>
            </div>

            <div class="inv-tax-manual" data-tax-show="manual">
                <label for="taxInput" class="text-muted">মোট VAT / Tax (৳)</label>
                <div class="input-group input-group-sm" style="width: 120px;">
                    <span class="input-group-text p-1 text-muted" style="font-size: 10px;">৳</span>
                    <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm font-monospace text-end p-1" value="{{ number_format($savedTax, 2, '.', '') }}" min="0" style="height: 28px; font-size: 11px;">
                </div>
            </div>
            <div class="inv-tax-note" id="taxNote" data-tax-show="inclusive">মূল্যের মধ্যেই ভ্যাট/ট্যাক্স ধরা আছে — গ্র্যান্ড টোটাল বদলাবে না, শুধু ভেঙে দেখানো হবে।</div>

            <div class="inv-tax-notefield">
                <div class="inv-tax-notehead">
                    <label for="vatNoteInput">প্রিন্টে নোট <span class="text-muted fw-normal" id="vatNoteState">(স্বয়ংক্রিয়)</span></label>
                    <button type="button" class="inv-tax-reset" id="vatNoteReset" hidden title="নিজের লেখা মুছে স্বয়ংক্রিয় নোটে ফিরুন"><i class="fa-solid fa-rotate-left"></i> স্বয়ংক্রিয়</button>
                </div>
                <textarea name="vat_note" id="vatNoteInput" rows="2" maxlength="500" class="form-control form-control-sm" placeholder="যেমন: 1. মূল্যের সাথে ভ্যাট যুক্ত।">{{ old('vat_note', $meta['note'] ?? '') }}</textarea>
            </div>
        </div>
    </td>
</tr>
<tr id="taxNetLine" data-tax-show="inclusive">
    <td class="text-muted fw-semibold py-1">ভ্যাট/ট্যাক্স ছাড়া মূল্য:</td>
    <td class="text-end font-monospace py-1" id="taxNetAmt">৳0.00</td>
</tr>
<tr id="taxVatLine" data-tax-show="exclusive inclusive">
    <td class="text-muted fw-semibold py-1">VAT (<span id="taxVatPct">0</span>%)<span class="inv-tax-incl" data-tax-show="inclusive"> · অন্তর্ভুক্ত</span>:</td>
    <td class="text-end font-monospace py-1" id="taxVatAmt">+৳0.00</td>
</tr>
<tr id="taxTaxLine" data-tax-show="exclusive inclusive">
    <td class="text-muted fw-semibold py-1">Tax (<span id="taxTaxPct">0</span>%)<span class="inv-tax-incl" data-tax-show="inclusive"> · অন্তর্ভুক্ত</span>:</td>
    <td class="text-end font-monospace py-1" id="taxTaxAmt">+৳0.00</td>
</tr>

@once
@push('styles')
<style>
    .inv-tax { display: flex; flex-direction: column; gap: .45rem; padding: .5rem .55rem; border: 1px solid var(--ac-line, #e6eaf2); border-radius: 10px; background: var(--ac-surface-2, #f8fafc); font-size: 11px; }
    .inv-tax-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .35rem; }
    .inv-tax-modes { display: inline-flex; flex-wrap: wrap; gap: 2px; padding: 2px; border-radius: 8px; background: var(--ac-line-2, #eef1f7); }
    .inv-tax-mode { margin: 0; cursor: pointer; position: relative; }
    .inv-tax-mode input { position: absolute; opacity: 0; pointer-events: none; }
    .inv-tax-mode span { display: inline-block; padding: .2rem .5rem; border-radius: 6px; font-weight: 700; color: var(--ac-ink-2, #475569); white-space: nowrap; }
    .inv-tax-mode input:checked + span { background: var(--ac-surface, #fff); color: var(--ac-blue, #2563eb); box-shadow: 0 1px 3px rgba(15, 23, 42, .15); }
    .inv-tax-mode input:focus-visible + span { outline: 2px solid var(--ac-blue, #2563eb); outline-offset: 1px; }
    .inv-tax-rates { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .inv-tax-rate label, .inv-tax-manual label { display: block; font-weight: 700; margin-bottom: 2px; color: var(--ac-ink-2, #475569); }
    .inv-tax-rate .form-control { height: 28px; font-size: 11px; }
    .inv-tax-rate .input-group-text { font-size: 10px; padding: 0 .4rem; }
    .inv-tax-chips { display: flex; flex-wrap: wrap; gap: 3px; margin-top: 4px; }
    .inv-tax-chip { border: 1px solid var(--ac-line, #e6eaf2); background: var(--ac-surface, #fff); color: var(--ac-ink-2, #475569); border-radius: 999px; padding: 0 .45rem; font-size: 10px; font-weight: 700; line-height: 1.7; cursor: pointer; }
    .inv-tax-chip:hover { border-color: var(--ac-blue, #2563eb); color: var(--ac-blue, #2563eb); }
    .inv-tax-chip.is-on { background: var(--ac-blue, #2563eb); border-color: var(--ac-blue, #2563eb); color: #fff; }
    .inv-tax-manual { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .inv-tax-manual label { margin: 0; }
    .inv-tax-note { color: #047857; font-weight: 600; }
    .inv-tax-notefield { border-top: 1px dashed var(--ac-line, #e6eaf2); padding-top: .4rem; }
    .inv-tax-notehead { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: 2px; }
    .inv-tax-notehead label { margin: 0; font-weight: 700; color: var(--ac-ink-2, #475569); }
    .inv-tax-notefield textarea { font-size: 11px; resize: vertical; min-height: 44px; }
    .inv-tax-reset { border: 0; background: none; padding: 0; font-size: 10px; font-weight: 700; color: var(--ac-blue, #2563eb); cursor: pointer; }
    .inv-tax-reset:hover { text-decoration: underline; }
    .inv-tax [hidden], tr[data-tax-show][hidden] { display: none !important; }
    @media (max-width: 400px) { .inv-tax-rates { grid-template-columns: 1fr; } }
</style>
@endpush
@endonce

<script>
(function () {
    'use strict';
    var root = document.getElementById('invTax');
    var form = root.closest('form') || document;
    var vatIn = document.getElementById('vatRateInput');
    var taxIn = document.getElementById('taxRateInput');
    var amtIn = document.getElementById('taxInput');
    var r2 = function (v) { return Math.round((v + Number.EPSILON) * 100) / 100; };
    var pct = function (el) { return Math.min(100, Math.max(0, parseFloat(el.value) || 0)); };
    var fmtPct = function (v) { return String(+v.toFixed(2)); };

    function mode() {
        var c = form.querySelector('input[name="tax_mode"]:checked');
        return c ? c.value : 'none';
    }

    // Same maths as the server: rates apply to (subtotal − discount).
    window.invoiceTax = function (base) {
        base = Math.max(0, base || 0);
        var m = mode(), v = pct(vatIn), t = pct(taxIn), vat = 0, tax = 0, total = 0, added = 0, net = base;
        if (m === 'exclusive') {
            vat = r2(base * v / 100); tax = r2(base * t / 100); total = r2(vat + tax); added = total;
        } else if (m === 'inclusive') {
            var n = (v + t) > 0 ? base * 100 / (100 + v + t) : base;
            vat = r2(n * v / 100); tax = r2(n * t / 100); total = r2(vat + tax); net = r2(base - total);
        } else if (m === 'manual') {
            total = Math.max(0, parseFloat(amtIn.value) || 0); added = total;
        }
        if (m !== 'manual') amtIn.value = total.toFixed(2);

        var sign = m === 'inclusive' ? '৳' : '+৳';
        document.getElementById('taxVatPct').textContent = fmtPct(v);
        document.getElementById('taxTaxPct').textContent = fmtPct(t);
        document.getElementById('taxVatAmt').textContent = sign + vat.toFixed(2);
        document.getElementById('taxTaxAmt').textContent = sign + tax.toFixed(2);
        document.getElementById('taxNetAmt').textContent = '৳' + net.toFixed(2);
        // Hide a zero-rate line so only the taxes in use are shown.
        document.getElementById('taxVatLine').hidden = !(m === 'exclusive' || m === 'inclusive') || v === 0;
        document.getElementById('taxTaxLine').hidden = !(m === 'exclusive' || m === 'inclusive') || t === 0;

        lastAuto = autoNote(m, v, t, total);
        if (noteCustom && noteIn.value.trim() === lastAuto) noteCustom = false;
        if (!noteCustom) noteIn.value = lastAuto;
        paintNote();
        return { mode: m, vat: vat, tax: tax, total: total, added: added, net: net };
    };

    // ── Printed note: follows the mode/rates until the user writes their own text ──
    var noteIn = document.getElementById('vatNoteInput');
    var noteReset = document.getElementById('vatNoteReset');
    var noteState = document.getElementById('vatNoteState');
    var lastAuto = '';
    // Only custom notes are saved, so a pre-filled value means the user wrote it.
    var noteCustom = noteIn.value.trim() !== '';

    // Same wording as IdeaInvoice::autoVatNote().
    function autoNote(m, v, t, total) {
        var rates = [v > 0 ? 'VAT ' + fmtPct(v) + '%' : '', t > 0 ? 'Tax ' + fmtPct(t) + '%' : ''].filter(Boolean).join(', ');
        if (m === 'inclusive' && rates) return '1. Prices are inclusive of ' + rates + '.';
        if (m === 'exclusive' && rates) return '1. ' + rates + ' added as shown.';
        if (m === 'manual' && total > 0) return '1. VAT / Tax added as shown.';
        return '1. VAT not included unless specified.';
    }
    function paintNote() {
        noteReset.hidden = !noteCustom;
        noteState.textContent = noteCustom ? '(নিজের লেখা)' : '(স্বয়ংক্রিয়)';
    }
    noteIn.addEventListener('input', function () {
        var v = noteIn.value.trim();
        noteCustom = v !== '' && v !== lastAuto;
        paintNote();
    });
    noteIn.addEventListener('blur', function () {
        if (noteIn.value.trim() === '') { noteCustom = false; noteIn.value = lastAuto; paintNote(); }
    });
    noteReset.addEventListener('click', function () {
        noteCustom = false;
        noteIn.value = lastAuto;
        paintNote();
        noteIn.focus();
    });

    function paint() {
        var m = mode();
        document.querySelectorAll('[data-tax-show]').forEach(function (el) {
            if (el.id === 'taxVatLine' || el.id === 'taxTaxLine') return; // handled in invoiceTax()
            el.hidden = el.dataset.taxShow.split(' ').indexOf(m) === -1;
        });
        amtIn.readOnly = m !== 'manual';
        [[vatIn, 'vatRateInput'], [taxIn, 'taxRateInput']].forEach(function (p) {
            root.querySelectorAll('[data-rate-for="' + p[1] + '"]').forEach(function (c) {
                c.classList.toggle('is-on', parseFloat(c.dataset.rate) === pct(p[0]));
            });
        });
    }

    function refresh() {
        paint();
        if (typeof window.calcTotals === 'function') window.calcTotals(); else window.invoiceTax(0);
    }

    root.addEventListener('change', function (e) {
        if (e.target.name !== 'tax_mode') return;
        var m = e.target.value;
        // First switch to a rate mode with nothing set: start from the default rates in settings.
        if ((m === 'exclusive' || m === 'inclusive') && pct(vatIn) === 0 && pct(taxIn) === 0) {
            vatIn.value = fmtPct(parseFloat(root.dataset.defaultVat) || 0);
            taxIn.value = fmtPct(parseFloat(root.dataset.defaultTax) || 0);
        }
        refresh();
    });
    root.addEventListener('click', function (e) {
        var chip = e.target.closest('.inv-tax-chip');
        if (!chip) return;
        var input = document.getElementById(chip.dataset.rateFor);
        input.value = pct(input) === parseFloat(chip.dataset.rate) ? 0 : fmtPct(parseFloat(chip.dataset.rate));
        refresh();
    });
    [vatIn, taxIn, amtIn].forEach(function (el) { el.addEventListener('input', refresh); });

    paint();
    document.addEventListener('DOMContentLoaded', refresh);
})();
</script>
