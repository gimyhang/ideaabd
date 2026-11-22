/* Admin › SEO dashboard: gauge, step-by-step scan, one-click fix, editor with live score. */
(function () {
    'use strict';
    var page = document.querySelector('.seo-page');
    if (!page) return;
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var items = window.seoItems || {};

    function $(s, r) { return (r || document).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function tone(s) { return s >= 100 ? 'perfect' : s >= 80 ? 'good' : s >= 50 ? 'fair' : 'poor'; }
    function len(s) { return Array.from(String(s || '')).length; }

    function send(url, data, method) {
        var body = new FormData();
        Object.keys(data || {}).forEach(function (k) { if (data[k] != null) body.append(k, data[k]); });
        if (method && method !== 'POST') body.append('_method', method);
        return fetch(url, { method: method === 'GET' ? 'GET' : 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: method === 'GET' ? undefined : body, credentials: 'same-origin' })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { if (!r.ok) { var e = new Error(j.message || ('Error ' + r.status)); e.status = r.status; e.body = j; throw e; } return j; }); });
    }

    var toastEl = $('[data-toast]'), toastT;
    function toast(msg, bad) {
        if (!toastEl) return;
        toastEl.textContent = msg; toastEl.className = 'seo-toast' + (bad ? ' bad' : ''); toastEl.hidden = false;
        clearTimeout(toastT); toastT = setTimeout(function () { toastEl.hidden = true; }, 3200);
    }

    /* ---------- score rings ---------- */
    function setRing(el, score) {
        if (!el) return;
        var c = el.querySelector('circle.v'), r = 15, C = 2 * Math.PI * r;
        if (c) c.setAttribute('stroke-dashoffset', (C * (1 - Math.max(0, Math.min(100, score)) / 100)).toFixed(2));
        var b = el.querySelector('b'); if (b) b.textContent = score;
        el.className = el.className.replace(/seo-tone-\w+/g, '').trim() + ' seo-tone-' + tone(score);
    }

    /* ---------- hero gauge: animate in ---------- */
    var gauge = $('[data-gauge]');
    if (gauge) {
        var v = gauge.querySelector('.seo-gauge-value'), num = gauge.querySelector('[data-gauge-num]'), target = +gauge.dataset.score || 0;
        requestAnimationFrame(function () { setTimeout(function () { v.setAttribute('stroke-dashoffset', v.dataset.targetOffset); }, 60); });
        if (num && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            var t0 = null;
            (function step(t) { t0 = t0 || t; var p = Math.min(1, (t - t0) / 900); num.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); })(performance.now());
        }
    }

    /* ---------- filters ---------- */
    var f = document.getElementById('seoFilters');
    if (f) {
        var ft;
        f.elements.search.addEventListener('input', function () { clearTimeout(ft); ft = setTimeout(function () { f.submit(); }, 500); });
        ['filter_type', 'score_level', 'issue'].forEach(function (n) { if (f.elements[n]) f.elements[n].addEventListener('change', function () { f.submit(); }); });
    }

    /* ---------- full scan, slice by slice (no request runs long enough to time out) ---------- */
    var scanning = false;
    var NAMES = { books: 'Books', blog: 'Posts', authors: 'Authors', publishers: 'Publishers', ebooks: 'Ebooks', webzines: 'Webzines', research: 'Research', pages: 'Pages', cleanup: 'Cleaning up' };
    function scanAll() {
        if (scanning) return;
        if (!confirm('Scan every book, post, author and page and rewrite their tags?\nTitles and descriptions you wrote by hand are kept.')) return;
        scanning = true;
        var box = $('[data-scan-box]'), fill = $('[data-scan-fill]'), label = $('[data-scan-label]'), pct = $('[data-scan-pct]'), acts = $('[data-scan-actions]');
        box.hidden = false; if (acts) acts.hidden = true;
        $$('[data-seo-scan]').forEach(function (b) { b.disabled = true; });
        window.addEventListener('beforeunload', guard);
        var done = 0, total = 1, retries = 0;
        function show(kind) {
            var p = Math.min(100, Math.round(done * 100 / total));
            fill.style.width = p + '%'; pct.textContent = p + '%';
            label.textContent = (NAMES[kind] || kind) + ' · ' + done.toLocaleString() + ' / ' + total.toLocaleString();
        }
        function next(kind, after) {
            show(kind);
            send(page.dataset.stepUrl, { kind: kind, after: after || 0 }).then(function (r) {
                retries = 0; done += r.scanned || 0; show(r.kind);
                if (r.next) return next(r.next, r.after);
                fill.style.width = '100%'; pct.textContent = '100%';
                label.textContent = 'Done — average ' + (r.overview ? r.overview.avg : '') + '/100' + (r.removed ? ' · ' + r.removed + ' stale removed' : '');
                window.removeEventListener('beforeunload', guard);
                setTimeout(function () { location.reload(); }, 900);
            }).catch(function (e) {
                if (retries++ < 3) { setTimeout(function () { next(kind, after); }, 1500 * retries); return; }
                scanning = false; label.textContent = 'Stopped: ' + e.message + ' — press Scan to continue.';
                $$('[data-seo-scan]').forEach(function (b) { b.disabled = false; });
                if (acts) acts.hidden = false;
                window.removeEventListener('beforeunload', guard);
            });
        }
        send(page.dataset.planUrl, {}, 'GET').then(function (plan) {
            total = Math.max(1, plan.total || 1);
            next(plan.steps[0] || 'pages', 0);
        }).catch(function () { total = 1; next('books', 0); });
    }
    function guard(e) { e.preventDefault(); e.returnValue = ''; }
    $$('[data-seo-scan]').forEach(function (b) { b.addEventListener('click', scanAll); });

    /* ---------- table rows ---------- */
    function paintRow(it) {
        items[it.id] = it;
        var row = document.querySelector('tr[data-row="' + it.id + '"]');
        if (!row) return;
        setRing(row.querySelector('[data-cell="score"]'), it.seo_score);
        var t = row.querySelector('[data-cell="title"]'); if (t) { t.textContent = it.meta_title || 'No title'; t.title = it.meta_title; }
        var d = row.querySelector('[data-cell="desc"]'); if (d) { d.textContent = it.meta_description || 'No description'; d.title = it.meta_description; }
        var k = row.querySelector('[data-cell="keyword"]');
        if (k) k.outerHTML = it.focus_keyword ? '<span class="seo-kw" data-cell="keyword"><i class="fa-solid fa-key"></i>' + esc(it.focus_keyword.length > 34 ? it.focus_keyword.slice(0, 33) + '…' : it.focus_keyword) + '</span>' : '<span class="text-muted small" data-cell="keyword">—</span>';
        var l = row.querySelector('[data-cell="desclen"]'); if (l) { var n = len(it.meta_description); l.textContent = n + ' chars'; l.className = 'seo-len ' + (n >= 120 && n <= 160 ? 'ok' : 'warn'); }
        var fx = row.querySelector('[data-fix]'); if (fx) fx.hidden = it.seo_score >= 100;
        row.classList.add('seo-flash'); setTimeout(function () { row.classList.remove('seo-flash'); }, 1200);
    }
    function fix(id, btn) {
        var it = items[id]; if (!it) return Promise.resolve();
        if (it.manual && !confirm('This page has text written by hand. Replace it with text generated from the page?')) return Promise.resolve();
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
        return send(it.fix_url, {}).then(function (r) {
            if (r.removed) { var row = document.querySelector('tr[data-row="' + id + '"]'); if (row) row.remove(); toast(r.message); return null; }
            paintRow(r.item); toast(r.message); return r.item;
        }).catch(function (e) { toast(e.message, true); }).finally(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Fix'; }
        });
    }
    document.addEventListener('click', function (e) {
        var fb = e.target.closest('[data-fix]'); if (fb) { fix(fb.dataset.fix, fb); return; }
        var eb = e.target.closest('[data-edit]'); if (eb) openEditor(eb.dataset.edit);
    });

    /* ---------- editor ---------- */
    var modalEl = document.getElementById('seoEditModal'), form = document.getElementById('editSeoForm');
    if (!modalEl || !form) return;
    var modal = window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var cur = null, at;
    var F = { title: form.meta_title, desc: form.meta_description, focus: form.focus_keyword, kw: form.meta_keywords, canon: form.canonical_url, img: form.og_image, robots: form.robots };
    var M = function (k) { return modalEl.querySelector('[data-m="' + k + '"]'); };
    var P = function (k) { return modalEl.querySelector('[data-p="' + k + '"]'); };

    function openEditor(id) {
        cur = items[id]; if (!cur) return;
        F.title.value = cur.meta_title; F.desc.value = cur.meta_description; F.focus.value = cur.focus_keyword;
        F.kw.value = cur.meta_keywords; F.canon.value = cur.canonical_url; F.img.value = cur.og_image; F.robots.value = cur.robots || 'index, follow';
        M('type').textContent = cur.type; M('path').textContent = cur.url_path || cur.canonical_url;
        M('error').hidden = true;
        renderChecks(cur.checks, cur.seo_score);
        preview();
        if (modal) modal.show();
    }

    function meter(key, n, lo, hi, max) {
        var c = modalEl.querySelector('[data-count="' + key + '"]'), m = modalEl.querySelector('[data-meter="' + key + '"]');
        var state = n === 0 ? 'bad' : (n >= lo && n <= hi ? 'ok' : (n > hi ? 'warn' : 'bad'));
        c.textContent = n + ' / ' + hi; c.className = 'seo-count ' + state;
        m.style.width = Math.min(100, n * 100 / max) + '%'; m.className = state;
    }
    function hl(text, word) {
        var t = esc(text); if (!word) return t;
        var w = esc(word), i = t.toLowerCase().indexOf(w.toLowerCase());
        return i < 0 ? t : t.slice(0, i) + '<b>' + t.slice(i, i + w.length) + '</b>' + t.slice(i + w.length);
    }
    function preview() {
        var t = F.title.value.trim(), d = F.desc.value.trim(), k = F.focus.value.trim();
        meter('title', len(t), 30, 65, 80); meter('desc', len(d), 120, 160, 200);
        P('title').innerHTML = hl(Array.from(t).length > 65 ? Array.from(t).slice(0, 64).join('') + '…' : t || 'Page title', k);
        P('desc').innerHTML = hl(Array.from(d).length > 160 ? Array.from(d).slice(0, 159).join('') + '…' : d || 'Description', k);
        var url = F.canon.value.trim() || ('https://www.ideaabd.com' + (cur.url_path || '/'));
        P('url').textContent = url.replace(/^https?:\/\//, '').replace(/\//g, ' › ');
        P('stitle').textContent = t || 'Page title'; P('sdesc').textContent = d || 'Description';
        try { P('host').textContent = new URL(url).host.replace(/^www\./, ''); } catch (e) { }
        var img = F.img.value.trim(), box = P('img');
        if (/^(https?:\/\/|\/)/i.test(img) && !/^\/\//.test(img)) {
            box.innerHTML = ''; var im = new Image(); im.alt = ''; im.loading = 'lazy'; im.src = img;
            im.onerror = function () { box.innerHTML = '<i class="fa-regular fa-image"></i>'; };
            box.appendChild(im);
        } else box.innerHTML = '<i class="fa-regular fa-image"></i>';
    }
    function renderChecks(checks, score) {
        setRing(M('ring'), score || 0);
        M('checks').innerHTML = (checks || []).map(function (c) {
            var ic = c.status === 'pass' ? 'fa-circle-check' : (c.status === 'warn' ? 'fa-circle-exclamation' : 'fa-circle-xmark');
            return '<li class="' + c.status + '"><i class="fa-solid ' + ic + '"></i><div><b>' + esc(c.label) + '</b><span>' + esc(c.hint) + '</span></div><em>' + c.earned + '/' + c.max + '</em></li>';
        }).join('');
    }
    function analyze() {
        M('live').textContent = 'checking…';
        send(page.dataset.analyzeUrl, { meta_title: F.title.value, meta_description: F.desc.value, meta_keywords: F.kw.value, focus_keyword: F.focus.value, og_image: F.img.value })
            .then(function (a) { renderChecks(a.checks, a.score); M('live').textContent = 'live'; })
            .catch(function () { M('live').textContent = 'offline'; });
    }
    form.addEventListener('input', function () { preview(); clearTimeout(at); at = setTimeout(analyze, 350); });
    form.addEventListener('change', function () { clearTimeout(at); at = setTimeout(analyze, 150); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!cur) return;
        var btn = M('save'); btn.disabled = true;
        var data = { meta_title: F.title.value, meta_description: F.desc.value, focus_keyword: F.focus.value, meta_keywords: F.kw.value, canonical_url: F.canon.value, og_image: F.img.value, robots: F.robots.value };
        send(cur.update_url, data, 'PUT').then(function (r) {
            paintRow(r.item); toast(r.message); if (modal) modal.hide();
        }).catch(function (err) {
            var errs = err.body && err.body.errors ? Object.keys(err.body.errors).map(function (k) { return err.body.errors[k][0]; }) : [err.message];
            var box = M('error'); box.innerHTML = errs.map(esc).join('<br>'); box.hidden = false;
        }).finally(function () { btn.disabled = false; });
    });
    M('regen').addEventListener('click', function () {
        if (!cur) return;
        var id = cur.id;
        fix(id).then(function (it) { if (it) openEditor(id); });
    });
})();
