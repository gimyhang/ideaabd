/* Storage section of admin/cache/manage: measures what fills the hosting account and frees it in one click.
 * Server: AdminCacheController::storage / storageClean, App\Support\StorageCleaner. */
(function () {
    'use strict';
    var box = document.getElementById('ckStore');
    if (!box) return;
    var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var list = box.querySelector('[data-store-list]'), bar = box.querySelector('[data-store-bar]');
    var freeEl = box.querySelector('[data-store-free]'), totalEl = box.querySelector('[data-store-total]');
    var btn = document.getElementById('ckStoreClean'), again = document.getElementById('ckStoreMeasure');
    var COLORS = { cache: '#2563eb', pages: '#0ea5e9', views: '#6366f1', logs: '#f59e0b', sessions: '#14b8a6', reader: '#8b5cf6', temp: '#94a3b8', quarantine: '#f97316', backups: '#e11d48', uploads: '#cbd5e1' };
    var RISKY = { backups: 1, quarantine: 1 };
    var data = null, chosen = null;

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function human(b) {
        b = Number(b) || 0;
        if (b >= 1073741824) return (b / 1073741824).toFixed(2) + ' GB';
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024) return Math.round(b / 1024) + ' KB';
        return b + ' B';
    }
    function toast(msg, ok) {
        var t = document.querySelector('.ck-toast-box');
        if (!t) { t = document.createElement('div'); t.className = 'ck-toast-box'; t.setAttribute('role', 'status'); t.setAttribute('aria-live', 'polite'); document.body.appendChild(t); }
        var el = document.createElement('div');
        el.className = 'ck-toast' + (ok === false ? ' is-error' : '');
        el.textContent = msg;
        t.appendChild(el);
        setTimeout(function () { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 320); }, ok === false ? 9000 : 7000);
    }

    function selected() {
        return Array.prototype.map.call(list.querySelectorAll('input[data-bucket]:checked'), function (i) { return i.dataset.bucket; });
    }

    function paintTotals() {
        if (!data) return;
        var pick = selected(), free = 0;
        pick.forEach(function (k) { free += (data[k] && data[k].cleanable) || 0; });
        freeEl.textContent = human(free);
        btn.disabled = !pick.length || free <= 0;
        btn.classList.toggle('is-risky', pick.some(function (k) { return RISKY[k]; }));
    }

    function render(buckets, partial) {
        data = buckets;
        var keys = Object.keys(buckets), total = 0, max = 1;
        keys.forEach(function (k) { total += buckets[k].bytes; max = Math.max(max, buckets[k].bytes); });
        totalEl.textContent = 'সাইটের ডেটা মোট ' + human(total) + (partial ? ' (আংশিক — ফাইল অনেক বেশি)' : '');

        // stacked bar
        bar.innerHTML = keys.filter(function (k) { return buckets[k].bytes > 0; }).map(function (k) {
            return '<i style="flex-grow:' + Math.max(1, Math.round(buckets[k].bytes / 1024)) + ';background:' + (COLORS[k] || '#94a3b8') + '" title="' + esc(buckets[k].label + ' · ' + human(buckets[k].bytes)) + '"></i>';
        }).join('');

        // remember what the admin ticked between measurements; first time: the safe ones
        if (!chosen) {
            chosen = {};
            keys.forEach(function (k) { if (buckets[k].safe === true) chosen[k] = true; });
        }
        // largest first, read-only ones (uploads) last
        keys.sort(function (a, b) { return (buckets[a].safe === null) - (buckets[b].safe === null) || buckets[b].bytes - buckets[a].bytes; });
        list.innerHTML = keys.map(function (k) {
            var b = buckets[k], ro = b.safe === null, pct = Math.round(b.bytes / max * 100);
            var right = ro ? '<span class="ck-store-ro">মুছে ফেলা হয় না</span>'
                : (b.cleanable > 0 ? '<span class="ck-store-gain">− ' + human(b.cleanable) + '</span>' : '<span class="ck-store-zero">খালি</span>');
            return '<li class="ck-store-row' + (ro ? ' is-ro' : '') + (RISKY[k] ? ' is-risky' : '') + '">' +
                (ro ? '<span class="ck-store-check"></span>' :
                    '<label class="ck-store-check"><input type="checkbox" class="form-check-input" data-bucket="' + esc(k) + '"' + (chosen[k] && b.cleanable > 0 ? ' checked' : '') + (b.cleanable > 0 ? '' : ' disabled') + ' aria-label="' + esc(b.label) + '"></label>') +
                '<span class="ck-store-dot" style="background:' + (COLORS[k] || '#94a3b8') + '"></span>' +
                '<div class="ck-store-name"><strong>' + esc(b.label) + (RISKY[k] ? ' <em>confirm</em>' : '') + '</strong><small>' + esc(b.hint) + '</small></div>' +
                '<div class="ck-store-size"><b>' + human(b.bytes) + '</b><small>' + Number(b.files).toLocaleString('en-IN') + ' files</small>' +
                '<span class="ck-store-mini"><i style="width:' + pct + '%;background:' + (COLORS[k] || '#94a3b8') + '"></i></span></div>' +
                '<div class="ck-store-right">' + right + '</div></li>';
        }).join('');
        paintTotals();
    }

    function measure() {
        btn.disabled = true;
        again.disabled = true;
        box.classList.add('is-measuring');
        return fetch(box.dataset.measure, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j && j.success) render(j.buckets, j.partial); else throw new Error(); })
            .catch(function () { totalEl.textContent = 'মাপা যায়নি — আবার চেষ্টা করুন।'; })
            .finally(function () { again.disabled = false; box.classList.remove('is-measuring'); });
    }

    list.addEventListener('change', function (e) {
        var i = e.target.closest('input[data-bucket]');
        if (!i) return;
        chosen[i.dataset.bucket] = i.checked;
        paintTotals();
    });

    function doClean(keys, confirmed) {
        var label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>খালি করা হচ্ছে…';
        var body = new FormData();
        keys.forEach(function (k) { body.append('buckets[]', k); });
        if (confirmed) body.append('confirm', '1');
        fetch(box.dataset.clean, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json().catch(function () { return { success: false, message: 'সার্ভার থেকে অপ্রত্যাশিত উত্তর (' + r.status + ')' }; }); })
            .then(function (j) {
                toast(j.message || (j.success ? 'সম্পন্ন' : 'হয়নি'), !!j.success);
                if (j.success) {
                    box.classList.add('is-done');
                    setTimeout(function () { box.classList.remove('is-done'); }, 2500);
                    // checked risky buckets are unticked again after use
                    Object.keys(RISKY).forEach(function (k) { chosen[k] = false; });
                }
            })
            .catch(function () { toast('সংযোগ পাওয়া যায়নি। আবার চেষ্টা করুন।', false); })
            .finally(function () { btn.innerHTML = label; measure(); });
    }

    btn.addEventListener('click', function () {
        var keys = selected();
        if (!keys.length) return;
        var risky = keys.filter(function (k) { return RISKY[k]; });
        if (!risky.length) return doClean(keys, false);
        var names = risky.map(function (k) { return data[k].label; }).join(' ও ');
        var text = names + ' মুছে ফেলা হবে — এগুলো আর ফেরত আনা যাবে না' + (risky.indexOf('backups') > -1 ? ' (সবচেয়ে নতুন ব্যাকআপটি থাকবে)' : '') + '।';
        if (typeof window.SwalConfirm === 'function') {
            window.SwalConfirm({ title: 'নিশ্চিত?', text: text, icon: 'warning', confirmButtonText: 'হ্যাঁ, মুছুন' }).then(function (r) { if (r.isConfirmed) doClean(keys, true); });
        } else if (window.confirm(text)) {
            doClean(keys, true);
        }
    });
    again.addEventListener('click', measure);

    measure();
})();
