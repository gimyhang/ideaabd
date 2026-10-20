/* Blog admin: live filtering without page reloads — status tabs, search, selects, pagination, auto-refresh. */
(function () {
    'use strict';
    var doc = document;

    doc.addEventListener('DOMContentLoaded', function () {
        var form = doc.getElementById('blogFilterForm');
        var bulk = doc.getElementById('bulkActionForm');
        var live = doc.getElementById('blogLive');
        var tabs = doc.getElementById('blogTabs');
        if (!form || !live) return;

        var stamp = doc.getElementById('blogUpdated');
        var timer, ctrl;

        function query() {
            var p = new URLSearchParams(new FormData(form));
            Array.from(p.keys()).forEach(function (k) { if (!p.get(k) || (k === 'status' && p.get(k) === 'all')) p.delete(k); });
            return p.toString();
        }

        function selected() { return doc.querySelectorAll('.row-checkbox:checked').length; }

        function refreshSelectionUi() {
            if (!bulk) return;
            var n = selected();
            bulk.classList.toggle('has-selection', n > 0);
            var label = doc.querySelector('label[for="selectAllCheckbox"]');
            if (label) label.textContent = n > 0 ? n + ' selected' : 'Select All';
            var master = doc.getElementById('selectAllCheckbox');
            if (master && n === 0) master.checked = false;
        }

        function load(qs, opts) {
            opts = opts || {};
            if (ctrl) ctrl.abort();
            ctrl = new AbortController();
            live.classList.add('is-loading');

            fetch(form.getAttribute('action') + (qs ? '?' + qs : ''), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: ctrl.signal })
                .then(function (r) { return r.ok ? r.text() : Promise.reject(r.status); })
                .then(function (html) {
                    var next = new DOMParser().parseFromString(html, 'text/html');
                    var nLive = next.getElementById('blogLive');
                    var nTabs = next.getElementById('blogTabs');
                    if (!nLive) throw new Error('no list');
                    live.innerHTML = nLive.innerHTML;
                    if (tabs && nTabs) {
                        var keepStamp = stamp ? stamp.textContent : '';
                        tabs.innerHTML = nTabs.innerHTML;
                        stamp = doc.getElementById('blogUpdated');
                        if (stamp) stamp.textContent = keepStamp;
                    }
                    if (!opts.silent) history.replaceState(null, '', form.getAttribute('action') + (qs ? '?' + qs : ''));
                    if (stamp) stamp.textContent = new Date().toLocaleTimeString();
                    refreshSelectionUi();
                })
                .catch(function (e) { if (e && e.name === 'AbortError') return; })
                .then(function () { live.classList.remove('is-loading'); });
        }

        // filters: typing searches after a short pause, selects apply at once
        form.addEventListener('submit', function (e) { e.preventDefault(); load(query()); });
        form.querySelectorAll('select').forEach(function (sel) {
            sel.removeAttribute('onchange');
            sel.addEventListener('change', function () { syncTab(); load(query()); });
        });
        var search = form.querySelector('input[name="search"]');
        if (search) {
            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { load(query()); }, 300);
            });
        }

        function syncTab() {
            var sel = form.querySelector('select[name="status"]');
            if (!sel || !tabs) return;
            tabs.querySelectorAll('.blog-tab').forEach(function (t) { t.classList.toggle('is-active', t.dataset.status === (sel.value || 'all')); });
        }

        // status tabs
        doc.addEventListener('click', function (e) {
            var tab = e.target.closest('#blogTabs .blog-tab');
            if (tab) {
                e.preventDefault();
                var sel = form.querySelector('select[name="status"]');
                if (sel) sel.value = tab.dataset.status;
                syncTab();
                load(query());
                return;
            }
            var page = e.target.closest('#blogLive .pagination a');
            if (page) {
                e.preventDefault();
                var u = new URL(page.href);
                load(u.search.slice(1));
            }
        });

        // selection bar
        doc.addEventListener('change', function (e) {
            if (e.target.matches('.row-checkbox, #selectAllCheckbox')) refreshSelectionUi();
        });
        refreshSelectionUi();

        // keep the list fresh; never while something is ticked or a menu/modal is open
        setInterval(function () {
            if (doc.hidden || selected() > 0 || doc.querySelector('.modal.show, .dropdown-menu.show')) return;
            load(query(), { silent: true });
        }, 45000);
    });
})();

/* Customizer: one-click presets, unsaved-changes marker, reset, Ctrl+S */
(function () {
    'use strict';
    var doc = document;

    doc.addEventListener('DOMContentLoaded', function () {
        var form = doc.getElementById('blogCustomizerForm');
        if (!form) return;

        var badge = doc.getElementById('custStatus');
        var presets = doc.getElementById('custPresets');
        var initial = serialize();

        function serialize() {
            var parts = [];
            new FormData(form).forEach(function (v, k) { if (typeof v === 'string' && k !== '_token') parts.push(k + '=' + v); });
            return parts.join('&');
        }
        function mark() {
            var dirty = serialize() !== initial;
            if (badge) badge.hidden = !dirty;
            return dirty;
        }
        function setSelect(id, value) {
            var el = doc.getElementById(id);
            if (!el) return;
            if (typeof value === 'number') { el.selectedIndex = Math.min(value, el.options.length - 1); } else { el.value = value; }
        }
        function preview() { if (typeof window.updateLivePreview === 'function') window.updateLivePreview(); }

        var PRESETS = {
            classic: { font: 0, size: '1.08rem', lh: '1.6',  bg: '#ffffff' },
            book:    { font: 1, size: '1.15rem', lh: '1.75', bg: '#fbf9f4' },
            reader:  { font: 1, size: '1.25rem', lh: '1.9',  bg: '#f8f4eb' },
            compact: { font: 2, size: '1.0rem',  lh: '1.45', bg: '#ffffff' }
        };

        if (presets) {
            presets.addEventListener('click', function (e) {
                var b = e.target.closest('button[data-preset]');
                var p = b && PRESETS[b.dataset.preset];
                if (!p) return;
                setSelect('custFontFamily', p.font);
                setSelect('custFontSize', p.size);
                setSelect('custLineHeight', p.lh);
                setSelect('custReadingBg', p.bg);
                presets.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-on', x === b); });
                preview();
                mark();
            });
        }

        form.addEventListener('input', mark);
        form.addEventListener('change', mark);

        var reset = doc.getElementById('custReset');
        if (reset) {
            reset.addEventListener('click', function () {
                form.reset();
                if (presets) presets.querySelectorAll('button').forEach(function (x) { x.classList.remove('is-on'); });
                preview();
                mark();
            });
        }

        // Ctrl/Cmd + S saves while the customizer is open
        doc.addEventListener('keydown', function (e) {
            var modal = doc.getElementById('blogCustomizerModal');
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's' && modal && modal.classList.contains('show')) {
                e.preventDefault();
                if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
            }
        });

        // leaving with edits open asks first
        var modalEl = doc.getElementById('blogCustomizerModal');
        if (modalEl) {
            modalEl.addEventListener('hide.bs.modal', function (e) {
                if (mark() && !form.dataset.saving && !window.confirm('Discard unsaved changes?')) e.preventDefault();
            });
        }
        form.addEventListener('submit', function () { form.dataset.saving = '1'; });
    });
})();