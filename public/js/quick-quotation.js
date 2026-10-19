/*
 * Quick quotation (accounting hub)
 *  - live calculator: line totals, discount, tax %, grand total
 *  - old customers: type letters → matching customers, A→Z, picking one fills the form
 *  - items: type letters → catalogue / earlier lines for the chosen category, A→Z
 *  - printing cost calculator result lands here as a line (title + detail)
 */
(function () {
    'use strict';
    var doc = document;
    var rows, tpl, index = 0, modalEl, form;

    function num(el) { return parseFloat(el && el.value) || 0; }
    function money(v) { return '৳' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function round2(v) { return Math.round((v + Number.EPSILON) * 100) / 100; }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    // ── Generic suggestion dropdown ─────────────────────────────────────────
    function suggest(input, list, load, render, pick, opts) {
        opts = opts || {};
        var timer, items = [], active = -1, seq = 0;

        function close() { list.hidden = true; input.setAttribute('aria-expanded', 'false'); active = -1; }
        function paint() {
            Array.prototype.forEach.call(list.children, function (el, i) { el.classList.toggle('is-active', i === active); });
            if (list.children[active]) list.children[active].scrollIntoView({ block: 'nearest' });
        }
        function open(rows) {
            items = rows;
            if (!rows.length) { list.innerHTML = '<div class="qq-suggest-empty">None</div>'; }
            else {
                list.innerHTML = rows.map(function (r, i) { return '<button type="button" class="qq-suggest-item" role="option" data-i="' + i + '">' + render(r) + '</button>'; }).join('');
            }
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            active = rows.length ? 0 : -1;
            paint();
        }
        function run() {
            var q = input.value.trim();
            if (!q && !opts.allowEmpty) { close(); return; }
            var mine = ++seq;
            load(q).then(function (rows) { if (mine === seq) open(rows); }).catch(close);
        }

        input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 180); });
        input.addEventListener('focus', function () { if (opts.allowEmpty || input.value.trim()) run(); });
        input.addEventListener('keydown', function (e) {
            if (list.hidden) { if (e.key === 'ArrowDown') run(); return; }
            if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(items.length - 1, active + 1); paint(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(0, active - 1); paint(); }
            else if (e.key === 'Enter' && active >= 0 && items[active]) { e.preventDefault(); e.stopPropagation(); pick(items[active]); close(); }
            else if (e.key === 'Escape') { e.stopPropagation(); close(); }
        });
        list.addEventListener('mousedown', function (e) {
            var b = e.target.closest('.qq-suggest-item');
            if (b) { e.preventDefault(); pick(items[+b.dataset.i]); close(); }
        });
        doc.addEventListener('click', function (e) { if (!list.contains(e.target) && e.target !== input) close(); });
        return { close: close };
    }

    function getJson(url) {
        return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : []; });
    }

    // ── Items ───────────────────────────────────────────────────────────────
    function category() { return doc.getElementById('qqCategory').value; }

    function addRow(values) {
        var wrap = doc.createElement('div');
        wrap.innerHTML = tpl.innerHTML.replace(/__i__/g, String(index++)).trim();
        var row = wrap.firstElementChild;
        rows.appendChild(row);

        var title = row.querySelector('.qq-in-title');
        suggest(
            title, row.querySelector('.qq-suggest'),
            function (q) {
                var url = new URL(modalEl.dataset.items, location.origin);
                url.searchParams.set('category', category());
                url.searchParams.set('q', q);
                return getJson(url);
            },
            function (r) {
                return '<span class="qq-s-main"><b>' + esc(r.title) + '</b>' + (r.detail ? '<small>' + esc(r.detail) + '</small>' : '') + '</span>'
                    + '<span class="qq-s-side">' + (r.price ? money(r.price) : '') + '<em>' + esc(r.source) + '</em></span>';
            },
            function (r) { fillRow(row, r); },
            { allowEmpty: true }
        );

        if (values) fillRow(row, values);
        return row;
    }

    function fillRow(row, v) {
        row.querySelector('.qq-in-title').value = v.title || '';
        if (v.detail !== undefined) row.querySelector('.qq-in-detail').value = v.detail || '';
        if (v.price !== undefined && v.price !== null) row.querySelector('.qq-in-price').value = v.price;
        if (v.qty) row.querySelector('.qq-in-qty').value = v.qty;
        row.querySelector('.qq-unit').value = v.unit || '';
        row.querySelector('.qq-type').value = v.type || '';
        row.querySelector('.qq-reg').value = v.regular || '';
        row.querySelector('.qq-book').value = v.book_id || '';
        recalc();
        var focus = row.querySelector(v.price ? '.qq-in-qty' : '.qq-in-price');
        if (focus && doc.activeElement && doc.activeElement.classList.contains('qq-in-title')) focus.focus();
    }

    // ── Totals ──────────────────────────────────────────────────────────────
    function recalc() {
        var sub = 0;
        rows.querySelectorAll('.qq-row').forEach(function (r) {
            var line = round2(num(r.querySelector('.qq-in-qty')) * num(r.querySelector('.qq-in-price')));
            r.querySelector('.qq-sum').textContent = money(line);
            sub += line;
        });
        sub = round2(sub);

        var discountEl = doc.getElementById('qqDiscount');
        var discount = Math.min(num(discountEl), sub);
        discountEl.classList.toggle('is-invalid', num(discountEl) > sub + 0.004);

        var taxable = Math.max(0, sub - discount);
        var tax = round2(taxable * Math.min(100, num(doc.getElementById('qqTaxPct'))) / 100);
        var grand = round2(taxable + tax);

        doc.getElementById('qqTax').value = tax.toFixed(2);
        doc.getElementById('qqSub').textContent = money(sub);
        doc.getElementById('qqDisc').textContent = '−' + money(discount);
        doc.getElementById('qqTaxAmt').textContent = money(tax);
        doc.getElementById('qqGrand').textContent = money(grand);
        return { sub: sub, discount: num(discountEl) };
    }

    // ── Customers ───────────────────────────────────────────────────────────
    var SOURCE = { invoice: 'Invoice', user: 'User', bill: 'Bill' };

    function initCustomers() {
        var input = doc.getElementById('qqCustomer');
        var due = doc.getElementById('qqDue');
        suggest(
            input, doc.getElementById('qqCustomerList'),
            function (q) {
                var url = new URL(modalEl.dataset.customers, location.origin);
                url.searchParams.set('q', q);
                return getJson(url);
            },
            function (c) {
                var sub = [c.org, c.phone].filter(Boolean).join(' · ');
                return '<span class="qq-s-main"><b>' + esc(c.name) + '</b>' + (sub ? '<small>' + esc(sub) + '</small>' : '') + '</span>'
                    + '<span class="qq-s-side">' + (c.due_amount > 0 ? '<strong class="qq-s-due">' + money(c.due_amount) + '</strong>' : '') + '<em>' + esc(SOURCE[c.source_type] || '') + '</em></span>';
            },
            function (c) {
                input.value = c.name || '';
                doc.getElementById('qqPhone').value = c.phone || '';
                doc.getElementById('qqOrg').value = c.org || '';
                doc.getElementById('qqEmail').value = c.email || '';
                doc.getElementById('qqAddress').value = c.address || '';
                doc.getElementById('qqDesignation').value = c.designation || '';
                if (c.due_amount > 0) { due.textContent = 'Due ' + money(c.due_amount); due.hidden = false; } else { due.hidden = true; }
            }
        );
        // Editing the name by hand drops details that belonged to the previously picked customer.
        input.addEventListener('input', function () {
            ['qqOrg', 'qqEmail', 'qqAddress', 'qqDesignation'].forEach(function (id) { doc.getElementById(id).value = ''; });
            due.hidden = true;
        });
    }

    // ── Printing cost calculator ───────────────────────────────────────────
    var reopenAfterCalc = false;

    // Called by the calculator's "insert" buttons.
    window.addPresetItem = function (title, spec, itemType, unit, price, regularPrice, qty) {
        if (!form) return;
        // reuse the empty first row if the user has not typed anything yet
        var first = rows.querySelector('.qq-row');
        var target = (rows.children.length === 1 && !first.querySelector('.qq-in-title').value.trim() && !num(first.querySelector('.qq-in-price'))) ? first : addRow();
        fillRow(target, { title: title, detail: spec, type: itemType, unit: unit, regular: (regularPrice > price ? regularPrice : ''), price: price, qty: qty });
        var cat = doc.getElementById('qqCategory');
        if (cat.value === 'books') cat.value = 'printing_goods';
    };

    function initCalculator() {
        var calcModal = doc.getElementById('printCostCalculatorModal');
        doc.getElementById('qqCalc').addEventListener('click', function () {
            reopenAfterCalc = true;
            modalEl.addEventListener('hidden.bs.modal', function once() {
                modalEl.removeEventListener('hidden.bs.modal', once);
                if (typeof window.openPrintCostCalculator === 'function') window.openPrintCostCalculator();
            });
            window.bootstrap.Modal.getInstance(modalEl).hide();
        });
        if (calcModal) {
            calcModal.addEventListener('hidden.bs.modal', function () {
                if (!reopenAfterCalc) return;
                reopenAfterCalc = false;
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        }

        function initQuotationDefaults() {
            var date = doc.getElementById('qqDate');
            var valid = doc.getElementById('qqValid');
            var days = Math.max(1, parseInt(modalEl.dataset.validityDays, 10) || 30);
            var validDate = new Date(date.value + 'T00:00:00');
            validDate.setDate(validDate.getDate() + days);
            doc.getElementById('qqValid').value = validDate.getFullYear() + '-'
                + String(validDate.getMonth() + 1).padStart(2, '0') + '-'
                + String(validDate.getDate()).padStart(2, '0');
        }

        function initNumberRefresh() {
            var number = doc.getElementById('qqNo');
            var autoNumber = doc.getElementById('qqAutoNumber');
            var message = doc.getElementById('qqNumberError');
            var requestId = 0;
            var manuallyEdited = false;

            function refresh() {
                if (manuallyEdited) return;
                var id = ++requestId;
                var url = new URL(modalEl.dataset.numberUrl, location.origin);
                url.searchParams.set('type', 'quotation');
                url.searchParams.set('sales_category', category());
                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Could not generate a quotation number.');
                        return response.json();
                    })
                    .then(function (result) {
                        if (id !== requestId || manuallyEdited) return;
                        number.value = result.invoice_no;
                        autoNumber.value = '1';
                        message.textContent = '';
                    })
                    .catch(function (error) {
                        if (id !== requestId) return;
                        message.textContent = error.message;
                    });
            }

            number.addEventListener('input', function () {
                manuallyEdited = true;
                autoNumber.value = '0';
            });
            doc.getElementById('qqCategory').addEventListener('change', function () {
                refresh();
            });
            refresh();
        }
    }

    // ── Boot ────────────────────────────────────────────────────────────────
    doc.addEventListener('DOMContentLoaded', function () {
        form = doc.getElementById('quickQuotationForm');
        modalEl = doc.getElementById('quickQuotationModal');
        if (!form) return;
        rows = doc.getElementById('qqItems');
        tpl = doc.getElementById('qqRowTpl');
        addRow();
        initCustomers();
        initCalculator();
        initQuotationDefaults();
        initNumberRefresh();

        doc.getElementById('qqAdd').addEventListener('click', function () { addRow().querySelector('.qq-in-title').focus(); });
        form.addEventListener('input', recalc);

        form.addEventListener('click', function (e) {
            var del = e.target.closest('.qq-del');
            if (del && rows.children.length > 1) { del.closest('.qq-row').remove(); recalc(); }
        });

        // Enter inside an item row moves on instead of submitting half a quotation.
        form.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || e.defaultPrevented || !e.target.closest('.qq-row') || e.target.tagName === 'TEXTAREA') return;
            e.preventDefault();
            if (e.target.classList.contains('qq-in-price')) addRow().querySelector('.qq-in-title').focus();
            else if (e.target.classList.contains('qq-in-qty')) e.target.closest('.qq-row').querySelector('.qq-in-price').focus();
            else e.target.closest('.qq-row').querySelector('.qq-in-qty').focus();
        });

        // Changing the category changes what the item boxes suggest.
        doc.getElementById('qqCategory').addEventListener('change', function () {
            rows.querySelectorAll('.qq-suggest').forEach(function (l) { l.hidden = true; });
        });

        // Valid-until can not be before the quotation date.
        var date = doc.getElementById('qqDate'), valid = doc.getElementById('qqValid');
        function syncValid() { valid.min = date.value; if (valid.value && valid.value < date.value) valid.value = date.value; }
        date.addEventListener('change', syncValid);
        syncValid();

        form.addEventListener('submit', function (e) {
            var t = recalc();
            if (t.discount > t.sub + 0.004) { e.preventDefault(); doc.getElementById('qqDiscount').focus(); return; }
            // Item + detail become one description: first line is the item, the rest is its description.
            rows.querySelectorAll('.qq-row').forEach(function (r) {
                var title = r.querySelector('.qq-in-title').value.trim();
                var detail = r.querySelector('.qq-in-detail').value.trim();
                r.querySelector('.qq-full').value = detail ? title + '\n' + detail : title;
            });
        });

        modalEl.addEventListener('shown.bs.modal', function () { doc.getElementById('qqCustomer').focus(); });
        recalc();
    });
})();
