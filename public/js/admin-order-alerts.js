/*
 * Live new-order alert for the admin panel.
 * Polls /admin/ecommerce-orders/pulse (every 20 s while the tab is open, 60 s in the background) and,
 * when a new web order arrives: plays a chime, shows a desktop notification and an on-page toast,
 * updates the bell count and puts "(n)" in the tab title. Several open tabs share one "last seen
 * order" in localStorage, so an order is announced once, not once per tab.
 */
(function () {
    'use strict';
    var cfg = document.getElementById('orderAlertCfg');
    if (!cfg || !window.fetch) return;

    var KEY = 'ideaabd.lastOrderId';
    var baseTitle = document.title.replace(/^\(\d+\)\s*/, '');
    var timer = null;
    var audioCtx = null;

    function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function bn(n) { return Number(n || 0).toLocaleString('en-US').replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }); }

    // ── Sound: a short two-note chime made in the browser (no file to load). Browsers only allow
    //    sound after the user has clicked somewhere on the page, so the context is unlocked on first click.
    function unlockAudio() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch (e) {}
    }
    document.addEventListener('pointerdown', unlockAudio, { once: true, capture: true });
    document.addEventListener('keydown', unlockAudio, { once: true, capture: true });

    function chime() {
        if (!audioCtx || audioCtx.state !== 'running') return;
        var t = audioCtx.currentTime;
        [[880, 0], [1320, 0.18], [880, 0.5], [1320, 0.68]].forEach(function (n) {
            var o = audioCtx.createOscillator(), g = audioCtx.createGain();
            o.type = 'sine';
            o.frequency.value = n[0];
            g.gain.setValueAtTime(0.0001, t + n[1]);
            g.gain.exponentialRampToValueAtTime(0.25, t + n[1] + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, t + n[1] + 0.3);
            o.connect(g).connect(audioCtx.destination);
            o.start(t + n[1]);
            o.stop(t + n[1] + 0.32);
        });
    }

    // ── Bell badge + tab title ───────────────────────────────
    function setCount(n) {
        document.title = (n > 0 ? '(' + n + ') ' : '') + baseTitle;
        var bell = document.querySelector('.adm-iconbtn .fa-bell');
        var btn = bell && bell.closest('button');
        if (!btn) return;
        var badge = btn.querySelector('.badge');
        if (n > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge bg-danger rounded-pill position-absolute top-0 end-0 translate-middle-y';
                badge.style.cssText = 'font-size:.65rem;padding:.25em .5em;';
                btn.appendChild(badge);
            }
            badge.textContent = n;
            bell.classList.add('text-primary');
            btn.title = n + ' pending orders';
        }
    }

    // ── Toasts ───────────────────────────────────────────────
    function toastBox() {
        var box = document.getElementById('orderAlertToasts');
        if (!box) {
            box = document.createElement('div');
            box.id = 'orderAlertToasts';
            box.setAttribute('role', 'status');
            box.setAttribute('aria-live', 'polite');
            box.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:2000;display:flex;flex-direction:column;gap:10px;max-width:min(360px,calc(100vw - 32px));';
            document.body.appendChild(box);
        }
        return box;
    }

    function toast(o) {
        var el = document.createElement('a');
        el.href = o.url;
        el.style.cssText = 'display:block;text-decoration:none;color:#0f172a;background:#fff;border:1px solid #e2e8f0;border-left:5px solid #16a34a;border-radius:14px;padding:12px 14px;box-shadow:0 18px 40px -16px rgba(15,23,42,.35);font-size:13px;line-height:1.45;';
        el.innerHTML =
            '<div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:2px;">' +
                '<strong style="color:#16a34a;">🛒 নতুন অর্ডার #' + esc(o.number) + '</strong>' +
                '<span style="font-weight:800;">৳' + bn(Math.round(o.total)) + '</span>' +
            '</div>' +
            '<div style="font-weight:600;">' + esc(o.name) + ' · ' + esc(o.phone) + '</div>' +
            '<div style="color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(o.items) + '</div>' +
            '<div style="color:#2563eb;font-weight:700;margin-top:4px;">অর্ডারটি দেখুন →</div>';
        toastBox().appendChild(el);
        setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 450); }, 60000);
    }

    function desktop(o) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            var n = new Notification('নতুন অর্ডার #' + o.number + ' — ৳' + Math.round(o.total), {
                body: o.name + ' · ' + o.phone + '\n' + o.items,
                tag: 'order-' + o.id,
                requireInteraction: true
            });
            n.onclick = function () { window.focus(); location.href = o.url; n.close(); };
        } catch (e) {}
    }

    // ── One-time prompt to allow desktop notifications (needs a click) ──
    function offerPermission() {
        if (!('Notification' in window) || Notification.permission !== 'default' || store('ideaabd.orderAlertAsked') === '1') return;
        var b = document.createElement('button');
        b.type = 'button';
        b.textContent = '🔔 নতুন অর্ডারের নোটিফিকেশন চালু করুন';
        b.style.cssText = 'border:0;border-radius:999px;padding:10px 16px;background:#0f172a;color:#fff;font-weight:700;font-size:13px;box-shadow:0 10px 24px -10px rgba(15,23,42,.6);cursor:pointer;';
        b.onclick = function () {
            unlockAudio();
            store('ideaabd.orderAlertAsked', '1');
            Notification.requestPermission().finally(function () { b.remove(); });
        };
        toastBox().appendChild(b);
    }

    // ── Polling ──────────────────────────────────────────────
    function poll() {
        clearTimeout(timer);
        // null = this browser has never checked (first visit); 0 is a real "no orders yet".
        var saved = store(KEY);
        var since = (saved === null || saved === '' || isNaN(parseInt(saved, 10))) ? null : parseInt(saved, 10);
        var url = cfg.dataset.pulse + (since === null ? '' : (cfg.dataset.pulse.indexOf('?') > -1 ? '&' : '?') + 'since=' + since);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) return;
                setCount(d.pending || 0);
                if (since === null) { store(KEY, String(d.latest_id || 0)); return; } // first visit: start from now, no alert
                if (d.latest_id > since) store(KEY, String(d.latest_id));
                if (d['new'] && d['new'].length) {
                    chime();
                    d['new'].slice().reverse().forEach(function (o) { toast(o); desktop(o); });
                }
            })
            .catch(function () {})
            .then(function () { timer = setTimeout(poll, document.hidden ? 60000 : 20000); });
    }

    document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
    window.addEventListener('storage', function (e) { if (e.key === KEY) clearTimeout(timer), timer = setTimeout(poll, 1500); });

    offerPermission();
    poll();
})();
