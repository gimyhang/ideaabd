/* Print pages on phones: a printable sheet (form, invitation card) keeps its real paper width and is scaled down to
 * the screen like a print preview, instead of squeezing and breaking its tables. Pinch-zoom still works.
 *
 *   <div data-print-fit="816">…</div>      816 = the sheet's width in CSS px (8.5in); data-print-gutter="12" optional
 *
 * Printing / PDF are not affected (see the @media print rule this script adds). */
(function () {
    'use strict';
    var els = [];

    function style() {
        if (document.getElementById('print-fit-style')) return;
        var s = document.createElement('style');
        s.id = 'print-fit-style';
        s.textContent = '.pf-sizer{position:relative;margin:0 auto;overflow:visible}' +
            '.pf-sizer>[data-print-fit]{transform-origin:top left}' +
            '@media print{.pf-sizer{width:auto!important;height:auto!important;margin:0!important}' +
            '.pf-sizer>[data-print-fit]{transform:none!important}}';
        document.head.appendChild(s);
    }

    function sizerOf(el) {
        var p = el.parentElement;
        if (p && p.classList.contains('pf-sizer')) return p;
        var w = document.createElement('div');
        w.className = 'pf-sizer';
        el.parentNode.insertBefore(w, el);
        w.appendChild(el);
        return w;
    }

    function fit(el) {
        var natural = parseFloat(el.getAttribute('data-print-fit')) || el.offsetWidth;
        var gutter = parseFloat(el.getAttribute('data-print-gutter') || '12');
        var sizer = sizerOf(el);
        var avail = Math.max(200, document.documentElement.clientWidth - gutter);
        var s = Math.min(1, avail / natural);
        el.style.width = natural + 'px';
        el.style.maxWidth = 'none';
        el.style.minWidth = natural + 'px';
        el.style.marginLeft = '0';
        el.style.marginRight = '0';
        el.style.transform = s < 1 ? 'scale(' + s + ')' : 'none';
        sizer.style.width = Math.floor(natural * s) + 'px';
        sizer.style.height = Math.ceil(el.offsetHeight * s) + 'px';
        el.classList.toggle('is-print-scaled', s < 1);
    }

    function fitAll() { els.forEach(fit); }

    function init() {
        style();
        els = Array.prototype.slice.call(document.querySelectorAll('[data-print-fit]'));
        if (!els.length) return;
        fitAll();
        var t = null;
        var later = function () { clearTimeout(t); t = setTimeout(fitAll, 60); };
        window.addEventListener('resize', later);
        window.addEventListener('orientationchange', function () { setTimeout(fitAll, 250); });
        window.addEventListener('load', fitAll);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitAll);
        // content that grows after load (images, fonts) keeps the frame tall enough
        if (window.ResizeObserver) {
            var ro = new ResizeObserver(later);
            els.forEach(function (el) { ro.observe(el); });
        }
        // the print dialog lays the sheet out at its paper size; restore the preview afterwards
        window.addEventListener('afterprint', fitAll);
    }

    window.IdeaPrintFit = { refresh: fitAll };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
