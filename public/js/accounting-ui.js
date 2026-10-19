/* Accounting UI helpers: button press ripple + duplicate-submit guard. */
(function () {
    'use strict';
    var doc = document;
    if (!doc.documentElement.classList.contains('acc-ui')) return;

    doc.addEventListener('pointerdown', function (e) {
        var btn = e.target.closest && e.target.closest('.btn');
        if (!btn || btn.disabled || btn.classList.contains('disabled')) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        var r = btn.getBoundingClientRect(), size = Math.max(r.width, r.height);
        var dot = doc.createElement('span');
        dot.className = 'ac-ripple';
        dot.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - r.left - size / 2) + 'px;top:' + (e.clientY - r.top - size / 2) + 'px';
        btn.appendChild(dot);
        setTimeout(function () { dot.remove(); }, 600);
    }, { passive: true });

    // A second submit on the same form (double click) would create a duplicate invoice/payment.
    doc.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM' || (form.method || '').toLowerCase() !== 'post') return;
        if (form.dataset.acSubmitting === '1') { e.preventDefault(); return; }
        form.dataset.acSubmitting = '1';
        setTimeout(function () { delete form.dataset.acSubmitting; }, 4000);
    }, true);
    // If another handler cancelled the submit (confirm, validation), release the guard.
    doc.addEventListener('submit', function (e) {
        if (e.defaultPrevented && e.target.dataset) delete e.target.dataset.acSubmitting;
    });
})();
