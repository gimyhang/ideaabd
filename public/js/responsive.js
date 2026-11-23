/**
 * Shared device helper for every layout (pair: public/css/responsive.css).
 *  - A table wider than its column gets a sideways-scroll box (.rsp-scroll) instead of pushing the page wide.
 *    Runs on load, on resize/rotation and for tables added later (AJAX lists, modals).
 *  - Sets --vh to the real visible height (phone browser bars) for full-height panels.
 * Skips tables that already sit in a scrolling box, and anything marked data-rsp="off".
 */
(function () {
    'use strict';

    function inScrollBox(el) {
        for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
            if (p.classList.contains('rsp-scroll') || p.classList.contains('table-responsive')) return true;
            const ox = getComputedStyle(p).overflowX;
            if (ox === 'auto' || ox === 'scroll') return true;   // a clipping box (hidden) would cut the table off, so it still gets wrapped
        }
        return false;
    }

    function fitTable(t) {
        if (t.closest('[data-rsp="off"]') || !t.isConnected) return;
        const wrap = t.parentElement;
        if (wrap && wrap.classList.contains('rsp-scroll')) {
            wrap.classList.toggle('is-scrollable', wrap.scrollWidth > wrap.clientWidth + 1);
            return;
        }
        if (inScrollBox(t)) return;
        const parent = t.parentElement;
        if (!parent || t.offsetParent === null) return;          // hidden for now, checked again when shown
        const room = parent.clientWidth;
        if (!room || t.getBoundingClientRect().width <= room + 1) return;
        const box = document.createElement('div');
        box.className = 'rsp-scroll';
        parent.insertBefore(box, t);
        box.appendChild(t);
        box.classList.toggle('is-scrollable', box.scrollWidth > box.clientWidth + 1);
    }

    function fitAll(root) {
        (root || document).querySelectorAll('table').forEach(fitTable);
    }

    let raf = 0;
    function schedule() {
        if (raf) return;
        raf = requestAnimationFrame(() => { raf = 0; fitAll(); });
    }

    function setVh() {
        document.documentElement.style.setProperty('--vh', (window.innerHeight * 0.01) + 'px');
    }

    function start() {
        setVh();
        fitAll();
        window.addEventListener('resize', () => { setVh(); schedule(); }, { passive: true });
        window.addEventListener('orientationchange', () => { setVh(); schedule(); });
        // tables shown later: tabs, modals, collapses
        ['shown.bs.tab', 'shown.bs.modal', 'shown.bs.collapse', 'shown.bs.offcanvas'].forEach(ev => document.addEventListener(ev, schedule));
        // tables added later (AJAX)
        if ('MutationObserver' in window) {
            new MutationObserver(muts => {
                for (const m of muts) {
                    for (const n of m.addedNodes) {
                        if (n.nodeType === 1 && (n.tagName === 'TABLE' || n.querySelector?.('table'))) { schedule(); return; }
                    }
                }
            }).observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
    window.addEventListener('load', schedule);
})();
