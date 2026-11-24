/* Cache & performance page: actions, live stats, key inspector, image quarantine, console. */
(function () {
    'use strict';
    var app = document.getElementById('ckApp');
    if (!app) return;
    var d = app.dataset;
    var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function toast(msg, ok) {
        var box = document.querySelector('.ck-toast-box');
        if (!box) { box = document.createElement('div'); box.className = 'ck-toast-box'; box.setAttribute('role', 'status'); box.setAttribute('aria-live', 'polite'); document.body.appendChild(box); }
        var t = document.createElement('div');
        t.className = 'ck-toast' + (ok === false ? ' is-error' : '');
        t.textContent = msg;
        box.appendChild(t);
        setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 320); }, ok === false ? 9000 : 6000);
    }

    function post(url, data) {
        var body = new FormData();
        Object.keys(data || {}).forEach(function (k) {
            var v = data[k];
            if (Array.isArray(v)) v.forEach(function (x) { body.append(k + '[]', x); }); else body.append(k, v);
        });
        return fetch(url, {
            method: 'POST', body: body, credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) {
            return r.json().catch(function () { return { success: false, message: 'সার্ভার থেকে অপ্রত্যাশিত উত্তর (' + r.status + ')' }; });
        }).catch(function () { return { success: false, message: 'সংযোগ পাওয়া যায়নি। ইন্টারনেট দেখে আবার চেষ্টা করুন।' }; });
    }

    /** Confirmation with the admin's SweetAlert dialog (resolves true when there is nothing to ask). */
    function ask(text) {
        if (!text) return Promise.resolve(true);
        if (typeof window.SwalConfirm === 'function') {
            return window.SwalConfirm({ title: 'নিশ্চিত?', text: text, icon: 'warning' }).then(function (r) { return !!(r && r.isConfirmed); });
        }
        return Promise.resolve(window.confirm(text));
    }

    function busy(btn, on, text) {
        if (!btn) return;
        if (on) { btn.dataset.label = btn.innerHTML; btn.classList.add('is-busy'); btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' + esc(text || '…'); }
        else { btn.classList.remove('is-busy'); btn.disabled = false; if (btn.dataset.label) btn.innerHTML = btn.dataset.label; }
    }

    // ── Live stats ───────────────────────────────────────────
    function paintStats(s) {
        if (!s) return;
        Object.keys(s).forEach(function (k) {
            document.querySelectorAll('[data-stat="' + k + '"]').forEach(function (el) { el.textContent = s[k] == null ? '—' : s[k]; });
            document.querySelectorAll('[data-bar="' + k + '"]').forEach(function (el) { el.style.width = (s[k] || 0) + '%'; });
        });
        [['is_route_cached', s.is_route_cached], ['view_files_count', s.view_files_count > 0], ['is_events_cached', s.is_events_cached], ['is_config_cached', s.is_config_cached]]
            .forEach(function (f) { var el = document.querySelector('[data-flag="' + f[0] + '"]'); if (el) el.classList.toggle('on', !!f[1]); });
        var states = {
            views: s.view_files_count + ' ফাইল · ' + s.view_cache_size,
            app: s.data_files_count + ' ফাইল · ' + s.data_cache_size,
            config: s.is_config_cached ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই',
            routes: s.is_route_cached ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই',
            events: s.is_events_cached ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই',
            opcache: s.opcache_enabled ? s.opcache_scripts + ' স্ক্রিপ্ট' : 'চালু নেই'
        };
        Object.keys(states).forEach(function (k) { var el = document.querySelector('[data-state="' + k + '"]'); if (el) el.textContent = states[k]; });
        var up = document.getElementById('ckUpdated');
        if (up) up.textContent = new Date().toLocaleTimeString();
    }

    function paintKeys(keys) {
        (keys || []).forEach(function (k) {
            var row = document.querySelector('tr[data-key="' + k.key + '"] [data-key-state]');
            if (row) { row.classList.toggle('on', !!k.is_cached); row.textContent = k.is_cached ? 'মেমোরিতে আছে' : 'খালি'; }
        });
    }

    function refresh() {
        return fetch(d.stats, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { if (j && j.success) { paintStats(j.stats); paintKeys(j.cachedKeys); } })
            .catch(function () {});
    }
    var refreshBtn = document.getElementById('ckRefresh');
    if (refreshBtn) refreshBtn.addEventListener('click', function () { busy(refreshBtn, true, 'রিফ্রেশ'); refresh().then(function () { busy(refreshBtn, false); }); });

    // ── Cache actions (data-ck-action) ───────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-ck-action]');
        if (!btn) return;
        ask(btn.dataset.ckConfirm).then(function (go) {
        if (!go) return;
        busy(btn, true, btn.dataset.busy);
        post(btn.dataset.ckAction).then(function (j) {
            busy(btn, false);
            toast(j.message || (j.success ? 'সম্পন্ন' : 'ব্যর্থ'), !!j.success);
            if (j.stats) paintStats(j.stats);
            if (j.batches) paintBatches(j.batches);
            refresh();
        });
        });
    });

    // ── Keys ─────────────────────────────────────────────────
    var modalEl = document.getElementById('ckKeyModal');
    function keyOf(el) { var tr = el.closest('tr[data-key]'); return tr ? tr.dataset.key : null; }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-ck-inspect],[data-ck-warm],[data-ck-delete]');
        if (!b) return;
        var key = keyOf(b);
        if (!key) return;

        if (b.hasAttribute('data-ck-inspect')) {
            busy(b, true, '');
            post(d.inspect, { key: key }).then(function (j) {
                busy(b, false);
                if (!j.success) { toast(j.message, false); return; }
                document.getElementById('ckKeyTitle').textContent = (j.label || key);
                document.getElementById('ckKeyMeta').textContent = key + ' · ' + (j.exists ? (j.type + ' · ' + j.size) : 'মেমোরিতে নেই');
                document.getElementById('ckKeyBody').textContent = j.exists ? (j.preview || '(খালি)') : 'এই কি এখন মেমোরিতে নেই। সাইট দরকার হলে নিজেই তৈরি করবে।';
                if (window.bootstrap && modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        } else if (b.hasAttribute('data-ck-warm')) {
            busy(b, true, '');
            post(d.warm, { key: key }).then(function (j) { busy(b, false); toast(j.message, !!j.success); refresh(); });
        } else {
            if (!confirm('এই কি মুছে ফেলবেন? সাইট পরে নিজেই নতুন করে তৈরি করবে।')) return;
            busy(b, true, '');
            post(d['delete'], { key: key }).then(function (j) { busy(b, false); toast(j.message, !!j.success); refresh(); });
        }
    });

    var allKeys = document.getElementById('ckAllKeys');
    var bulkBtn = document.getElementById('ckBulkDelete');
    function picked() { return Array.prototype.slice.call(document.querySelectorAll('.ck-key-pick:checked')).map(function (c) { return c.value; }); }
    function syncBulk() { if (bulkBtn) bulkBtn.disabled = picked().length === 0; }
    if (allKeys) allKeys.addEventListener('change', function () { document.querySelectorAll('.ck-key-pick').forEach(function (c) { c.checked = allKeys.checked; }); syncBulk(); });
    document.addEventListener('change', function (e) { if (e.target.classList && e.target.classList.contains('ck-key-pick')) syncBulk(); });
    if (bulkBtn) bulkBtn.addEventListener('click', function () {
        var keys = picked();
        if (!keys.length || !confirm(keys.length + 'টি কি মুছে ফেলবেন?')) return;
        busy(bulkBtn, true, 'মুছছে');
        post(d.bulk, { keys: keys }).then(function (j) {
            busy(bulkBtn, false);
            toast(j.message, !!j.success);
            document.querySelectorAll('.ck-key-pick').forEach(function (c) { c.checked = false; });
            if (allKeys) allKeys.checked = false;
            syncBulk();
            refresh();
        });
    });

    // ── Unused images ────────────────────────────────────────
    var scanBtn = document.getElementById('ckScan');
    var qBtn = document.getElementById('ckQuarantine');
    var result = document.getElementById('ckScanResult');

    if (scanBtn) scanBtn.addEventListener('click', function () {
        busy(scanBtn, true, 'স্ক্যান হচ্ছে… (কিছুক্ষণ লাগতে পারে)');
        qBtn.hidden = true;
        post(d.scan).then(function (j) {
            busy(scanBtn, false);
            result.hidden = false;
            if (!j.success) { result.innerHTML = '<div class="ck-scan-sum text-danger">' + esc(j.message) + '</div>'; return; }
            if (!j.count) { result.innerHTML = '<div class="ck-scan-sum">✔ অব্যবহৃত কোনো ছবি নেই।</div>'; return; }
            var rows = j.items.map(function (i) {
                return '<div class="ck-scan-row">' + (i.url ? '<img src="' + esc(i.url) + '" alt="" loading="lazy">' : '<span></span>') +
                    '<code>' + esc(i.rel) + '</code><span class="text-nowrap">' + esc(i.size) + '</span></div>';
            }).join('');
            result.innerHTML = '<div class="ck-scan-sum">' + j.count + 'টি অব্যবহৃত ছবি · মোট ' + esc(j.size) +
                (j.count > j.items.length ? ' (প্রথম ' + j.items.length + 'টি দেখানো হলো)' : '') + '</div><div class="ck-scan-list">' + rows + '</div>';
            qBtn.hidden = false;
        });
    });

    if (qBtn) qBtn.addEventListener('click', function () {
        if (!confirm('তালিকার ছবিগুলো কোয়ারেন্টাইনে সরানো হবে। দরকার হলে নিচ থেকে ফেরত আনা যাবে। চালিয়ে যাবেন?')) return;
        busy(qBtn, true, 'সরানো হচ্ছে');
        post(d.quarantine).then(function (j) {
            busy(qBtn, false);
            toast(j.message, !!j.success);
            if (j.success) { qBtn.hidden = true; result.hidden = true; }
            if (j.batches) paintBatches(j.batches);
            if (j.stats) paintStats(j.stats);
        });
    });

    function paintBatches(list) {
        var box = document.getElementById('ckBatches');
        if (!box) return;
        if (!list.length) { box.innerHTML = '<div class="ck-empty"><i class="fa-regular fa-circle-check"></i> কোয়ারেন্টাইনে কিছু নেই।</div>'; return; }
        box.innerHTML = list.map(function (b) {
            return '<div class="ck-batch" data-batch="' + esc(b.batch) + '"><div><div class="fw-semibold">' + esc(b.created_at) + '</div>' +
                '<div class="ck-sub">' + b.count + 'টি ছবি · ' + (b.bytes / 1048576).toFixed(1) + ' MB' + (b.by ? ' · ' + esc(b.by) : '') + ' · ' + esc(b.expires) + ' তারিখে স্থায়ীভাবে মুছবে</div></div>' +
                '<div class="d-flex gap-2"><button type="button" class="ck-btn ck-btn--success-soft ck-btn--sm" data-ck-restore><i class="fa-solid fa-rotate-left"></i><span>ফেরত আনুন</span></button>' +
                '<button type="button" class="ck-btn ck-btn--danger-soft ck-btn--sm" data-ck-delete-batch><i class="fa-regular fa-trash-can"></i><span>মুছুন</span></button></div></div>';
        }).join('');
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-ck-restore],[data-ck-delete-batch]');
        if (!b) return;
        var batch = b.closest('[data-batch]').dataset.batch;
        var restoring = b.hasAttribute('data-ck-restore');
        if (!confirm(restoring ? 'এই ব্যাচের সব ছবি আগের জায়গায় ফেরত আনবেন?' : 'এই ব্যাচের ছবি স্থায়ীভাবে মুছে ফেলবেন? এটি আর ফেরত আনা যাবে না।')) return;
        busy(b, true, '');
        post(restoring ? d.restore : d.deleteBatch, { batch: batch }).then(function (j) {
            busy(b, false);
            toast(j.message, !!j.success);
            if (j.batches) paintBatches(j.batches);
            if (j.stats) paintStats(j.stats);
        });
    });

    // ── Console ──────────────────────────────────────────────
    var con = document.getElementById('ckConsole');
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-ck-run]');
        if (!b || !con) return;
        var cmd = b.dataset.ckRun;
        busy(b, true, cmd);
        con.textContent = '$ php artisan ' + cmd + '\n…';
        post(d.run, { command: cmd }).then(function (j) {
            busy(b, false);
            con.textContent = '$ php artisan ' + cmd + '\n' + (j.output || j.message || '');
            if (!j.success) toast(j.message || 'ব্যর্থ', false);
            refresh();
        });
    });

    // ── Live: refresh every 30 s while the tab is visible (switch in the header, remembered) ──
    var live = document.getElementById('ckLive');
    try { if (live && localStorage.getItem('ck_live') === '0') live.checked = false; } catch (e) {}
    function liveOn() { return !live || live.checked; }
    if (live) live.addEventListener('change', function () {
        try { localStorage.setItem('ck_live', live.checked ? '1' : '0'); } catch (e) {}
        if (live.checked) refresh();
    });
    setInterval(function () { if (!document.hidden && liveOn()) refresh(); }, 30000);

    // ── Tabs (keys · images · console), remembered ──
    var tabs = document.querySelectorAll('[data-ck-tab]');
    function openTab(id, focus) {
        tabs.forEach(function (t) {
            var on = t.dataset.ckTab === id;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
            var pane = document.getElementById(t.dataset.ckTab);
            if (pane) { pane.hidden = !on; pane.classList.toggle('is-active', on); }
            if (on && focus) t.focus();
        });
        try { localStorage.setItem('ck_tab', id); } catch (e) {}
    }
    tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { openTab(t.dataset.ckTab); });
        t.addEventListener('keydown', function (e) {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            var next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            openTab(next.dataset.ckTab, true);
        });
    });
    try { var saved = localStorage.getItem('ck_tab'); if (saved && document.getElementById(saved)) openTab(saved); } catch (e) {}

    // ── Key search ──
    var find = document.getElementById('ckKeyFind');
    if (find) find.addEventListener('input', function () {
        var q = find.value.trim().toLowerCase(), shown = 0;
        document.querySelectorAll('tr[data-find]').forEach(function (tr) {
            var hit = !q || tr.dataset.find.indexOf(q) > -1;
            tr.hidden = !hit;
            if (hit) shown++;
        });
        var none = document.querySelector('tr.ck-none');
        if (none) none.hidden = shown > 0;
    });
})();
