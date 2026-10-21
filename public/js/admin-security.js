/* Admin › User Security */
(function () {
    'use strict';

    var root = document.getElementById('secRoot');
    if (!root) return;

    var $ = function (sel, ctx) { return (ctx || root).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || root).querySelectorAll(sel)); };
    var esc = function (v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    /* ---------- toast ---------- */
    function toast(msg, bad) {
        var el = document.createElement('div');
        el.textContent = msg;
        el.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2000;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 12px 30px -10px rgba(0,0,0,.5);transition:.25s;opacity:0;transform:translateY(8px);background:' + (bad ? '#dc2626' : '#0f172a');
        document.body.appendChild(el);
        requestAnimationFrame(function () { el.style.opacity = 1; el.style.transform = 'none'; });
        setTimeout(function () { el.style.opacity = 0; setTimeout(function () { el.remove(); }, 300); }, 2200);
    }

    window.copyText = function (text, okMsg) {
        var done = function () { toast(okMsg || 'Copied!'); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done, function () { fallback(); });
        } else { fallback(); }
        function fallback() {
            var t = document.createElement('textarea');
            t.value = text; t.style.position = 'fixed'; t.style.opacity = 0;
            document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); done(); } catch (e) { toast('Could not copy', true); }
            t.remove();
        }
    };

    /* ---------- score ring ---------- */
    var ring = $('.sec-ring');
    if (ring) {
        var bar = $('.bar', ring), score = Number(ring.dataset.score || 0);
        var len = 2 * Math.PI * Number(bar.getAttribute('r'));
        bar.style.strokeDasharray = len;
        bar.style.strokeDashoffset = len;
        requestAnimationFrame(function () { requestAnimationFrame(function () { bar.style.strokeDashoffset = len * (1 - score / 100); }); });
    }

    /* ---------- secure password generation ---------- */
    var CHARS = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
    function secureInt(max) {
        var a = new Uint32Array(1), limit = Math.floor(4294967296 / max) * max;
        do { crypto.getRandomValues(a); } while (a[0] >= limit);
        return a[0] % max;
    }
    window.generateRandomPassString = function (len) {
        len = len || 14;
        var p = 'Idea#';
        for (var i = 0; i < len - 5; i++) p += CHARS.charAt(secureInt(CHARS.length));
        var input = document.getElementById('modalAutoPassString');
        if (input) { input.value = p; meter(p); }
        return p;
    };

    function strength(p) {
        var s = 0;
        if (p.length >= 10) s++;
        if (p.length >= 14) s++;
        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
        if (/\d/.test(p)) s++;
        if (/[^A-Za-z0-9]/.test(p)) s++;
        return s; // 0..5
    }
    function meter(p) {
        var m = document.getElementById('modalPassMeter');
        if (!m) return;
        var s = strength(p || ''), colors = ['#dc2626', '#dc2626', '#d97706', '#d97706', '#16a34a', '#16a34a'];
        m.firstElementChild.style.width = (s / 5 * 100) + '%';
        m.firstElementChild.style.background = colors[s];
        var lbl = document.getElementById('modalPassMeterLbl');
        if (lbl) lbl.textContent = ['', 'Very weak', 'Weak', 'Fair', 'Good', 'Strong'][s] || '';
    }
    var pwInput = document.getElementById('modalAutoPassString');
    if (pwInput) pwInput.addEventListener('input', function () { meter(pwInput.value); });

    window.openAutoPasswordModal = function (userId, userName, identity) {
        var u = document.getElementById('modalAutoPassUserId'), i = document.getElementById('modalAutoPassIdentity');
        if (u) u.value = userId || '';
        if (i) i.value = identity || '';
        generateRandomPassString();
        var el = document.getElementById('autoPasswordModal');
        if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
    };

    /* ---------- user picker (autocomplete) ---------- */
    $$('[data-sec-pick]').forEach(function (input) {
        var wrap = input.closest('.sec-pick'), list = $('.sec-pick-list', wrap), idField = (input.form || wrap).querySelector('[data-pick-id]');
        var url = input.dataset.secPick, timer, ctrl, hl = -1, rows = [];
        function close() { list.classList.remove('show'); hl = -1; }
        function render() {
            if (!rows.length) { close(); return; }
            list.innerHTML = rows.map(function (u, i) {
                return '<div class="sec-pick-item" data-i="' + i + '"><div class="sec-avatar" style="width:28px;height:28px;font-size:12px">' + esc((u.name || '?').charAt(0)) + '</div><div><b>' + esc(u.name) + '</b> <span class="sec-chip mute">' + esc(u.role) + '</span><div class="muted" style="font-size:12px">' + esc(u.email || u.phone || '') + (u.active ? '' : ' · inactive') + '</div></div></div>';
            }).join('');
            list.classList.add('show');
        }
        function choose(i) {
            var u = rows[i]; if (!u) return;
            input.value = u.email || u.phone || u.name;
            if (idField) idField.value = u.id;
            close();
        }
        input.addEventListener('input', function () {
            if (idField) idField.value = '';
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) { rows = []; close(); return; }
            timer = setTimeout(function () {
                if (ctrl) ctrl.abort();
                ctrl = new AbortController();
                fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal })
                    .then(function (r) { return r.json(); }).then(function (d) { rows = d; render(); }).catch(function () { });
            }, 220);
        });
        input.addEventListener('keydown', function (e) {
            var items = $$('.sec-pick-item', list);
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                hl = (hl + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % (items.length || 1);
                items.forEach(function (it, i) { it.classList.toggle('hl', i === hl); });
            } else if (e.key === 'Enter' && hl > -1) { e.preventDefault(); choose(hl); }
            else if (e.key === 'Escape') close();
        });
        list.addEventListener('mousedown', function (e) {
            var it = e.target.closest('.sec-pick-item'); if (it) { e.preventDefault(); choose(Number(it.dataset.i)); }
        });
        input.addEventListener('blur', function () { setTimeout(close, 120); });
    });

    /* ---------- selection & bulk actions ---------- */
    var bulk = $('#secBulk'), checkAll = $('#secCheckAll');
    function selected() { return $$('.sec-row-check:checked'); }
    function refreshBulk() {
        var n = selected().length;
        $$('.sec-row-check').forEach(function (c) { c.closest('tr').classList.toggle('is-selected', c.checked); });
        if (bulk) { bulk.classList.toggle('show', n > 0); $('#secBulkCount').textContent = n; }
        if (checkAll) {
            var all = $$('.sec-row-check').length;
            checkAll.checked = n > 0 && n === all;
            checkAll.indeterminate = n > 0 && n < all;
        }
    }
    root.addEventListener('change', function (e) {
        if (e.target.classList.contains('sec-row-check')) refreshBulk();
        if (e.target === checkAll) { $$('.sec-row-check').forEach(function (c) { c.checked = checkAll.checked; }); refreshBulk(); }
    });
    $$('[data-bulk]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var ips = selected().map(function (c) { return c.value; });
            if (!ips.length) return;
            var act = btn.dataset.bulk;
            if (!confirm((act === 'block' ? 'Block ' : 'Unblock ') + ips.length + ' selected IP(s)?')) return;
            var f = document.getElementById('secBulkForm');
            f.querySelectorAll('input[name="ips[]"]').forEach(function (n) { n.remove(); });
            ips.forEach(function (ip) { var h = document.createElement('input'); h.type = 'hidden'; h.name = 'ips[]'; h.value = ip; f.appendChild(h); });
            f.elements.action.value = act;
            f.submit();
        });
    });

    /* ---------- filters ---------- */
    var filterForm = $('#secFilters');
    if (filterForm) {
        var search = filterForm.elements.q, t;
        if (search) search.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { filterForm.submit(); }, 500); });
        $$('select', filterForm).forEach(function (s) { s.addEventListener('change', function () { filterForm.submit(); }); });
        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && !/input|textarea|select/i.test(document.activeElement.tagName)) { e.preventDefault(); search && search.focus(); }
        });
        // keep the caret at the end after an auto-submit reload
        if (search && search.value && location.search.indexOf('q=') > -1 && document.referrer === location.href) {
            search.focus(); search.setSelectionRange(search.value.length, search.value.length);
        }
    }

    /* ---------- live stats ---------- */
    var live = $('#secLive'), liveUrl = root.dataset.liveUrl, paused = false, timerLive;
    function applyStats(d) {
        Object.keys(d.stats).forEach(function (k) {
            $$('[data-stat="' + k + '"]').forEach(function (el) {
                var v = Number(d.stats[k]).toLocaleString();
                if (el.textContent.trim() !== v) { el.textContent = v; el.animate([{ transform: 'scale(1.15)' }, { transform: 'none' }], { duration: 300 }); }
            });
        });
        var th = d.threats, total = Math.max(1, th.critical + th.high + th.medium + th.low);
        [['c', 'critical'], ['h', 'high'], ['m', 'medium'], ['l', 'low']].forEach(function (p) {
            var seg = $('.sec-threat .' + p[0]); if (seg) seg.style.width = (th[p[1]] / total * 100) + '%';
            var n = $('[data-threat="' + p[1] + '"]'); if (n) n.textContent = th[p[1]];
        });
        if (live) { $('span', live).textContent = 'Live · ' + d.at; }
    }
    function poll() {
        if (paused || document.hidden || !liveUrl) return;
        fetch(liveUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw 0; return r.json(); }).then(applyStats)
            .catch(function () { if (live) live.classList.add('off'); });
    }
    if (live && liveUrl) {
        live.style.cursor = 'pointer';
        live.title = 'Click to pause or resume auto-refresh';
        live.addEventListener('click', function () {
            paused = !paused; live.classList.toggle('off', paused);
            $('span', live).textContent = paused ? 'Auto-refresh paused' : 'Live';
        });
        timerLive = setInterval(poll, 30000);
    }
})();
