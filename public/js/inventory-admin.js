/* Inventory admin: book picker, dynamic transfer rows, location modal. */
(function () {
    'use strict';
    var doc = document;
    var timers = new WeakMap();

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // ── Book picker ──────────────────────────────────────────
    function search(pick) {
        var input = pick.querySelector('.iv-pick-input');
        var list = pick.querySelector('.iv-pick-list');
        var hidden = pick.querySelector('.iv-pick-id');
        var url = new URL(pick.dataset.url, location.origin);
        url.searchParams.set('q', input.value.trim());
        if (pick.dataset.location) {
            var sel = doc.querySelector(pick.dataset.location);
            if (sel && sel.value) url.searchParams.set('location_id', sel.value);
        }
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (rows) {
                if (!rows.length) { list.innerHTML = '<div class="iv-pick-empty">No books</div>'; list.hidden = false; return; }
                list.innerHTML = rows.map(function (b) {
                    var stock = b.here !== null && b.here !== undefined ? b.here + ' here' : b.stock + ' total';
                    return '<button type="button" class="iv-pick-item" data-id="' + b.id + '" data-title="' + esc(b.title) + '"><span>' + esc(b.title) + '</span><small>' + stock + '</small></button>';
                }).join('');
                list.hidden = false;
            })
            .catch(function () { list.hidden = true; });
        hidden.value = hidden.value; // keep selection until a new one is chosen
    }

    doc.addEventListener('input', function (e) {
        var input = e.target.closest && e.target.closest('.iv-pick-input');
        if (!input) return;
        var pick = input.closest('.iv-pick');
        pick.querySelector('.iv-pick-id').value = '';
        input.classList.remove('is-set');
        clearTimeout(timers.get(pick));
        timers.set(pick, setTimeout(function () { search(pick); }, 220));
    });

    doc.addEventListener('focusin', function (e) {
        var input = e.target.closest && e.target.closest('.iv-pick-input');
        if (input && !input.value) search(input.closest('.iv-pick'));
    });

    doc.addEventListener('click', function (e) {
        var item = e.target.closest && e.target.closest('.iv-pick-item');
        if (item) {
            var pick = item.closest('.iv-pick');
            pick.querySelector('.iv-pick-id').value = item.dataset.id;
            var input = pick.querySelector('.iv-pick-input');
            input.value = item.dataset.title;
            input.classList.add('is-set');
            pick.querySelector('.iv-pick-list').hidden = true;
            return;
        }
        doc.querySelectorAll('.iv-pick-list').forEach(function (l) {
            if (!l.parentNode.contains(e.target)) l.hidden = true;
        });
    });

    // A form must not be submitted with a typed-but-unpicked book.
    doc.addEventListener('submit', function (e) {
        var bad = null;
        e.target.querySelectorAll('.iv-pick').forEach(function (p) {
            if (!bad && !p.querySelector('.iv-pick-id').value) bad = p.querySelector('.iv-pick-input');
        });
        if (bad) { e.preventDefault(); bad.focus(); bad.classList.add('is-invalid'); }
    }, true);

    // ── Dynamic transfer rows ────────────────────────────────
    var rowIndex = 0;
    function addRow(containerId) {
        var box = doc.getElementById(containerId);
        var tpl = doc.getElementById(box.dataset.template);
        var html = tpl.innerHTML.replace(/__i__/g, String(rowIndex++));
        var wrap = doc.createElement('div');
        wrap.innerHTML = html.trim();
        box.appendChild(wrap.firstElementChild);
    }
    doc.addEventListener('click', function (e) {
        var add = e.target.closest && e.target.closest('[data-iv-add]');
        if (add) { addRow(add.dataset.ivAdd); return; }
        var rm = e.target.closest && e.target.closest('[data-iv-remove]');
        if (rm) {
            var row = rm.closest('.iv-row');
            if (row.parentNode.children.length > 1) row.remove();
        }
    });
    doc.addEventListener('DOMContentLoaded', function () {
        var rows = doc.getElementById('trRows');
        if (rows) addRow('trRows');
    });

    // ── Location modal (create / edit) ───────────────────────
    doc.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-iv-location]');
        if (!btn) return;
        var form = doc.getElementById('locationForm');
        if (!form) return;
        var d = JSON.parse(btn.dataset.ivLocation);
        form.reset();
        ['name', 'code', 'type', 'contact_person', 'phone', 'email', 'address'].forEach(function (k) {
            if (form.elements[k]) form.elements[k].value = d[k] == null ? (k === 'type' ? 'warehouse' : '') : d[k];
        });
        form.elements.is_central.checked = !!d.is_central;
        form.elements.is_active.checked = d.is_active !== false;
        if (d.id) {
            form.action = form.dataset.update + '/' + d.id;
            doc.getElementById('locationMethod').value = 'PUT';
        } else {
            form.action = form.dataset.store;
            doc.getElementById('locationMethod').value = 'POST';
        }
    });
})();

// ── Stock grid: instant filters, auto-refresh, no full reload ─────────────
(function () {
    'use strict';
    var doc = document;
    doc.addEventListener('DOMContentLoaded', function () {
        var form = doc.getElementById('ivFilter');
        var live = doc.getElementById('ivLive');
        if (!form || !live) return;
        var stamp = doc.getElementById('ivUpdated');
        var exp = doc.getElementById('ivExport');
        var timer, ctrl;

        function query() {
            var p = new URLSearchParams(new FormData(form));
            Array.from(p.keys()).forEach(function (k) { if (!p.get(k)) p.delete(k); });
            var cur = new URLSearchParams(location.search);
            ['sort', 'dir'].forEach(function (k) { if (cur.get(k)) p.set(k, cur.get(k)); });
            return p.toString();
        }

        function load(qs, push) {
            if (ctrl) ctrl.abort();
            ctrl = new AbortController();
            live.classList.add('is-loading');
            fetch(location.pathname + (qs ? '?' + qs : ''), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: ctrl.signal })
                .then(function (r) { return r.ok ? r.text() : Promise.reject(); })
                .then(function (html) {
                    var next = new DOMParser().parseFromString(html, 'text/html').getElementById('ivLive');
                    if (next) live.innerHTML = next.innerHTML;
                    if (push) history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''));
                    if (exp) exp.href = form.dataset.export + (qs ? '?' + qs : '');
                    if (stamp) stamp.textContent = new Date().toLocaleTimeString();
                })
                .catch(function () {})
                .then(function () { live.classList.remove('is-loading'); });
        }

        form.addEventListener('submit', function (e) { e.preventDefault(); load(query(), true); });
        form.addEventListener('input', function (e) {
            clearTimeout(timer);
            timer = setTimeout(function () { load(query(), true); }, e.target.type === 'search' ? 300 : 0);
        });
        doc.addEventListener('click', function (e) {
            var a = e.target.closest('#ivLive .iv-sort, #ivLive .pagination a');
            if (!a) return;
            e.preventDefault();
            var u = new URL(a.href);
            history.replaceState(null, '', u.pathname + u.search);
            load(u.search.slice(1), false);
        });
        setInterval(function () { if (!doc.hidden) load(location.search.slice(1), false); }, 30000);
    });
})();
