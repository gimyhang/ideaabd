/*
 * IdeaABD admin — UX enhancements (progressive; every feature is optional)
 * 1. Skip link + main landmark       5. Back-to-top button
 * 2. Top progress bar on navigation  6. Offline banner
 * 3. Submit-button loading state     7. Auto-dismiss success alerts
 * 4. Table scroll wrappers + shadows 8. [data-copy] clipboard buttons
 */
(function () {
    'use strict';
    var doc = document, body = doc.body;

    /* 1 ── Accessibility landmarks */
    var content = doc.querySelector('.adm-content');
    if (content) {
        if (!content.id) content.id = 'admMainContent';
        content.setAttribute('role', 'main');
        var skip = doc.createElement('a');
        skip.className = 'adm-skip-link';
        skip.href = '#' + content.id;
        skip.textContent = 'মূল কনটেন্টে যান';
        body.insertBefore(skip, body.firstChild);
    }

    /* 2 ── Progress bar */
    var bar = doc.createElement('div');
    bar.id = 'admProgress';
    body.appendChild(bar);
    var timer;
    function startProgress() {
        bar.classList.add('on'); bar.style.width = '25%';
        clearInterval(timer);
        timer = setInterval(function () {
            var w = parseFloat(bar.style.width) || 0;
            if (w < 90) bar.style.width = (w + (90 - w) * 0.12) + '%';
        }, 250);
    }
    function stopProgress() {
        clearInterval(timer); bar.style.width = '100%';
        setTimeout(function () { bar.classList.remove('on'); bar.style.width = '0'; }, 300);
    }
    doc.addEventListener('click', function (e) {
        var a = e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return;
        if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-confirm') || a.getAttribute('onclick')) return;
        if (a.origin && a.origin !== location.origin) return;
        startProgress();
    });
    window.addEventListener('pageshow', function () { stopProgress(); resetLoading(); });

    /* 3 ── Submit loading state (prevents double submits) */
    function resetLoading() {
        doc.querySelectorAll('.btn.is-loading').forEach(function (b) {
            b.classList.remove('is-loading'); b.disabled = false;
            var s = b.querySelector('.adm-spin'); if (s) s.remove();
        });
    }
    doc.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM' || form.target === '_blank' || form.hasAttribute('data-no-loading')) return;
        var submitter = e.submitter;
        // Run after other handlers (confirm dialogs call preventDefault) have had their say
        setTimeout(function () {
            if (e.defaultPrevented) return;
            var btn = submitter || form.querySelector('button[type="submit"]');
            if (!btn || btn.tagName !== 'BUTTON' || btn.classList.contains('is-loading')) return;
            var spin = doc.createElement('span'); spin.className = 'adm-spin'; spin.setAttribute('aria-hidden', 'true');
            btn.insertBefore(spin, btn.firstChild);
            btn.classList.add('is-loading');
            startProgress();
            // Safety net: downloads or failed requests never reload the page
            setTimeout(function () { stopProgress(); resetLoading(); }, 15000);
        }, 0);
    });
    // Block repeat clicks while loading (pointer-events is CSS-only; Enter key still fires)
    doc.addEventListener('submit', function (e) {
        var b = e.submitter;
        if (b && b.classList && b.classList.contains('is-loading')) e.preventDefault();
    }, true);

    /* 4 ── Table scroll wrappers */
    function updateShadows(w) {
        w.classList.toggle('can-scroll-l', w.scrollLeft > 4);
        w.classList.toggle('can-scroll-r', w.scrollLeft + w.clientWidth < w.scrollWidth - 4);
    }
    function wrapTables() {
        doc.querySelectorAll('.adm-content table.table').forEach(function (t) {
            if (t.closest('.table-responsive, .adm-table-wrap, .modal, [data-no-wrap]')) return;
            var w = doc.createElement('div'); w.className = 'adm-table-wrap';
            t.parentNode.insertBefore(w, t); w.appendChild(t);
        });
        doc.querySelectorAll('.adm-table-wrap, .table-responsive').forEach(function (w) {
            if (w._admShadow) { updateShadows(w); return; }
            w._admShadow = true;
            w.addEventListener('scroll', function () { updateShadows(w); }, { passive: true });
            updateShadows(w);
        });
    }
    wrapTables();
    window.addEventListener('resize', wrapTables);
    window.addEventListener('load', wrapTables);

    /* 5 ── Back to top */
    var top = doc.createElement('button');
    top.id = 'admToTop'; top.type = 'button'; top.setAttribute('aria-label', 'উপরে যান');
    top.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
    body.appendChild(top);
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return; ticking = true;
        requestAnimationFrame(function () { top.classList.toggle('show', window.scrollY > 500); ticking = false; });
    }, { passive: true });

    /* 6 ── Offline banner */
    var off = doc.createElement('div');
    off.id = 'admOffline'; off.setAttribute('role', 'status');
    off.innerHTML = '<i class="fa-solid fa-wifi me-2"></i>ইন্টারনেট সংযোগ নেই';
    body.appendChild(off);
    function net() { off.classList.toggle('show', !navigator.onLine); }
    window.addEventListener('online', net); window.addEventListener('offline', net); net();

    /* 7 ── Auto-dismiss success/info alerts */
    setTimeout(function () {
        doc.querySelectorAll('.adm-content > .alert-success, .adm-content > .alert-info').forEach(function (a) {
            if (window.bootstrap && bootstrap.Alert) { try { bootstrap.Alert.getOrCreateInstance(a).close(); } catch (e) { a.remove(); } }
            else a.remove();
        });
    }, 6000);

    /* 8 ── Copy to clipboard: <button data-copy="text"> or data-copy-target="#id" */
    doc.addEventListener('click', function (e) {
        var b = e.target.closest('[data-copy], [data-copy-target]');
        if (!b) return;
        var el = b.dataset.copyTarget ? doc.querySelector(b.dataset.copyTarget) : null;
        var text = b.dataset.copy || (el ? (el.value || el.textContent).trim() : '');
        if (!text) return;
        var done = function () { if (window.SwalToast) SwalToast('success', 'কপি হয়েছে'); };
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done);
        else {
            var ta = doc.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            body.appendChild(ta); ta.select(); try { doc.execCommand('copy'); done(); } catch (err) {} ta.remove();
        }
    });
})();
