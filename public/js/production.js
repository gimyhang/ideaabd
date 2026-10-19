/* Production ledger: stage modal + book picker that shows what is waiting at press / binder. */
(function () {
    'use strict';
    var doc = document;
    var LABELS = { printed: ['Printed', 'Press'], bound: ['Bound', 'Binder'], received: ['Received', 'Binder'] };
    var timer;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    doc.addEventListener('DOMContentLoaded', function () {
        var modal = doc.getElementById('stageModal');
        var form = doc.getElementById('stageForm');
        if (!modal || !form) return;

        var stageInput = doc.getElementById('stageInput');
        var title = doc.getElementById('stageTitle');
        var vendorLabel = doc.getElementById('vendorLabel');
        var search = doc.getElementById('bookSearch');
        var idInput = doc.getElementById('bookId');
        var list = doc.getElementById('bookList');
        var hint = doc.getElementById('bookHint');

        function setStage(stage) {
            var l = LABELS[stage] || LABELS.printed;
            stageInput.value = stage;
            title.textContent = l[0];
            vendorLabel.textContent = l[1];
        }

        modal.addEventListener('show.bs.modal', function (e) {
            var trigger = e.relatedTarget;
            setStage((trigger && trigger.dataset.stage) || 'printed');
            hint.hidden = true;
        });

        function load() {
            var url = new URL(form.dataset.books, location.origin);
            url.searchParams.set('q', search.value.trim());
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(function (books) {
                    list.innerHTML = books.length ? books.map(function (b) {
                        return '<button type="button" data-id="' + b.id + '" data-title="' + esc(b.title) + '" data-press="' + b.at_press + '" data-binder="' + b.at_binder + '" data-stock="' + b.stock + '"><span>' + esc(b.title) + '</span><small>' + b.stock + ' in stock</small></button>';
                    }).join('') : '<div class="iv-pick-empty">None</div>';
                    list.hidden = false;
                })
                .catch(function () { list.hidden = true; });
        }

        search.addEventListener('input', function () {
            idInput.value = '';
            hint.hidden = true;
            clearTimeout(timer);
            timer = setTimeout(load, 220);
        });
        search.addEventListener('focus', function () { if (!search.value) load(); });

        list.addEventListener('click', function (e) {
            var b = e.target.closest('button[data-id]');
            if (!b) return;
            idInput.value = b.dataset.id;
            search.value = b.dataset.title;
            list.hidden = true;
            hint.innerHTML = '<span>Press <b>' + b.dataset.press + '</b></span><span>Binder <b>' + b.dataset.binder + '</b></span><span>Stock <b>' + b.dataset.stock + '</b></span>';
            hint.hidden = false;
            form.elements.quantity.focus();
        });

        doc.addEventListener('click', function (e) {
            if (!list.contains(e.target) && e.target !== search) list.hidden = true;
        });

        form.addEventListener('submit', function (e) {
            if (!idInput.value) { e.preventDefault(); search.focus(); search.classList.add('is-invalid'); }
        });
    });
})();
