/**
 * Backup hub (/admin/backup). Buttons carry data-backup / data-act, so there is no inline JavaScript in the page.
 * Everything that comes from the server is put into the page as text, never as HTML.
 */
(function () {
    'use strict';

    const B = window.BACKUP || { routes: {}, tables: [], timeline: {} };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const esc = (v) => { const d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; };
    const url = (path, file) => B.routes.base + '/' + path + (file ? '/' + encodeURIComponent(file) : '');
    const modal = (id) => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
    const bn = (n) => String(n).replace(/\d/g, (d) => '০১২৩৪৫৬৭৮৯'[d]);

    async function api(method, target, body) {
        const res = await fetch(target, {
            method,
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', ...(body ? { 'Content-Type': 'application/json' } : {}) },
            body: body ? JSON.stringify(body) : undefined,
        });
        let data = {};
        try { data = await res.json(); } catch (e) { data = { success: false, message: 'সার্ভার থেকে সঠিক উত্তর আসেনি (' + res.status + ')' }; }
        if (!res.ok && data.success === undefined) data.success = false;
        data._status = res.status;
        return data;
    }

    function toast(type, message) {
        const box = $('#toastBox');
        if (!box) return;
        const el = document.createElement('div');
        el.className = 'alert alert-' + type + ' alert-dismissible fade show mb-0';
        el.setAttribute('role', 'alert');
        el.textContent = message;
        const close = document.createElement('button');
        close.type = 'button'; close.className = 'btn-close'; close.setAttribute('data-bs-dismiss', 'alert');
        el.appendChild(close);
        box.replaceChildren(el);
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function ask(title, text, okLabel) {
        if (window.SwalConfirm) {
            return window.SwalConfirm({ title, text, icon: 'warning', confirmButtonText: okLabel, cancelButtonText: 'বাতিল' }).then((r) => r.isConfirmed);
        }
        return Promise.resolve(window.confirm(text));
    }

    // ------------------------------------------------------------ create
    async function createBackup(mode) {
        $('#progressTitle').textContent = mode === 'db_only' ? 'ডাটাবেজ ব্যাকআপ হচ্ছে' : 'ডাটাবেজ + মিডিয়া ব্যাকআপ হচ্ছে';
        modal('progressModal').show();
        const res = await api('POST', B.routes.create, { mode });
        if (res.success) {
            toast('success', res.message);
            setTimeout(() => location.reload(), 700);
        } else {
            modal('progressModal').hide();
            toast('danger', res.message || 'ব্যাকআপ ব্যর্থ হয়েছে');
        }
    }

    // ------------------------------------------------------------ upload
    function initUpload() {
        const zone = $('#dropZone'), input = $('#backupFileInput');
        if (!zone || !input) return;
        const open = () => input.click();
        zone.addEventListener('click', open);
        $('#btnUpload')?.addEventListener('click', open);
        input.addEventListener('change', () => input.files.length && upload(input.files[0]));
        ['dragenter', 'dragover'].forEach((n) => zone.addEventListener(n, (e) => { e.preventDefault(); zone.classList.add('dragover'); }));
        ['dragleave', 'drop'].forEach((n) => zone.addEventListener(n, (e) => { e.preventDefault(); zone.classList.remove('dragover'); }));
        zone.addEventListener('drop', (e) => e.dataTransfer?.files?.length && upload(e.dataTransfer.files[0]));
    }

    function upload(file) {
        if (file.size > B.uploadMaxMb * 1048576) return toast('danger', 'ফাইল ' + B.uploadMaxMb + ' MB-এর বেশি।');
        const idle = $('#dropIdle'), busy = $('#dropBusy'), bar = $('#uploadBar'), pct = $('#uploadPct');
        $('#uploadName').textContent = file.name;
        idle.classList.add('d-none'); busy.classList.remove('d-none');

        const form = new FormData();
        form.append('backup_file', file);
        form.append('_token', csrf);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', B.routes.upload);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = (e) => { if (e.lengthComputable) { const p = Math.round(e.loaded / e.total * 100); bar.style.width = p + '%'; pct.textContent = p + '%'; } };
        const reset = () => { idle.classList.remove('d-none'); busy.classList.add('d-none'); bar.style.width = '0'; $('#backupFileInput').value = ''; };
        xhr.onload = () => {
            let res = {};
            try { res = JSON.parse(xhr.responseText); } catch (e) { /* not json */ }
            if (xhr.status === 200 && res.success) { toast('success', res.message); setTimeout(() => location.reload(), 600); }
            else { toast('danger', res.message || (res.errors && Object.values(res.errors)[0][0]) || 'আপলোড ব্যর্থ হয়েছে'); reset(); }
        };
        xhr.onerror = () => { toast('danger', 'নেটওয়ার্ক সমস্যা'); reset(); };
        xhr.send(form);
    }

    // ------------------------------------------------------------ list: filter, select
    let category = 'all';

    function applyFilter() {
        const q = ($('#backupSearch')?.value || '').toLowerCase().trim();
        let shown = 0;
        $$('#backupRows tr[data-name]').forEach((tr) => {
            const ok = (category === 'all' || tr.dataset.cat === category) && (!q || tr.dataset.name.includes(q));
            tr.classList.toggle('d-none', !ok);
            if (ok) shown++;
        });
        $('#shownCount').textContent = bn(shown) + 'টি';
        $('#noMatchRow')?.classList.toggle('d-none', shown > 0 || !$('#backupRows tr[data-name]'));
        updateBulk();
    }

    function updateBulk() {
        const n = $$('.row-check:checked').length;
        const bar = $('#bulkBar');
        bar.style.display = n ? 'flex' : 'none';
        $('#bulkCount').textContent = bn(n) + ' নির্বাচিত';
    }

    function initList() {
        $$('#bkTabs [data-cat]').forEach((b) => b.addEventListener('click', () => {
            $$('#bkTabs .nav-link').forEach((x) => x.classList.remove('active'));
            b.classList.add('active');
            category = b.dataset.cat;
            applyFilter();
        }));
        $('#backupSearch')?.addEventListener('input', applyFilter);
        $('#selectAll')?.addEventListener('change', (e) => {
            $$('#backupRows tr[data-name]:not(.d-none) .row-check').forEach((c) => { c.checked = e.target.checked; });
            updateBulk();
        });
        document.addEventListener('change', (e) => { if (e.target.classList?.contains('row-check')) updateBulk(); });

        $('#schemaSearch')?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            $$('#schemaTable tbody tr').forEach((tr) => tr.classList.toggle('d-none', q && !tr.dataset.name.includes(q)));
        });
    }

    // ------------------------------------------------------------ row actions
    const openInfo = (title, file, html, footer) => {
        $('#infoTitle').textContent = title;
        $('#infoFile').textContent = file || '';
        $('#infoBody').innerHTML = html;
        const f = $('#infoFooter');
        f.innerHTML = footer || '';
        f.classList.toggle('d-none', !footer);
        modal('infoModal').show();
    };
    const loading = '<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm me-2"></div>লোড হচ্ছে…</div>';
    const stat = (label, value, tone) => '<div class="col-6 col-md"><div class="p-3 border rounded-3 text-center"><small class="text-muted d-block">' + esc(label) + '</small><strong class="fs-5 ' + (tone || '') + '">' + esc(value) + '</strong></div></div>';

    async function preview(file) {
        openInfo('প্রিভিউ', file, loading);
        const d = await api('GET', url('inspect', file));
        if (!d.success) return void ($('#infoBody').innerHTML = '<div class="alert alert-danger mb-0">' + esc(d.message) + '</div>');
        let html = '<div class="row g-2 mb-3">' + stat('ফাইল', d.files_count) + stat('সাইজ', d.size) + (d.manifest ? stat('ডাটাবেজ', (d.manifest.driver || '').toUpperCase()) + stat('মিডিয়া', d.manifest.media_files ?? 0) : '') + '</div>';
        html += '<div class="table-responsive border rounded-3" style="max-height:320px"><table class="table table-sm mb-0 font-monospace small"><tbody>';
        (d.files || []).forEach((f) => { html += '<tr><td class="text-truncate" style="max-width:420px">' + esc(f.name) + '</td><td class="text-end text-muted">' + esc(f.size) + '</td></tr>'; });
        $('#infoBody').innerHTML = html + '</tbody></table></div>' + (d.files_count > 100 ? '<small class="text-muted">প্রথম ১০০টি দেখানো হয়েছে</small>' : '');
    }

    async function verify(file) {
        openInfo('যাচাই', file, loading);
        const d = await api('POST', url('dry-run', file));
        const checks = d.checks || [];
        if (!checks.length) return void ($('#infoBody').innerHTML = '<div class="alert alert-danger mb-0">' + esc(d.message || 'যাচাই করা যায়নি') + '</div>');
        let html = '<div class="alert alert-' + (d.passed ? 'success' : 'danger') + ' py-2">' + (d.passed ? 'ঠিক আছে — রিস্টোর করা যাবে। লাইভ ডাটাবেজে কিছু বদলানো হয়নি।' : 'সমস্যা পাওয়া গেছে — এই ফাইল রিস্টোর করা যাবে না।') + '</div>';
        checks.forEach((c) => {
            html += '<div class="bkp-check"><i class="fa-solid ' + (c.ok ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger') + ' mt-1"></i><div><div>' + esc(c.label) + '</div>' + (c.detail ? '<small class="text-muted">' + esc(c.detail) + '</small>' : '') + '</div></div>';
        });
        if (d.stats) html += '<small class="text-muted d-block mt-3">' + bn(d.stats.statements) + ' স্টেটমেন্ট · ' + bn(d.stats.tables) + ' টেবিল · ' + bn(d.stats.rows) + ' রো · ' + bn(d.ms) + ' ms</small>';
        $('#infoBody').innerHTML = html;
    }

    const diffLabels = {
        equal: ['মিল আছে', 'success'], live_higher: ['লাইভে বেশি', 'primary'], backup_higher: ['ব্যাকআপে বেশি', 'warning'],
        only_in_live: ['শুধু লাইভে', 'info'], only_in_backup: ['শুধু ব্যাকআপে', 'danger'],
    };

    async function diff(file) {
        openInfo('তুলনা', file, loading, '<button type="button" class="btn btn-outline-danger" data-act="selective" data-file="' + esc(file) + '" data-bs-dismiss="modal">টেবিল বাছাই করে রিস্টোর</button>');
        const d = await api('GET', url('diff', file));
        if (!d.success) { $('#infoFooter').classList.add('d-none'); return void ($('#infoBody').innerHTML = '<div class="alert alert-danger mb-0">' + esc(d.message) + '</div>'); }
        let html = '<div class="row g-2 mb-3">' + stat('টেবিল', d.total_tables) + stat('পার্থক্য', d.changed_tables_count, d.changed_tables_count ? 'text-warning' : 'text-success') + stat('লাইভ রো', Number(d.total_live_rows).toLocaleString()) + stat('ব্যাকআপ রো', Number(d.total_backup_rows).toLocaleString()) + '</div>';
        html += '<input type="search" class="form-control form-control-sm mb-2" id="diffSearch" placeholder="টেবিল খুঁজুন">';
        html += '<div class="table-responsive border rounded-3" style="max-height:360px"><table class="table table-sm align-middle mb-0 small"><thead><tr><th>টেবিল</th><th class="text-end">লাইভ</th><th class="text-end">ব্যাকআপ</th><th class="text-end">অবস্থা</th></tr></thead><tbody id="diffBody">';
        (d.tables || []).forEach((r) => {
            const [label, tone] = diffLabels[r.status] || [r.status, 'secondary'];
            html += '<tr data-name="' + esc(r.table.toLowerCase()) + '"><td class="font-monospace">' + esc(r.table) + '</td><td class="text-end">' + (r.live_rows ?? '—') + '</td><td class="text-end">' + (r.backup_rows ?? '—') + '</td><td class="text-end"><span class="badge rounded-pill text-bg-' + tone + '">' + esc(label) + '</span></td></tr>';
        });
        $('#infoBody').innerHTML = html + '</tbody></table></div>';
        $('#diffSearch').addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            $$('#diffBody tr').forEach((tr) => tr.classList.toggle('d-none', q && !tr.dataset.name.includes(q)));
        });
    }

    function restore(file) {
        $('#restoreFile').textContent = file;
        $('#restoreForm').action = url('restore', file);
        modal('restoreModal').show();
    }

    // ---- selective restore
    let selectiveFile = '';
    const picked = () => $$('.pick-check:checked').map((c) => c.value);
    const updatePicked = () => { $('#selCount').textContent = bn(picked().length) + ' নির্বাচিত'; };

    function selective(file) {
        selectiveFile = file;
        $('#selectiveFile').textContent = file;
        $$('.pick-check').forEach((c) => { c.checked = false; });
        $('#selSearch').value = '';
        $$('.bkp-pick').forEach((p) => p.classList.remove('d-none'));
        updatePicked();
        modal('selectiveModal').show();
    }

    function initSelective() {
        $('#selAll')?.addEventListener('click', () => { $$('.bkp-pick:not(.d-none) .pick-check').forEach((c) => { c.checked = true; }); updatePicked(); });
        $('#selNone')?.addEventListener('click', () => { $$('.pick-check').forEach((c) => { c.checked = false; }); updatePicked(); });
        $('#pickList')?.addEventListener('change', updatePicked);
        $('#selSearch')?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            $$('.bkp-pick').forEach((p) => p.classList.toggle('d-none', q && !p.dataset.name.includes(q)));
        });
        $('#selectiveRun')?.addEventListener('click', async () => {
            const tables = picked();
            if (!tables.length) return toast('warning', 'অন্তত একটি টেবিল বাছাই করুন।');
            if (!(await ask('টেবিল রিস্টোর', bn(tables.length) + 'টি টেবিল ব্যাকআপ থেকে বদলে যাবে। এগোবেন?', 'রিস্টোর করুন'))) return;
            const btn = $('#selectiveRun'); btn.disabled = true;
            const res = await api('POST', url('selective-restore', selectiveFile), { tables });
            btn.disabled = false;
            modal('selectiveModal').hide();
            toast(res.success ? 'success' : 'danger', res.message || (res.errors && Object.values(res.errors)[0][0]) || 'রিস্টোর ব্যর্থ');
            if (res.success) setTimeout(() => location.reload(), 900);
        });
    }

    // ---- email
    let emailFile = '';
    function email(file) {
        emailFile = file;
        $('#emailFile').textContent = file;
        if (!$('#emailTo').value) $('#emailTo').value = B.email || '';
        modal('emailModal').show();
    }

    async function sendEmail() {
        const to = $('#emailTo').value.trim();
        if (!to) return toast('warning', 'ইমেইল ঠিকানা দিন।');
        const btn = $('#emailSend'); btn.disabled = true;
        const res = await api('POST', url('email', emailFile), { email: to });
        btn.disabled = false;
        modal('emailModal').hide();
        toast(res.success ? 'success' : 'danger', res.message || (res.errors && Object.values(res.errors)[0][0]) || 'পাঠানো যায়নি');
    }

    // ---- delete
    async function remove(file) {
        if (!(await ask('ব্যাকআপ মুছুন', file + ' স্থায়ীভাবে মুছে যাবে।', 'মুছুন'))) return;
        const res = await api('DELETE', B.routes.base + '/' + encodeURIComponent(file));
        if (!res.success) return toast('danger', res.message || 'মোছা যায়নি');
        const tr = $$('#backupRows tr[data-file]').find((r) => r.dataset.file === file);
        tr?.remove();
        toast('success', res.message);
        applyFilter();
    }

    async function bulkRemove() {
        const files = $$('.row-check:checked').map((c) => c.value);
        if (!files.length) return;
        if (!(await ask('একসাথে মুছুন', bn(files.length) + 'টি ব্যাকআপ স্থায়ীভাবে মুছে যাবে।', 'মুছুন'))) return;
        const res = await api('POST', B.routes.bulkDelete, { filenames: files });
        if (!res.success) return toast('danger', res.message || 'মোছা যায়নি');
        $$('#backupRows tr[data-file]').filter((r) => files.includes(r.dataset.file)).forEach((r) => r.remove());
        $('#selectAll').checked = false;
        toast('success', res.message);
        applyFilter();
    }

    // ---- masked dump, telegram test
    async function masked() {
        if (!(await ask('মাস্কড ডাম্প', 'পাসওয়ার্ড, ইমেইল ও ফোন নম্বর বদলে দেওয়া একটি ডাম্প তৈরি হবে (ডেভেলপারদের জন্য)।', 'তৈরি করুন'))) return;
        toast('info', 'ডাম্প তৈরি হচ্ছে…');
        const res = await api('POST', B.routes.masked);
        if (!res.success) return toast('danger', res.message || 'ডাম্প ব্যর্থ');
        toast('success', res.message);
        if (res.download_url) location.href = res.download_url;
        setTimeout(() => location.reload(), 1500);
    }

    async function telegramTest() {
        const token = $('#tgToken').value.trim(), chat = $('#tgChat').value.trim();
        const res = await api('POST', B.routes.notify, { type: 'telegram', telegram_bot_token: token || undefined, telegram_chat_id: chat || undefined });
        toast(res.success ? 'success' : 'danger', res.message || (res.errors && Object.values(res.errors)[0][0]) || 'পাঠানো যায়নি');
    }

    // ------------------------------------------------------------ chart
    function initChart() {
        const el = $('#usageChart');
        if (!el || typeof Chart === 'undefined') return;
        new Chart(el, {
            type: 'bar',
            data: { labels: B.timeline.labels, datasets: [{ data: B.timeline.backup_sizes_mb, backgroundColor: '#2563eb', borderRadius: 4, maxBarThickness: 36 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => c.parsed.y + ' MB · ' + bn(B.timeline.archive_counts[c.dataIndex]) + 'টি' } } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { callback: (v) => v + ' MB' } } },
            },
        });
    }

    // ------------------------------------------------------------ wiring
    const actions = { preview, verify, diff, restore, selective, email, delete: remove, masked, 'bulk-delete': bulkRemove };

    document.addEventListener('click', (e) => {
        const create = e.target.closest('[data-backup]');
        if (create) return createBackup(create.dataset.backup);
        const act = e.target.closest('[data-act]');
        if (act && actions[act.dataset.act]) actions[act.dataset.act](act.dataset.file);
    });

    document.addEventListener('DOMContentLoaded', () => {
        initUpload(); initList(); initSelective(); initChart();
        $('#emailSend')?.addEventListener('click', sendEmail);
        $('#tgTest')?.addEventListener('click', telegramTest);
        applyFilter();
    });
})();
