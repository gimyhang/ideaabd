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

    // The purchase form sits behind the "+ New purchase" button; open it when there is work in it.
    function openForm() {
        var wrap = doc.getElementById('piFormWrap');
        if (!wrap || wrap.classList.contains('show')) return;
        wrap.classList.add('show');
        var t = doc.getElementById('piFormToggle');
        if (t) { t.setAttribute('aria-expanded', 'true'); t.classList.remove('collapsed'); }
    }

    window.piOpenForm = openForm;

    // Category is only asked for items that will be created as new products.
    function syncCat(row) {
        var isNew = !row.querySelector('.pi-pid').value && row.querySelector('.pi-title').value.trim() !== '';
        var box = row.querySelector('.pi-cat');
        box.hidden = !isNew;
        row.querySelector('.pi-catsel').disabled = !isNew;
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

        var qty = 0, count = 0;
        rows.querySelectorAll('.pi-row').forEach(function (r) {
            var q = num(r.querySelector('.pi-qty'));
            if (q > 0) { qty += q; count++; }
        });
        doc.getElementById('tCount').textContent = count + ' · ' + qty;
        var st = doc.getElementById('tStatus');
        var state = grand > 0 && paid >= grand ? 'paid' : (paid > 0 ? 'partial' : 'credit');
        st.textContent = { paid: 'Paid', partial: 'Partial', credit: 'Credit' }[state];
        st.className = 'pi-status is-' + state;
    }

    // ── Draft autosave (this browser only) ───────────────────
    var DRAFT_KEY;
    function saveDraft() {
        try {
            var data = { f: {}, rows: [] };
            ['purchase_date', 'memo_no', 'due_date', 'vendor_name', 'vendor_phone', 'discount', 'paid_amount', 'payment_method', 'transaction_ref', 'notes'].forEach(function (id) {
                var el = doc.getElementById(id); if (el) data.f[id] = el.value;
            });
            rows.querySelectorAll('.pi-row').forEach(function (r) {
                data.rows.push({
                    pid: r.querySelector('.pi-pid').value, title: r.querySelector('.pi-title').value, label: r.querySelector('.pi-search').value,
                    qty: r.querySelector('.pi-qty').value, cost: r.querySelector('.pi-cost').value, sale: r.querySelector('.pi-sale').value
                });
            });
            var empty = !data.rows.some(function (r) { return r.label || r.qty; }) && !data.f.vendor_name;
            if (empty) localStorage.removeItem(DRAFT_KEY); else localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
        } catch (e) {}
    }
    function restoreDraft() {
        try {
            var raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return false;
            var data = JSON.parse(raw);
            Object.keys(data.f).forEach(function (id) { var el = doc.getElementById(id); if (el && !el.value) el.value = data.f[id]; });
            var used = data.rows.filter(function (r) { return r.label || r.qty; });
            if (!used.length) return !!data.f.vendor_name;
            rows.innerHTML = '';
            used.forEach(function (d) {
                var r = addRow();
                r.querySelector('.pi-pid').value = d.pid; r.querySelector('.pi-title').value = d.title;
                var s = r.querySelector('.pi-search'); s.value = d.label; if (d.pid || d.title) s.classList.add('is-set');
                r.querySelector('.pi-qty').value = d.qty; r.querySelector('.pi-cost').value = d.cost; r.querySelector('.pi-sale').value = d.sale;
            });
            return true;
        } catch (e) { return false; }
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
                    return '<button type="button" class="iv-pick-item" data-id="' + p.id + '" data-title="' + esc(p.title) + '" data-price="' + p.price + '" data-cost="' + (p.last_cost == null ? '' : p.last_cost) + '"><span>' + esc(p.title) + (p.sku ? ' <em>' + esc(p.sku) + '</em>' : '') + '</span><small class="' + (p.stock <= 5 ? 'is-low' : '') + '">' + p.stock + ' in stock' + (p.last_cost != null ? ' · last ' + money(p.last_cost) : '') + '</small></button>';
                }).join('');
                var typed = input.value.trim();
                var exact = items.some(function (p) { return p.title.trim().toLowerCase() === typed.toLowerCase(); });
                if (typed && !exact) {
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
            // Same product already on another line: add to that quantity instead of duplicating.
            var dup = Array.prototype.find.call(rows.querySelectorAll('.pi-row'), function (r) {
                return r !== row && r.querySelector('.pi-pid').value === btn.dataset.id;
            });
            if (dup) {
                var dq = dup.querySelector('.pi-qty');
                dq.value = (parseInt(dq.value, 10) || 0) + 1;
                if (rows.children.length > 1) row.remove();
                dq.focus();
                recalc();
                return;
            }
            row.querySelector('.pi-pid').value = btn.dataset.id;
            var cost = row.querySelector('.pi-cost');
            if (!cost.value && btn.dataset.cost) cost.value = btn.dataset.cost;
            row.querySelector('.pi-title').value = '';
            input.value = btn.dataset.title;
            var sale = row.querySelector('.pi-sale');
            if (!sale.value && btn.dataset.price) sale.value = btn.dataset.price;
            hint.textContent = btn.querySelector('small').textContent;
        }
        hint.hidden = false;
        syncCat(row);
        input.classList.add('is-set');
        row.querySelector('.iv-pick-list').hidden = true;
        row.querySelector('.pi-qty').focus();
    }

    doc.addEventListener('DOMContentLoaded', function () {
        form = doc.getElementById('itemPurchaseForm');
        if (!form) return;
        rows = doc.getElementById('rows');
        tpl = doc.getElementById('rowTpl');
        DRAFT_KEY = 'pi-draft-' + form.dataset.type;
        var restored = restoreDraft();
        if (!rows.children.length) addRow();
        if (restored) { doc.getElementById('draftNote').hidden = false; openForm(); }

        doc.getElementById('addRow').addEventListener('click', addRow);
        doc.getElementById('draftClear').addEventListener('click', function (e) {
            e.preventDefault();
            try { localStorage.removeItem(DRAFT_KEY); } catch (x) {}
            location.reload();
        });
        doc.getElementById('payFull').addEventListener('click', function () {
            var sub = 0;
            rows.querySelectorAll('.pi-row').forEach(function (r) { sub += num(r.querySelector('.pi-qty')) * num(r.querySelector('.pi-cost')); });
            doc.getElementById('paid_amount').value = Math.max(0, sub - Math.min(num(doc.getElementById('discount')), sub)).toFixed(2);
            recalc(); saveDraft();
        });
        // Enter on the last field of the last line adds the next line.
        form.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || !e.target.classList.contains('pi-sale')) return;
            e.preventDefault();
            var row = e.target.closest('.pi-row');
            var next = row.nextElementSibling || addRow();
            next.querySelector('.pi-search').focus();
        });

        form.addEventListener('input', function (e) {
            var t = e.target;
            if (t.classList.contains('pi-search')) {
                var row = t.closest('.pi-row');
                row.querySelector('.pi-pid').value = '';
                row.querySelector('.pi-title').value = t.value.trim();
                t.classList.remove('is-set');
                row.querySelector('.pi-hint').hidden = true;
                syncCat(row);
                clearTimeout(timers.get(row));
                timers.set(row, setTimeout(function () { search(row); }, 220));
            }
            recalc();
            saveDraft();
        });

        form.addEventListener('focusin', function (e) {
            if (e.target.classList.contains('pi-search') && !e.target.value) search(e.target.closest('.pi-row'));
        });

        form.addEventListener('click', function (e) {
            var pick = e.target.closest('.iv-pick-item');
            if (pick) { choose(pick); recalc(); saveDraft(); return; }
            var del = e.target.closest('.pi-del');
            if (del && rows.children.length > 1) { del.closest('.pi-row').remove(); recalc(); saveDraft(); return; }
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
            if (bad) { e.preventDefault(); bad.focus(); bad.classList.add('is-invalid'); return; }
            try { localStorage.removeItem(DRAFT_KEY); } catch (x) {}
            // Block double submits (a second click would book the purchase twice).
            form.querySelectorAll('.pi-submit button').forEach(function (b) { b.disabled = true; });
        });

        recalc();
    });
})();

// ── Embedded stock list: instant filters, auto-refresh, "+ Buy" into the form ──
(function () {
    'use strict';
    var doc = document;
    doc.addEventListener('DOMContentLoaded', function () {
        var box = doc.getElementById('piStock');
        var body = doc.getElementById('piStockBody');
        var filter = doc.getElementById('piStockFilter');
        var form = doc.getElementById('itemPurchaseForm');
        if (!box || !body || !filter) return;
        var stamp = doc.getElementById('piStockAt');
        var catInput = filter.elements.category_id;
        var timer, ctrl, page = 1;

        function load() {
            var p = new URLSearchParams(new FormData(filter));
            Array.from(p.keys()).forEach(function (k) { if (!p.get(k)) p.delete(k); });
            if (box.dataset.type) p.set('type', box.dataset.type);
            if (box.dataset.buy) p.set('buy', '1');
            if (page > 1) p.set('page', page);
            if (ctrl) ctrl.abort();
            ctrl = new AbortController();
            body.classList.add('is-loading');
            fetch(box.dataset.url + '?' + p.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: ctrl.signal })
                .then(function (r) { return r.ok ? r.text() : Promise.reject(); })
                .then(function (html) { body.innerHTML = html; if (stamp) stamp.textContent = new Date().toLocaleTimeString(); })
                .catch(function () {})
                .then(function () { body.classList.remove('is-loading'); });
        }

        filter.addEventListener('input', function (e) {
            page = 1;
            if (e.target.name === 'type' && catInput) catInput.value = '';
            clearTimeout(timer);
            timer = setTimeout(load, e.target.type === 'search' ? 300 : 0);
        });

        body.addEventListener('click', function (e) {
            var tab = e.target.closest('.pi-tab');
            if (tab) {
                if (catInput) catInput.value = tab.dataset.cat;
                page = 1;
                load();
                return;
            }
            var merge = e.target.closest('.pi-merge');
            if (merge) {
                var ids = merge.dataset.ids.split(',');
                if (!confirm('Merge ' + ids.length + ' duplicate(s) into the kept item? Stock is added together. This cannot be undone.')) return;
                merge.disabled = true;
                var fd = new FormData();
                fd.append('_token', box.dataset.csrf);
                fd.append('keep_id', merge.dataset.keep);
                ids.forEach(function (id) { fd.append('merge_ids[]', id); });
                fetch(box.dataset.merge, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(load)
                    .catch(function () { merge.disabled = false; alert('Merge failed.'); });
                return;
            }
            var a = e.target.closest('.pagination a');
            if (a) {
                e.preventDefault();
                page = parseInt(new URL(a.href).searchParams.get('page'), 10) || 1;
                load();
                return;
            }
            var buy = e.target.closest('.pi-buy');
            if (!buy || !form) return;
            if (window.piOpenForm) window.piOpenForm();
            var rows = doc.getElementById('rows');
            var list = Array.prototype.slice.call(rows.querySelectorAll('.pi-row'));
            var row = list.find(function (r) { return r.querySelector('.pi-pid').value === buy.dataset.id; });
            if (row) {
                var q = row.querySelector('.pi-qty');
                q.value = (parseInt(q.value, 10) || 0) + 1;
            } else {
                row = list.find(function (r) { return !r.querySelector('.pi-pid').value && !r.querySelector('.pi-title').value.trim(); });
                if (!row) { doc.getElementById('addRow').click(); row = rows.lastElementChild; }
                row.querySelector('.pi-pid').value = buy.dataset.id;
                row.querySelector('.pi-title').value = '';
                var s = row.querySelector('.pi-search');
                s.value = buy.dataset.title; s.classList.add('is-set');
                row.querySelector('.pi-sale').value = buy.dataset.price;
                var h = row.querySelector('.pi-hint');
                h.textContent = buy.dataset.stock + ' in stock'; h.hidden = false;
                row.querySelector('.pi-qty').value = 1;
                row.querySelector('.pi-cat').hidden = true;
                row.querySelector('.pi-catsel').disabled = true;
            }
            form.dispatchEvent(new Event('input', { bubbles: true }));
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.querySelector('.pi-cost').focus();
        });

        setInterval(function () { if (!doc.hidden) load(); }, 30000);
    });
})();
