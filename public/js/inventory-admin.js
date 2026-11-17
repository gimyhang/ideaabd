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
    function bn(n) {
        return Number(n || 0).toLocaleString('en-US').replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
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
                    if (pick.closest('#lifeForm')) {
                        stock = 'প্রেসে ' + bn(b.press) + ' · বাঁধাইয়ে ' + bn(b.binder) + ' · স্টোরে ' + bn(b.stock);
                    }
                    return '<button type="button" class="iv-pick-item" data-id="' + b.id + '" data-title="' + esc(b.title) + '" data-press="' + (b.press || 0) + '" data-binder="' + (b.binder || 0) + '" data-stock="' + b.stock + '"><span>' + esc(b.title) + '</span><small>' + stock + '</small></button>';
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
            input.classList.remove('is-invalid');
            pick.querySelector('.iv-pick-list').hidden = true;
            pick.dispatchEvent(new CustomEvent('iv:pick', { bubbles: true, detail: {
                id: item.dataset.id, title: item.dataset.title,
                press: +item.dataset.press || 0, binder: +item.dataset.binder || 0, stock: +item.dataset.stock || 0
            } }));
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

// ── Lifecycle entry modal: ছাপা → বাঁধাই → স্টোরে গ্রহণ, plus wastage at each stage ──────────
(function () {
    'use strict';
    var doc = document;
    var MODES = {
        printed:      { title: 'ছাপা', help: 'প্রেস থেকে ছাপা কপি এলো (বাঁধাই হয়নি)', vendor: 'প্রেসের নাম', show: ['date', 'batch', 'vendor'], cap: null },
        bound:        { title: 'বাঁধাই', help: 'প্রেসে থাকা ছাপা কপি থেকে বাঁধাই হলো — বাঁধাইখানায় আছে', vendor: 'বাঁধাইখানার নাম', show: ['date', 'batch', 'vendor'], cap: 'press' },
        received:     { title: 'স্টোরে গ্রহণ', help: 'বাঁধাইখানা থেকে বাঁধানো বই গুদামে এলো — বিক্রয়যোগ্য স্টকে যোগ হবে', vendor: 'কোন বাঁধাইখানা থেকে', show: ['date', 'batch', 'vendor', 'wh'], cap: 'binder' },
        press_waste:  { title: 'নষ্ট · প্রেসে', help: 'ছাপা কপি বাঁধাইয়ের আগেই নষ্ট/বাতিল', show: ['date'], cap: 'press', waste: true },
        binder_waste: { title: 'নষ্ট · বাঁধাইখানায়', help: 'বাঁধাইয়ের সময় বা বাঁধাইখানায় থাকা অবস্থায় নষ্ট', show: ['date'], cap: 'binder', waste: true },
        store_waste:  { title: 'নষ্ট · স্টোরে', help: 'গুদাম বা শোরুমে থাকা বই নষ্ট/হারানো — লোকেশনের স্টক থেকে কমবে', show: ['loc', 'reason'], cap: null, waste: true }
    };
    var CAP_LABEL = { press: 'প্রেসে বাকি', binder: 'বাঁধাইখানায় আছে' };

    function bn(n) {
        return Number(n || 0).toLocaleString('en-US').replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
    }

    doc.addEventListener('DOMContentLoaded', function () {
        var form = doc.getElementById('lifeForm');
        var modal = doc.getElementById('lifeModal');
        if (!form || !modal) return;

        var stage = doc.getElementById('lifeStage');
        var qty = doc.getElementById('lifeQty');
        var maxEl = doc.getElementById('lifeMax');
        var hint = doc.getElementById('lifeHint');
        var seg = doc.getElementById('lifeWasteSeg');
        var preset = doc.getElementById('lifePreset');
        var book = preset ? { press: +preset.dataset.press, binder: +preset.dataset.binder, stock: +preset.dataset.stock } : null;
        var mode = 'printed';

        function paintHint() {
            if (!book) { hint.hidden = true; return; }
            hint.innerHTML = '<span>প্রেসে <b>' + bn(book.press) + '</b></span><span>বাঁধাইখানায় <b>' + bn(book.binder) + '</b></span><span>স্টোরে <b>' + bn(book.stock) + '</b></span>';
            hint.hidden = false;
        }

        function paintMax() {
            var cfg = MODES[mode];
            qty.removeAttribute('max');
            maxEl.hidden = true;
            maxEl.classList.remove('is-zero');
            if (!cfg.cap || !book) return;
            var room = book[cfg.cap];
            qty.max = String(Math.max(room, 0));
            maxEl.textContent = CAP_LABEL[cfg.cap] + ': সর্বোচ্চ ' + bn(room) + ' কপি';
            maxEl.classList.toggle('is-zero', room <= 0);
            maxEl.hidden = false;
        }

        function setMode(next) {
            if (!MODES[next]) next = 'printed';
            mode = next;
            var cfg = MODES[mode];
            stage.value = mode;
            form.action = mode === 'store_waste' ? form.dataset.wastage : form.dataset.production;
            doc.getElementById('lifeTitle').textContent = cfg.title;
            doc.getElementById('lifeHelp').textContent = cfg.help;
            if (cfg.vendor) doc.getElementById('lifeVendorLabel').textContent = cfg.vendor;
            stage.disabled = mode === 'store_waste';

            form.querySelectorAll('[data-field]').forEach(function (el) {
                var on = cfg.show.indexOf(el.dataset.field) > -1;
                if (el.matches('input, select')) { el.disabled = !on; return; }
                el.hidden = !on;
                el.querySelectorAll('input, select').forEach(function (i) { i.disabled = !on; });
            });

            form.querySelectorAll('.lc-step').forEach(function (b) {
                b.classList.toggle('is-active', cfg.waste ? b.hasAttribute('data-life-waste') : b.dataset.lifeMode === mode);
            });
            form.querySelectorAll('.lc-wasteseg .lc-chip').forEach(function (b) {
                b.classList.toggle('is-active', b.dataset.lifeMode === mode);
            });
            seg.hidden = !cfg.waste;
            paintMax();
        }

        form.addEventListener('click', function (e) {
            var b = e.target.closest('[data-life-mode]');
            if (!b) return;
            // The main "নষ্ট" step keeps the current waste place if one is already chosen.
            setMode(b.hasAttribute('data-life-waste') && MODES[mode].waste ? mode : b.dataset.lifeMode);
        });

        form.addEventListener('iv:pick', function (e) {
            book = e.detail;
            paintHint();
            paintMax();
            qty.focus();
        });

        modal.addEventListener('show.bs.modal', function (e) {
            var t = e.relatedTarget;
            if (!t) return;
            if (t.dataset.lifeBook && !preset) {
                var pick = form.querySelector('.iv-pick');
                pick.querySelector('.iv-pick-id').value = t.dataset.lifeBook;
                var input = pick.querySelector('.iv-pick-input');
                input.value = t.dataset.lifeTitle || '';
                input.classList.add('is-set');
                book = { press: +t.dataset.lifePress || 0, binder: +t.dataset.lifeBinder || 0, stock: +t.dataset.lifeStock || 0 };
                paintHint();
            }
            setMode(t.dataset.lifeMode || 'printed');
        });
        modal.addEventListener('shown.bs.modal', function () {
            var input = form.querySelector('.iv-pick-input');
            (input && !form.querySelector('.iv-pick-id').value ? input : qty).focus();
        });

        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            var cfg = MODES[mode];
            if (cfg.cap && book && +qty.value > book[cfg.cap]) {
                e.preventDefault();
                qty.classList.add('is-invalid');
                qty.focus();
                return;
            }
            qty.classList.remove('is-invalid');
            doc.getElementById('lifeSave').disabled = true;
        });
        qty.addEventListener('input', function () { qty.classList.remove('is-invalid'); });

        setMode('printed');
        if (book) paintHint();
    });
})();
