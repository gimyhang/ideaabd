/* Money receipts: honorarium paid / bill money received (dashboard + /admin/receipts) */
(function () {
    'use strict';

    var modalEl = document.getElementById('mrModal');
    if (!modalEl) return;

    var form = document.getElementById('mrForm');
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var kind = 'honorarium';
    var due = 0;
    var bn = function (s) { return String(s).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }); };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
    var money = function (n) { return '৳' + bn(Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })); };
    var modal = function () { return bootstrap.Modal.getOrCreateInstance(modalEl); };

    var TEXT = {
        honorarium: { title: 'Honorarium', sub: 'লেখককে সম্মানী প্রদানের রসিদ', icon: 'fa-hand-holding-dollar', done: 'সম্মানী প্রদানের রসিদ তৈরি হয়েছে' },
        bill: { title: 'Receipt', sub: 'বিলের অর্থ গ্রহণের মানি রিসিট', icon: 'fa-receipt', done: 'মানি রিসিট তৈরি হয়েছে' },
    };

    function show(el, on) { if (el) el.hidden = !on; }

    function sync() {
        var linked = !!form.invoice_id.value;
        modalEl.querySelectorAll('[data-for]').forEach(function (el) {
            var f = el.getAttribute('data-for');
            show(el, f === kind || (f === 'bill-free' && kind === 'bill' && !linked));
        });
        modalEl.querySelector('#mrFullDue').hidden = !(kind === 'bill' && linked);
        modalEl.classList.toggle('is-bill', kind === 'bill');
    }

    function setKind(k) {
        kind = TEXT[k] ? k : 'honorarium';
        form.kind.value = kind;
        modalEl.querySelectorAll('[data-mr-kind]').forEach(function (b) { b.classList.toggle('is-on', b.dataset.mrKind === kind); });
        document.getElementById('mrTitle').textContent = TEXT[kind].title;
        document.getElementById('mrSub').textContent = TEXT[kind].sub;
        document.getElementById('mrIcon').innerHTML = '<i class="fa-solid ' + TEXT[kind].icon + '"></i>';
        // writer search only for honorarium; the bill box searches invoices
        document.getElementById('mrName').setAttribute('data-lookup-type', kind === 'honorarium' ? 'author' : '');
        form.author_id.value = '';
        if (kind !== 'honorarium') { document.getElementById('mrBookRows').innerHTML = ''; }
        unlinkBill();
        sync();
        if (typeof recalcBooks === 'function') recalcBooks();
    }

    function reset() {
        form.reset();
        form.author_id.value = '';
        authorRoyalty = null;
        document.getElementById('mrBookRows').innerHTML = '';
        recalcBooks();
        unlinkBill();
        document.getElementById('mrDate').value = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
        document.getElementById('mrError').hidden = true;
        document.getElementById('mrWords').textContent = '';
        show(document.getElementById('mrBody'), true);
        show(document.getElementById('mrFoot'), true);
        show(document.getElementById('mrDone'), false);
        modalEl.querySelector('.mr-tabs').hidden = false;
    }

    // ---------------------------------------------------------------- lookup
    var timers = new WeakMap();
    var seq = 0;

    modalEl.addEventListener('input', function (e) {
        var input = e.target;
        if (input.id === 'mrName' && form.author_id.value) { form.author_id.value = ''; authorRoyalty = null; }
        if (input.id === 'mrAmount') {
            var v = parseFloat(input.value);
            document.getElementById('mrWords').textContent = v > 0 ? money(v) + (kind === 'bill' && due && v > due ? ' — বকেয়ার (' + money(due) + ') চেয়ে বেশি!' : '') : '';
            document.getElementById('mrWords').classList.toggle('is-warn', kind === 'bill' && due > 0 && v > due);
        }
        if (input.closest('.mr-bk-row')) {
            if (input.name && input.name.indexOf('[title]') !== -1) input.closest('.mr-bk-row').querySelector('[data-f="book_id"]').value = '';
            recalcBooks();
        }
        var type = input.getAttribute('data-lookup-type');
        if (!type) return;
        clearTimeout(timers.get(input));
        timers.set(input, setTimeout(function () { lookup(input, type); }, 220));
    });

    modalEl.addEventListener('focusin', function (e) {
        var type = e.target.getAttribute && e.target.getAttribute('data-lookup-type');
        if (type && !e.target.value) lookup(e.target, type);
    });

    function lookup(input, type) {
        var box = input.closest('.mr-lookup').querySelector('.mr-suggest');
        var my = ++seq;
        fetch(modalEl.dataset.lookup + '?type=' + type + '&q=' + encodeURIComponent(input.value.trim()) + (type === 'book' && form.author_id.value ? '&author_id=' + encodeURIComponent(form.author_id.value) : ''), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : { items: [] }; })
            .then(function (d) {
                if (my !== seq || document.activeElement !== input) return;
                var items = d.items || [];
                if (!items.length) { box.hidden = true; return; }
                box.innerHTML = items.map(function (it, i) {
                    return '<button type="button" data-i="' + i + '"><b>' + esc(it.name) + '</b><small>' + esc(type === 'author' ? (it.phone || 'লেখক') : it.sub) + '</small></button>';
                }).join('');
                box._items = items; box._type = type;
                box.hidden = false;
            }).catch(function () {});
    }

    modalEl.addEventListener('mousedown', function (e) {
        var b = e.target.closest('.mr-suggest button');
        if (!b) return;
        e.preventDefault();
        var box = b.parentElement;
        var it = box._items[+b.dataset.i];
        box.hidden = true;
        if (box._type === 'book') {
            var row = box.closest('.mr-bk-row');
            row.querySelector('[data-f="title"]').value = it.name;
            row.querySelector('[data-f="book_id"]').value = it.id;
            if (!row.querySelector('[data-f="price"]').value && it.price) row.querySelector('[data-f="price"]').value = it.price;
            recalcBooks();
            row.querySelector('[data-f="qty"]').focus();
            return;
        }
        if (box._type === 'author') {
            form.party_name.value = it.name;
            if (it.phone) form.party_phone.value = it.phone;
            form.author_id.value = it.id;
            authorRoyalty = it.royalty != null ? it.royalty : null;
            if (authorRoyalty != null) document.querySelectorAll('#mrBookRows [data-f="royalty"]').forEach(function (r) { if (!r.value) r.value = authorRoyalty; });
            recalcBooks();
            form.purpose.focus();
        } else {
            linkBill(it);
        }
    });

    modalEl.addEventListener('focusout', function (e) {
        var box = e.target.closest && e.target.closest('.mr-lookup') && e.target.closest('.mr-lookup').querySelector('.mr-suggest');
        if (box) setTimeout(function () { box.hidden = true; }, 120);
    });

    function linkBill(it) {
        form.invoice_id.value = it.id;
        due = it.due;
        form.party_name.value = it.name || '';
        form.party_phone.value = it.phone || '';
        form.party_address.value = it.address || '';
        if (!form.purpose.value) form.purpose.value = 'বিল #' + it.no;
        form.amount.value = it.due;
        form.amount.dispatchEvent(new Event('input', { bubbles: true }));
        var card = document.getElementById('mrLinkedBill');
        card.innerHTML = '<i class="fa-solid fa-file-invoice"></i><div><b>#' + esc(it.no) + '</b> · ' + esc(it.name) +
            '<small>মোট ' + money(it.total) + ' · বকেয়া <strong>' + money(it.due) + '</strong> — জমা নিলে বিলের বকেয়া কমবে</small></div>';
        card.hidden = false;
        document.getElementById('mrBillSearch').value = '';
        modalEl.querySelector('[data-clear-link]').hidden = false;
        sync();
    }

    function unlinkBill() {
        form.invoice_id.value = '';
        due = 0;
        var card = document.getElementById('mrLinkedBill');
        if (card) card.hidden = true;
        var x = modalEl.querySelector('[data-clear-link]');
        if (x) x.hidden = true;
        sync();
    }

    modalEl.querySelector('[data-clear-link]').addEventListener('click', unlinkBill);
    document.getElementById('mrFullDue').addEventListener('click', function () {
        form.amount.value = due;
        form.amount.dispatchEvent(new Event('input', { bubbles: true }));
    });

    modalEl.querySelectorAll('[data-mr-kind]').forEach(function (b) {
        b.addEventListener('click', function () { setKind(b.dataset.mrKind); });
    });

    // ---------------------------------------------------------------- book sales (honorarium)
    // sale price = cover price less commission %; writer gets royalty % of copies × sale price
    var bookSeq = 0;
    var authorRoyalty = null;
    var mem = function (k, d) { try { return localStorage.getItem(k) || d; } catch (e) { return d; } };
    var keep = function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} };
    var num = function (v) { return bn(Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 })); };

    function field(i, f, label, attrs) {
        return '<label class="mr-bk-f"><span>' + label + '</span><input type="number" data-f="' + f + '" name="items[' + i + '][' + f + ']" ' + attrs + '></label>';
    }

    function addBookRow() {
        var i = bookSeq++;
        var royalty = authorRoyalty != null ? authorRoyalty : mem('mr_last_royalty', '');
        var row = document.createElement('div');
        row.className = 'mr-bk-row';
        row.innerHTML =
            '<div class="mr-bk-top">' +
                '<div class="mr-lookup mr-bk-title"><input type="text" data-f="title" name="items[' + i + '][title]" maxlength="255" placeholder="বইয়ের নাম লিখুন বা তালিকা থেকে বাছুন" data-lookup-type="book" aria-label="বইয়ের নাম"><div class="mr-suggest" hidden></div></div>' +
                '<button type="button" class="mr-bk-del">বাদ দিন</button>' +
            '</div>' +
            '<input type="hidden" data-f="book_id" name="items[' + i + '][book_id]">' +
            '<div class="mr-bk-fields">' +
                field(i, 'qty', 'কপি', 'min="1" step="1" inputmode="numeric" placeholder="০"') +
                field(i, 'price', 'মুদ্রিত মূল্য', 'min="0" step="0.01" inputmode="decimal" placeholder="০"') +
                field(i, 'disc', 'কমিশন %', 'min="0" max="100" step="0.01" inputmode="decimal" placeholder="০" value="' + esc(mem('mr_last_disc', '25')) + '"') +
                field(i, 'royalty', 'রয়্যালটি %', 'min="0" max="100" step="0.01" inputmode="decimal" placeholder="০" value="' + esc(royalty) + '"') +
            '</div>' +
            '<div class="mr-bk-calc">' +
                '<div><span>বিক্রয় মূল্য</span><b data-f="unit">—</b></div>' +
                '<div><span>মোট বিক্রয়</span><b data-f="sale">—</b></div>' +
                '<div class="is-net"><span>লেখকের প্রাপ্য</span><b data-f="net">—</b></div>' +
            '</div>';
        document.getElementById('mrBookRows').appendChild(row);
        if (!form.purpose.value.trim()) form.purpose.value = 'বই বিক্রয়ের সম্মানী';
        recalcBooks();
        row.querySelector('[data-f="title"]').focus();
    }

    function recalcBooks() {
        var rows = Array.prototype.slice.call(document.querySelectorAll('#mrBookRows .mr-bk-row'));
        var t = { qty: 0, sale: 0, net: 0 };
        var val = function (row, f) { return parseFloat(row.querySelector('[data-f="' + f + '"]').value) || 0; };
        var pc = function (v) { return Math.min(100, Math.max(0, v)); };
        rows.forEach(function (row) {
            var qty = Math.max(0, Math.floor(val(row, 'qty')));
            var price = Math.max(0, val(row, 'price'));
            var unit = Math.round(price * (100 - pc(val(row, 'disc')))) / 100;
            var sale = Math.round(qty * unit * 100) / 100;
            var net = Math.round(sale * pc(val(row, 'royalty'))) / 100;
            row.querySelector('[data-f="unit"]').textContent = price ? money(unit) : '—';
            row.querySelector('[data-f="sale"]').textContent = qty && price ? money(sale) : '—';
            row.querySelector('[data-f="net"]').textContent = sale ? money(net) : '—';
            row.classList.toggle('is-done', net > 0);
            t.qty += qty; t.sale += sale; t.net += net;
        });
        var has = rows.length > 0;
        document.getElementById('mrBooks').hidden = !has;
        document.getElementById('mrBkQty').textContent = num(t.qty);
        document.getElementById('mrBkSale').textContent = money(t.sale);
        document.getElementById('mrBkNet').textContent = money(t.net);
        var amount = form.amount;
        amount.readOnly = has;
        amount.closest('.mr-amount').classList.toggle('is-auto', has);
        if (has) {
            amount.value = t.net ? Math.round(t.net * 100) / 100 : '';
            document.getElementById('mrWords').textContent = t.net ? money(t.net) + ' · বই বিক্রির হিসাব থেকে' : 'বইয়ের কপি, মূল্য আর রয়্যালটি দিন';
        }
    }

    document.getElementById('mrAddBook').addEventListener('click', addBookRow);
    document.getElementById('mrBookRows').addEventListener('click', function (e) {
        var x = e.target.closest('.mr-bk-del');
        if (!x) return;
        x.closest('.mr-bk-row').remove();
        recalcBooks();
    });
    document.getElementById('mrBookRows').addEventListener('change', function (e) {
        var f = e.target.getAttribute('data-f');
        if (f === 'disc') keep('mr_last_disc', e.target.value);
        if (f === 'royalty') keep('mr_last_royalty', e.target.value);
    });

    // ---------------------------------------------------------------- save
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var err = document.getElementById('mrError');
        err.hidden = true;
        form.querySelectorAll('.is-invalid').forEach(function (x) { x.classList.remove('is-invalid'); });
        var miss = ['party_name', 'purpose', 'amount'].filter(function (n) { return !String(form[n].value).trim(); });
        if (miss.length) {
            miss.forEach(function (n) { form[n].classList.add('is-invalid'); });
            form[miss[0]].focus();
            err.textContent = 'নাম, কী বাবদ আর টাকার পরিমাণ দিন।';
            err.hidden = false;
            return;
        }
        var btn = document.getElementById('mrSave');
        btn.disabled = true;
        fetch(modalEl.dataset.store, {
            method: 'POST', credentials: 'same-origin', body: new FormData(form),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, status: r.status, d: d }; });
        }).then(function (res) {
            btn.disabled = false;
            if (!res.ok) {
                var msg = res.d.message;
                if (res.d.errors) {
                    var k = Object.keys(res.d.errors)[0];
                    msg = res.d.errors[k][0];
                    if (form[k] && form[k].classList) form[k].classList.add('is-invalid');
                }
                err.textContent = msg || (res.status === 403 ? 'অনুমতি নেই' : res.status === 419 ? 'সেশন শেষ — পেজ রিলোড করুন' : 'সংরক্ষণ করা যায়নি');
                err.hidden = false;
                return;
            }
            document.getElementById('mrDoneTitle').textContent = TEXT[kind].done;
            document.getElementById('mrDoneText').innerHTML = '<b>' + esc(res.d.receipt_no) + '</b> · ' + esc(form.party_name.value) + ' · ' + money(form.amount.value);
            document.getElementById('mrPrint').href = res.d.print_url;
            document.getElementById('mrPrint2').href = res.d.print_url + '?copies=2';
            show(document.getElementById('mrBody'), false);
            show(document.getElementById('mrFoot'), false);
            show(document.getElementById('mrDone'), true);
            modalEl.querySelector('.mr-tabs').hidden = true;
            document.dispatchEvent(new CustomEvent('mr:saved', { detail: res.d }));
        }).catch(function () {
            btn.disabled = false;
            err.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন';
            err.hidden = false;
        });
    });

    document.getElementById('mrAgain').addEventListener('click', function () { var k = kind; reset(); setKind(k); });

    // ---------------------------------------------------------------- open
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-mr-open]');
        if (!t) return;
        e.preventDefault();
        reset();
        setKind(t.getAttribute('data-mr-open'));
        modal().show();
    });
    modalEl.addEventListener('shown.bs.modal', function () {
        (kind === 'bill' ? document.getElementById('mrBillSearch') : document.getElementById('mrName')).focus();
    });

    setKind('honorarium');
})();
