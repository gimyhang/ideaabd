/* Production ledger: stage modal + book picker that shows what is waiting at press / binder. */
(function () {
    'use strict';
    var doc = document;
    var LABELS = { printed: ['Printed', 'Press'], bound: ['Bound', 'Binder'], received: ['Received', 'Binder'] };
    var timer;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    doc.addEventListener('DOMContentLoaded', function () {
        var modal = doc.getElementById('stageModal');
        var form = doc.getElementById('stageForm');
        if (!modal || !form) return;

        var stageInput = doc.getElementById('stageInput');
        var title = doc.getElementById('stageTitle');
        var vendorLabel = doc.getElementById('vendorLabel');
        var search = doc.getElementById('bookSearch');
        var idInput = doc.getElementById('bookId');
        var list = doc.getElementById('bookList');
        var hint = doc.getElementById('bookHint');

        function setStage(stage) {
            var l = LABELS[stage] || LABELS.printed;
            stageInput.value = stage;
            title.textContent = l[0];
            vendorLabel.textContent = l[1];
        }

        modal.addEventListener('show.bs.modal', function (e) {
            var trigger = e.relatedTarget;
            setStage((trigger && trigger.dataset.stage) || 'printed');
            hint.hidden = true;
        });

        function load() {
            var url = new URL(form.dataset.books, location.origin);
            url.searchParams.set('q', search.value.trim());
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(function (books) {
                    list.innerHTML = books.length ? books.map(function (b) {
                        return '<button type="button" data-id="' + b.id + '" data-title="' + esc(b.title) + '" data-press="' + b.at_press + '" data-binder="' + b.at_binder + '" data-stock="' + b.stock + '"><span>' + esc(b.title) + (b.author ? ' <em>— ' + esc(b.author) + '</em>' : '') + '</span><small>' + b.stock + ' in stock</small></button>';
                    }).join('') : '<div class="iv-pick-empty">None</div>';
                    list.hidden = false;
                })
                .catch(function () { list.hidden = true; });
        }

        search.addEventListener('input', function () {
            idInput.value = '';
            hint.hidden = true;
            clearTimeout(timer);
            timer = setTimeout(load, 220);
        });
        search.addEventListener('focus', function () { if (!search.value) load(); });

        list.addEventListener('click', function (e) {
            var b = e.target.closest('button[data-id]');
            if (!b) return;
            idInput.value = b.dataset.id;
            search.value = b.dataset.title;
            list.hidden = true;
            hint.innerHTML = '<span>Press <b>' + b.dataset.press + '</b></span><span>Binder <b>' + b.dataset.binder + '</b></span><span>Stock <b>' + b.dataset.stock + '</b></span>';
            hint.hidden = false;
            form.elements.quantity.focus();
        });

        doc.addEventListener('click', function (e) {
            if (!list.contains(e.target) && e.target !== search) list.hidden = true;
        });

        form.addEventListener('submit', function (e) {
            if (!idInput.value) { e.preventDefault(); search.focus(); search.classList.add('is-invalid'); }
        });
    });

    // Live refresh: KPIs, per-book rows and real-time store stock (no page reload).
    doc.addEventListener('DOMContentLoaded', function () {
        var bar = doc.getElementById('pdLive');
        var body = doc.getElementById('pdSummaryBody');
        if (!bar || !body) return;
        var n = function (v) { return Number(v || 0).toLocaleString(); };
        var chip = function (v) { return v ? '<span class="pd-chip is-amber">' + n(v) + '</span>' : '<span class="text-muted">0</span>'; };
        var url = new URL(bar.dataset.url, location.origin);
        new URLSearchParams(location.search).forEach(function (v, k) {
            if (['q', 'date_from', 'date_to', 'open'].indexOf(k) > -1) url.searchParams.set(k, v);
        });
        var map = { at_press: 'press', at_binder: 'binder' };

        var last = null;
        doc.getElementById('pdCsv').addEventListener('click', function () {
            if (!last) return;
            var q = function (v) { return '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"'; };
            var lines = [['Category', 'Title', 'Author', 'ISBN', 'Printed', 'Bound', 'Received', 'At press', 'At binder', 'Stock', 'Warehouse', 'Showroom']];
            last.rows.forEach(function (r) { lines.push([r.category, r.title, r.author, r.isbn, r.printed, r.bound, r.received, r.at_press, r.at_binder, r.stock, r.warehouse, r.showroom]); });
            var blob = new Blob(['﻿' + lines.map(function (l) { return l.map(q).join(','); }).join('\n')], { type: 'text/csv;charset=utf-8' });
            var a = doc.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'production-' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });

        // Instant search: no submit needed.
        var qInput = doc.querySelector('.pd-filter input[name="q"]');
        var qTimer;
        if (qInput) {
            qInput.addEventListener('input', function () {
                clearTimeout(qTimer);
                qTimer = setTimeout(function () {
                    if (qInput.value.trim()) url.searchParams.set('q', qInput.value.trim()); else url.searchParams.delete('q');
                    tick();
                }, 250);
            });
        }

        function paint(d) {
            last = d;
            Object.keys(d.totals).forEach(function (k) {
                var el = doc.querySelector('[data-kpi="' + (map[k] || k) + '"]');
                if (el) el.textContent = n(d.totals[k]);
            });
            doc.getElementById('pdStore').textContent = n(d.store.stock);
            doc.getElementById('pdWh').textContent = n(d.store.warehouse);
            doc.getElementById('pdSr').textContent = n(d.store.showroom);
            doc.getElementById('pdIn').textContent = n(d.store.indoor);
            doc.getElementById('pdUpdated').textContent = d.updated_at;
            var groups = {};
            d.rows.forEach(function (r) { (groups[r.category] = groups[r.category] || []).push(r); });
            body.innerHTML = d.rows.length ? Object.keys(groups).sort().map(function (cat) {
                var g = groups[cat];
                var stock = g.reduce(function (a, r) { return a + r.stock; }, 0);
                return '<tr class="pd-cat"><td colspan="10"><strong>' + esc(cat) + '</strong> <span class="pd-sub">' + g.length + ' · Stock ' + n(stock) + '</span></td></tr>' + g.map(row).join('');
            }).join('') : '<tr><td colspan="10" class="pd-empty">None</td></tr>';
        }

        function bar(l) {
            var p = Math.max(1, l.printed);
            var w = function (v) { return Math.round(Math.max(0, v) / p * 100); };
            return '<div class="pd-bar" title="Printed ' + l.printed + ' · Bound ' + l.bound + ' · Received ' + l.received + '"><i class="is-rec" style="width:' + w(l.received) + '%"></i><i class="is-bnd" style="width:' + w(l.bound - l.received) + '%"></i><i class="is-prt" style="width:' + w(l.printed - l.bound) + '%"></i></div>';
        }

        function row(r) {
                return '<tr data-book="' + r.id + '"><td><div class="fw-semibold">' + esc(r.title) + '</div><div class="pd-sub">' + esc(r.author || '') + (r.isbn ? ' · ' + esc(r.isbn) : '') + '</div>' + bar(r.life) + '</td>' +
                    '<td class="text-end">' + n(r.printed) + '</td><td class="text-end">' + n(r.bound) + '</td><td class="text-end">' + n(r.received) + '</td>' +
                    '<td class="text-end">' + chip(r.at_press) + '</td><td class="text-end">' + chip(r.at_binder) + '</td>' +
                    '<td class="text-end fw-bold">' + n(r.stock) + '</td><td class="text-end">' + n(r.warehouse) + '</td><td class="text-end">' + n(r.showroom) + '</td>' +
                    '<td class="text-end"><a href="' + location.pathname + '?book_id=' + r.id + '" class="btn btn-outline-secondary btn-sm">History</a></td></tr>';
        }

        function tick() {
            if (doc.hidden) return;
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) { if (d) paint(d); })
                .catch(function () {});
        }
        tick();
        setInterval(tick, 15000);
        doc.addEventListener('visibilitychange', function () { if (!doc.hidden) tick(); });
    });
})();
