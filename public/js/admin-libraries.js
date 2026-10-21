/* Admin › Libraries — instant filtering, live KPIs, bulk actions (no page reloads) */
(function () {
    'use strict';

    var root = document.getElementById('libRoot');
    if (!root) return;

    var form = document.getElementById('libFilters');
    var holder = document.getElementById('libTable');
    var bulkBar = document.getElementById('libBulk');
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var base = root.dataset.url;
    var divInput = form.elements.division;
    var timer, ctrl;

    function params() {
        var p = new URLSearchParams();
        Array.prototype.forEach.call(form.elements, function (el) {
            if (el.name && el.value !== '') p.set(el.name, el.value);
        });
        return p;
    }

    function setExportLink() {
        var p = params(); p.delete('per_page');
        var a = document.getElementById('libExport');
        if (a) a.href = root.dataset.exportUrl + (p.toString() ? '?' + p : '');
    }

    function toast(msg, bad) {
        var el = document.createElement('div');
        el.textContent = msg;
        el.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2000;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 12px 30px -10px rgba(0,0,0,.5);background:' + (bad ? '#dc2626' : '#0f172a');
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 2400);
    }

    /* ---------- KPIs ---------- */
    function applyStats(s) {
        if (!s) return;
        var map = { total: s.total, approved: s.approved, allocated: s.allocated, acknowledged: s.acknowledged, pending: s.pending, readers: s.readers, existing: s.existing };
        Object.keys(map).forEach(function (k) {
            document.querySelectorAll('[data-kpi="' + k + '"]').forEach(function (el) {
                var v = Number(map[k]).toLocaleString();
                if (el.textContent.trim() !== v) { el.textContent = v; el.classList.remove('flash'); void el.offsetWidth; el.classList.add('flash'); }
            });
        });
        var r = document.querySelector('[data-kpi-sub="received"]'); if (r) r.textContent = Number(s.received).toLocaleString() + ' books delivered';
        var d = document.querySelector('[data-kpi-sub="districts"]'); if (d) d.textContent = s.districts + ' districts covered';
        var all = document.querySelector('[data-div-count="all"]'); if (all) all.textContent = Number(s.total).toLocaleString();
        Object.keys(s.divisions || {}).forEach(function (div) {
            document.querySelectorAll('[data-div-count="' + div + '"]').forEach(function (el) { el.textContent = s.divisions[div]; });
        });
    }
    window.LibDyn = { applyStats: applyStats, refresh: function () { load(false); } };

    /* ---------- table refresh ---------- */
    function load(push) {
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        var p = params(), qs = p.toString();
        holder.classList.add('is-loading');
        fetch(base + (qs ? '?' + qs : ''), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal
        }).then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
          .then(function (d) {
              holder.innerHTML = d.table;
              applyStats(d.stats);
              if (push !== false) history.replaceState(null, '', base + (qs ? '?' + qs : ''));
              setExportLink();
              clearSelection();
          })
          .catch(function (e) { if (e.name !== 'AbortError') toast('Could not refresh the list.', true); })
          .finally(function () { holder.classList.remove('is-loading'); });
    }

    function reload(delay) { clearTimeout(timer); timer = setTimeout(function () { load(); }, delay || 0); }

    form.addEventListener('submit', function (e) { e.preventDefault(); reload(); });
    form.elements.search.addEventListener('input', function () { reload(350); });
    ['status', 'ack_status', 'sort', 'per_page'].forEach(function (n) { form.elements[n].addEventListener('change', function () { reload(); }); });

    document.getElementById('libDivisions').addEventListener('click', function (e) {
        var b = e.target.closest('.div-stat-pill'); if (!b) return;
        divInput.value = b.dataset.division || '';
        this.querySelectorAll('.div-stat-pill').forEach(function (x) { x.classList.toggle('active', x === b); });
        reload();
    });

    document.getElementById('libReset').addEventListener('click', function () {
        form.reset();
        form.elements.search.value = ''; ['status', 'ack_status', 'sort'].forEach(function (n) { form.elements[n].value = ''; });
        form.elements.per_page.value = '25'; divInput.value = '';
        document.querySelectorAll('#libDivisions .div-stat-pill').forEach(function (x) { x.classList.toggle('active', x.dataset.division === ''); });
        reload();
    });

    // pagination inside the swapped table
    holder.addEventListener('click', function (e) {
        var a = e.target.closest('.pagination a'); if (!a) return;
        e.preventDefault();
        var u = new URL(a.href, location.href), page = u.searchParams.get('page');
        var p = params(); if (page) p.set('page', page);
        ctrl && ctrl.abort(); ctrl = new AbortController();
        holder.classList.add('is-loading');
        fetch(base + '?' + p, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal })
            .then(function (r) { return r.json(); })
            .then(function (d) { holder.innerHTML = d.table; applyStats(d.stats); history.replaceState(null, '', base + '?' + p); clearSelection(); holder.scrollIntoView({ behavior: 'smooth', block: 'start' }); })
            .catch(function () { })
            .finally(function () { holder.classList.remove('is-loading'); });
    });

    /* ---------- selection + bulk ---------- */
    function rowChecks() { return Array.prototype.slice.call(holder.querySelectorAll('.lib-row-check')); }
    function selectedIds() { return rowChecks().filter(function (c) { return c.checked; }).map(function (c) { return c.value; }); }
    function refreshBulk() {
        var ids = selectedIds(), all = rowChecks();
        all.forEach(function (c) { c.closest('tr').classList.toggle('is-selected', c.checked); });
        document.getElementById('libBulkCount').textContent = ids.length;
        bulkBar.hidden = ids.length === 0;
        var master = document.getElementById('libCheckAll');
        if (master) { master.checked = ids.length > 0 && ids.length === all.length; master.indeterminate = ids.length > 0 && ids.length < all.length; }
    }
    function clearSelection() { rowChecks().forEach(function (c) { c.checked = false; }); refreshBulk(); }

    holder.addEventListener('change', function (e) {
        if (e.target.id === 'libCheckAll') { rowChecks().forEach(function (c) { c.checked = e.target.checked; }); refreshBulk(); }
        else if (e.target.classList.contains('lib-row-check')) refreshBulk();
    });
    document.getElementById('libBulkClear').addEventListener('click', clearSelection);

    bulkBar.addEventListener('click', function (e) {
        var b = e.target.closest('[data-bulk]'); if (!b) return;
        var ids = selectedIds(); if (!ids.length) return;
        var act = b.dataset.bulk;
        if (act === 'reject' && !confirm('Reject ' + ids.length + ' selected librar' + (ids.length === 1 ? 'y' : 'ies') + '?')) return;
        b.disabled = true;
        fetch(root.dataset.bulkUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: act, ids: ids })
        }).then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
          .then(function (d) { toast(d.message); applyStats(d.stats); load(false); })
          .catch(function () { toast('Bulk action failed.', true); })
          .finally(function () { b.disabled = false; });
    });

    setExportLink();
})();
