/* Purchases › stationery / electronics entry form: item rows, product picker, live totals. */
(function () {
    'use strict';
    var doc = document;
    var form, rows, tpl, index = 0, timers = new WeakMap();

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function money(v) { return '৳' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function num(el) { return parseFloat(el && el.value) || 0; }

    function addRow() {
        var wrap = doc.createElement('div');
        wrap.innerHTML = tpl.innerHTML.replace(/__i__/g, String(index++)).trim();
        var row = wrap.firstElementChild;
        rows.appendChild(row);
        return row;
    }

    function recalc() {
        var sub = 0;
        rows.querySelectorAll('.pi-row').forEach(function (r) {
            var line = num(r.querySelector('.pi-qty')) * num(r.querySelector('.pi-cost'));
            r.querySelector('.pi-sum').textContent = money(line);
            sub += line;
        });
        var discount = Math.min(num(doc.getElementById('discount')), sub);
        var grand = Math.max(0, sub - discount);
        var paid = num(doc.getElementById('paid_amount'));
        doc.getElementById('tSub').textContent = money(sub);
        doc.getElementById('tGrand').textContent = money(grand);
        doc.getElementById('tDue').textContent = money(Math.max(0, grand - paid));
    }

    function search(row) {
        var input = row.querySelector('.pi-search');
        var list = row.querySelector('.iv-pick-list');
        var url = new URL(form.dataset.products, location.origin);
        url.searchParams.set('q', input.value.trim());
        url.searchParams.set('type', form.dataset.type);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (items) {
                var html = items.map(function (p) {
                    return '<button type="button" class="iv-pick-item" data-id="' + p.id + '" data-title="' + esc(p.title) + '" data-price="' + p.price + '"><span>' + esc(p.title) + '</span><small>' + p.stock + ' in stock</small></button>';
                }).join('');
                var typed = input.value.trim();
                if (typed) {
                    html += '<button type="button" class="iv-pick-item" data-new="' + esc(typed) + '"><span>' + esc(typed) + '</span><small>New</small></button>';
                }
                list.innerHTML = html || '<div class="iv-pick-empty">Type a name</div>';
                list.hidden = false;
            })
            .catch(function () { list.hidden = true; });
    }

    function choose(btn) {
        var row = btn.closest('.pi-row');
        var input = row.querySelector('.pi-search');
        var hint = row.querySelector('.pi-hint');
        if (btn.dataset.new !== undefined) {
            row.querySelector('.pi-pid').value = '';
            row.querySelector('.pi-title').value = btn.dataset.new;
            input.value = btn.dataset.new;
            hint.textContent = 'New item';
        } else {
            row.querySelector('.pi-pid').value = btn.dataset.id;
            row.querySelector('.pi-title').value = '';
            input.value = btn.dataset.title;
            var sale = row.querySelector('.pi-sale');
            if (!sale.value && btn.dataset.price) sale.value = btn.dataset.price;
            hint.textContent = btn.querySelector('small').textContent;
        }
        hint.hidden = false;
        input.classList.add('is-set');
        row.querySelector('.iv-pick-list').hidden = true;
        row.querySelector('.pi-qty').focus();
    }

    doc.addEventListener('DOMContentLoaded', function () {
        form = doc.getElementById('itemPurchaseForm');
        if (!form) return;
        rows = doc.getElementById('rows');
        tpl = doc.getElementById('rowTpl');
        addRow();

        doc.getElementById('addRow').addEventListener('click', addRow);

        form.addEventListener('input', function (e) {
            var t = e.target;
            if (t.classList.contains('pi-search')) {
                var row = t.closest('.pi-row');
                row.querySelector('.pi-pid').value = '';
                row.querySelector('.pi-title').value = t.value.trim();
                t.classList.remove('is-set');
                row.querySelector('.pi-hint').hidden = true;
                clearTimeout(timers.get(row));
                timers.set(row, setTimeout(function () { search(row); }, 220));
            }
            recalc();
        });

        form.addEventListener('focusin', function (e) {
            if (e.target.classList.contains('pi-search') && !e.target.value) search(e.target.closest('.pi-row'));
        });

        form.addEventListener('click', function (e) {
            var pick = e.target.closest('.iv-pick-item');
            if (pick) { choose(pick); recalc(); return; }
            var del = e.target.closest('.pi-del');
            if (del && rows.children.length > 1) { del.closest('.pi-row').remove(); recalc(); return; }
            rows.querySelectorAll('.iv-pick-list').forEach(function (l) { if (!l.parentNode.contains(e.target)) l.hidden = true; });
        });

        // Vendor phone follows a known vendor.
        var vendor = doc.getElementById('vendor_name');
        vendor.addEventListener('change', function () {
            var opt = Array.prototype.find.call(doc.querySelectorAll('#vendorList option'), function (o) { return o.value === vendor.value; });
            var phone = doc.getElementById('vendor_phone');
            if (opt && opt.dataset.phone && !phone.value) phone.value = opt.dataset.phone;
        });

        form.addEventListener('submit', function (e) {
            var bad = null;
            rows.querySelectorAll('.pi-row').forEach(function (r) {
                if (!bad && !r.querySelector('.pi-pid').value && !r.querySelector('.pi-title').value.trim()) bad = r.querySelector('.pi-search');
            });
            if (bad) { e.preventDefault(); bad.focus(); bad.classList.add('is-invalid'); }
        });

        recalc();
    });
})();
