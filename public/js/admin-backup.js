/**
 * Backup hub (/admin/backup). Buttons carry data-backup / data-act, so there is no inline JavaScript in the page.
 * Everything that comes from the server is put into the page as text (esc() or textContent), never as raw HTML.
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
    const firstError = (res, fallback) => res.message || (res.errors && Object.values(res.errors)[0][0]) || fallback;

    async function api(method, target, body) {
        let res;
        try {
            res = await fetch(target, {
                method,
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', ...(body ? { 'Content-Type': 'application/json' } : {}) },
                body: body ? JSON.stringify(body) : undefined,
                credentials: 'same-origin',
            });
        } catch (e) {
            return { success: false, message: 'নেটওয়ার্ক সমস্যা — ইন্টারনেট সংযোগ দেখুন।', _status: 0 };
        }
        let data = {};
        try { data = await res.json(); } catch (e) { data = { success: false, message: 'সার্ভার থেকে সঠিক উত্তর আসেনি (' + res.status + ')' }; }
        if (res.status === 419) data = { success: false, message: 'সেশন শেষ — পেজটি রিফ্রেশ করুন।' };
        if (res.status === 429) data = { success: false, message: 'খুব দ্রুত চেষ্টা হচ্ছে — এক মিনিট পরে আবার করুন।' };
        if (!res.ok && data.success === undefined) data.success = false;
        data._status = res.status;
        return data;
    }

    /** Shows a spinner on the button while fn runs, and always puts the button back. */
    async function busy(btn, fn) {
        if (!btn) return fn();
        const old = btn.innerHTML;
        btn.disabled = true; btn.classList.add('is-busy');
        const ic = btn.querySelector('i');
        if (ic) ic.outerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
        try { return await fn(); } finally { if (btn.isConnected) { btn.innerHTML = old; btn.disabled = false; btn.classList.remove('is-busy'); } }
    }

    const ICONS = { success: 'fa-circle-check', danger: 'fa-circle-exclamation', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    function toast(type, message) {
        const box = $('#toastBox');
        if (!box) return;
        const el = document.createElement('div');
        el.className = 'bk-toast is-' + type;
        el.setAttribute('role', type === 'danger' ? 'alert' : 'status');
        el.innerHTML = '<i class="fa-solid ' + (ICONS[type] || ICONS.info) + '"></i><span></span><button type="button" class="bk-toast-x" aria-label="বন্ধ"><i class="fa-solid fa-xmark"></i></button>';
        el.querySelector('span').textContent = message;
        const kill = () => { el.classList.add('is-out'); setTimeout(() => el.remove(), 200); };
        el.querySelector('.bk-toast-x').addEventListener('click', kill);
        box.appendChild(el);
        while (box.children.length > 3) box.firstElementChild.remove();
        setTimeout(kill, type === 'danger' ? 9000 : 5000);
    }

    function ask(title, text, okLabel, icon) {
        if (window.SwalConfirm) {
            return window.SwalConfirm({ title, text, icon: icon || 'warning', confirmButtonText: okLabel, cancelButtonText: 'বাতিল' }).then((r) => r.isConfirmed);
        }
        return Promise.resolve(window.confirm(text));
    }

    // ------------------------------------------------------------ progress (with a running clock)
    let clock = null, shown = Promise.resolve();
    function progress(title, note, icon) {
        $('#progressTitle').textContent = title;
        $('#progressNote').textContent = note || 'পেজটি বন্ধ করবেন না';
        $('#progressIcon').className = 'fa-solid ' + (icon || 'fa-database');
        const t0 = Date.now(), out = $('#progressTime');
        out.textContent = '০:০০';
        clearInterval(clock);
        clock = setInterval(() => { const s = Math.floor((Date.now() - t0) / 1000); out.textContent = bn(Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0')); }, 1000);
        const el = document.getElementById('progressModal');
        shown = new Promise((ok) => el.addEventListener('shown.bs.modal', ok, { once: true }));
        modal('progressModal').show();
    }
    /** Hides the progress dialog once it has finished opening (Bootstrap ignores a hide during the opening animation). */
    async function progressDone() {
        clearInterval(clock);
        await shown;
        const el = document.getElementById('progressModal');
        await new Promise((ok) => { el.addEventListener('hidden.bs.modal', ok, { once: true }); modal('progressModal').hide(); });
    }

    // ------------------------------------------------------------ create
    async function createBackup(mode) {
        progress(mode === 'db_only' ? 'Database backup চলছে' : 'Full backup চলছে', mode === 'db_only' ? 'সাধারণত কয়েক সেকেন্ড লাগে' : 'মিডিয়া থাকায় কয়েক মিনিট লাগতে পারে', mode === 'db_only' ? 'fa-database' : 'fa-box-archive');
        const res = await api('POST', B.routes.create, { mode });
        if (res.success) {
            $('#progressTitle').textContent = 'সম্পন্ন ✓';
            toast('success', res.message);
            setTimeout(() => location.reload(), 700);
        } else {
            await progressDone();
            toast('danger', firstError(res, 'ব্যাকআপ ব্যর্থ হয়েছে'));
        }
    }

    // ------------------------------------------------------------ upload
    let xhr = null;
    function initUpload() {
        const zone = $('#dropZone'), input = $('#backupFileInput');
        if (!zone || !input) return;
        const open = () => { if (!xhr) input.click(); };
        zone.addEventListener('click', (e) => { if (!e.target.closest('#uploadCancel')) open(); });
        zone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
        $('#btnUpload')?.addEventListener('click', () => { zone.scrollIntoView({ behavior: 'smooth', block: 'center' }); open(); });
        input.addEventListener('change', () => input.files.length && upload(input.files[0]));
        ['dragenter', 'dragover'].forEach((n) => zone.addEventListener(n, (e) => { e.preventDefault(); zone.classList.add('dragover'); }));
        ['dragleave', 'drop'].forEach((n) => zone.addEventListener(n, (e) => { e.preventDefault(); zone.classList.remove('dragover'); }));
        zone.addEventListener('drop', (e) => e.dataTransfer?.files?.length && upload(e.dataTransfer.files[0]));
        $('#uploadCancel')?.addEventListener('click', (e) => { e.stopPropagation(); xhr?.abort(); });
    }

    function upload(file) {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        if (!['zip', 'sql', 'gz', 'sqlite'].includes(ext)) return toast('danger', 'শুধু .zip, .sql, .gz বা .sqlite ফাইল দেওয়া যাবে।');
        if (file.size > B.uploadMaxMb * 1048576) return toast('danger', 'ফাইল ' + bn(B.uploadMaxMb) + ' MB-এর বেশি।');
        const idle = $('#dropIdle'), busyBox = $('#dropBusy'), bar = $('#uploadBar'), pct = $('#uploadPct');
        $('#uploadName').textContent = file.name;
        idle.classList.add('d-none'); busyBox.classList.remove('d-none');

        const form = new FormData();
        form.append('backup_file', file);
        form.append('_token', csrf);

        xhr = new XMLHttpRequest();
        xhr.open('POST', B.routes.upload);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = (e) => {
            if (!e.lengthComputable) return;
            const p = Math.round(e.loaded / e.total * 100);
            bar.style.width = p + '%';
            pct.textContent = p < 100 ? bn(p) + '%' : 'যাচাই হচ্ছে…';
        };
        const reset = () => { xhr = null; idle.classList.remove('d-none'); busyBox.classList.add('d-none'); bar.style.width = '0'; $('#backupFileInput').value = ''; };
        xhr.onload = () => {
            let res = {};
            try { res = JSON.parse(xhr.responseText); } catch (e) { /* not json */ }
            if (xhr.status === 200 && res.success) { toast('success', res.message); setTimeout(() => location.reload(), 600); }
            else { toast('danger', xhr.status === 413 ? 'ফাইলটি সার্ভারের সীমার চেয়ে বড়।' : firstError(res, 'আপলোড ব্যর্থ হয়েছে')); reset(); }
        };
        xhr.onerror = () => { toast('danger', 'নেটওয়ার্ক সমস্যা'); reset(); };
        xhr.onabort = () => { toast('info', 'আপলোড বাতিল হয়েছে'); reset(); };
        xhr.send(form);
    }

    // ------------------------------------------------------------ list: filter, sort, select
    let category = 'all';
    const rows = () => $$('#backupRows tr[data-file]');

    function applyFilter() {
        const q = ($('#backupSearch')?.value || '').toLowerCase().trim();
        let shown = 0;
        rows().forEach((tr) => {
            const ok = (category === 'all' || tr.dataset.cat === category) && (!q || tr.dataset.name.includes(q));
            tr.classList.toggle('d-none', !ok);
            if (!ok) { const c = $('.row-check', tr); if (c) c.checked = false; }
            if (ok) shown++;
        });
        $('#shownCount').textContent = bn(shown) + 'টি';
        $('#noMatchRow')?.classList.toggle('d-none', shown > 0 || !rows().length);
        updateBulk();
    }

    function updateBulk() {
        const n = $$('.row-check:checked').length;
        $('#bulkBar').classList.toggle('is-on', n > 0);
        $('#bulkCount').textContent = bn(n) + 'টি নির্বাচিত';
        const visible = rows().filter((r) => !r.classList.contains('d-none'));
        const all = $('#selectAll');
        if (all) {
            const on = visible.filter((r) => $('.row-check', r)?.checked).length;
            all.checked = on > 0 && on === visible.length;
            all.indeterminate = on > 0 && on < visible.length;
        }
    }

    function sortBy(key, btn) {
        const desc = !btn.classList.contains('is-desc');
        $$('.bk-sort').forEach((b) => { b.classList.remove('is-desc', 'is-asc'); $('i', b).className = 'fa-solid fa-sort'; });
        btn.classList.add(desc ? 'is-desc' : 'is-asc');
        $('i', btn).className = 'fa-solid ' + (desc ? 'fa-sort-down' : 'fa-sort-up');
        const body = $('#backupRows'), tail = $('#noMatchRow');
        rows().sort((a, b) => (Number(a.dataset[key]) - Number(b.dataset[key])) * (desc ? -1 : 1)).forEach((r) => body.insertBefore(r, tail));
    }

    function initList() {
        $$('#bkTabs [data-cat]').forEach((b) => b.addEventListener('click', () => {
            $$('#bkTabs .nav-link').forEach((x) => { x.classList.remove('active'); x.setAttribute('aria-selected', 'false'); });
            b.classList.add('active'); b.setAttribute('aria-selected', 'true');
            category = b.dataset.cat;
            applyFilter();
        }));
        $('#backupSearch')?.addEventListener('input', applyFilter);
        $('#backupSearch')?.addEventListener('keydown', (e) => { if (e.key === 'Escape') { e.target.value = ''; applyFilter(); e.target.blur(); } });
        $('#selectAll')?.addEventListener('change', (e) => {
            rows().filter((r) => !r.classList.contains('d-none')).forEach((r) => { const c = $('.row-check', r); if (c) c.checked = e.target.checked; });
            updateBulk();
        });
        document.addEventListener('change', (e) => { if (e.target.classList?.contains('row-check')) updateBulk(); });
        $$('.bk-sort').forEach((b) => b.addEventListener('click', () => sortBy(b.dataset.sort, b)));

        $('#schemaSearch')?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            $$('#schemaTable tbody tr').forEach((tr) => tr.classList.toggle('d-none', q && !tr.dataset.name?.includes(q)));
        });
        document.addEventListener('keydown', (e) => {
            if (e.key !== '/' || e.target.closest('input, textarea, select, [contenteditable="true"]') || document.querySelector('.modal.show')) return;
            e.preventDefault(); $('#backupSearch')?.focus();
        });
    }

    // ------------------------------------------------------------ info modal
    const openInfo = (title, file, html, footer) => {
        $('#infoTitle').textContent = title;
        $('#infoFile').textContent = file || '';
        $('#infoBody').innerHTML = html;
        const f = $('#infoFooter');
        f.innerHTML = footer || '';
        f.classList.toggle('d-none', !footer);
        modal('infoModal').show();
    };
    const loading = (text) => '<div class="bk-loading"><span class="spinner-border spinner-border-sm"></span>' + esc(text || 'লোড হচ্ছে…') + '</div>';
    const stat = (label, value, tone) => '<div class="col-6 col-md"><div class="bk-stat"><small>' + esc(label) + '</small><strong class="' + (tone || '') + '">' + esc(value) + '</strong></div></div>';
    const banner = (ok, text) => '<div class="bk-banner ' + (ok ? 'is-good' : 'is-poor') + '"><i class="fa-solid ' + (ok ? 'fa-circle-check' : 'fa-circle-xmark') + '"></i><span>' + esc(text) + '</span></div>';
    const errorBox = (msg) => banner(false, msg || 'কাজটি হয়নি');
    const checkList = (checks) => checks.map((c) => '<div class="bkp-check"><i class="fa-solid ' + (c.ok ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger') + ' mt-1"></i><div><div>' + esc(c.label) + '</div>' + (c.detail ? '<small class="text-muted">' + esc(c.detail) + '</small>' : '') + '</div></div>').join('');

    function markVerified(file, ok) {
        const tr = rows().find((r) => r.dataset.file === file);
        const v = tr && $('[data-ver]', tr);
        if (!v) return;
        v.hidden = false;
        v.className = 'bk-ver ' + (ok ? 'is-ok' : 'is-bad');
        $('i', v).className = 'fa-solid ' + (ok ? 'fa-circle-check' : 'fa-circle-xmark');
        $('span', v).textContent = ok ? 'Verified' : 'Failed';
        v.title = 'যাচাই: এইমাত্র';
    }

    async function preview(file) {
        openInfo('Preview', file, loading());
        const d = await api('GET', url('inspect', file));
        if (!d.success) return void ($('#infoBody').innerHTML = errorBox(d.message));
        let html = '<div class="row g-2 mb-3">' + stat('ফাইল', bn(d.files_count)) + stat('সাইজ', d.size) + (d.manifest ? stat('ডাটাবেজ', (d.manifest.driver || '').toUpperCase()) + stat('মিডিয়া', bn(d.manifest.media_files ?? 0)) : '') + '</div>';
        html += '<div class="table-responsive border rounded-3" style="max-height:320px"><table class="table table-sm mb-0 font-monospace small"><tbody>';
        (d.files || []).forEach((f) => { html += '<tr><td class="text-truncate" style="max-width:420px">' + esc(f.name) + '</td><td class="text-end text-muted">' + esc(f.size) + '</td></tr>'; });
        $('#infoBody').innerHTML = html + '</tbody></table></div>' + (d.files_count > 100 ? '<small class="text-muted">প্রথম ১০০টি দেখানো হয়েছে</small>' : '');
    }

    async function verify(file, btn) {
        openInfo('Verify', file, loading('যাচাই হচ্ছে — লাইভ ডাটাবেজে কিছু বদলানো হচ্ছে না…'));
        const d = await busy(btn, () => api('POST', url('dry-run', file)));
        const checks = d.checks || [];
        if (!checks.length) return void ($('#infoBody').innerHTML = errorBox(d.message || 'যাচাই করা যায়নি'));
        markVerified(file, !!d.passed);
        let html = banner(d.passed, d.passed ? 'ঠিক আছে — রিস্টোর করা যাবে। লাইভ ডাটাবেজে কিছু বদলানো হয়নি।' : 'সমস্যা পাওয়া গেছে — এই ফাইল রিস্টোর করা যাবে না।');
        html += checkList(checks);
        if (d.stats) html += '<small class="text-muted d-block mt-3">' + bn(d.stats.statements) + ' স্টেটমেন্ট · ' + bn(d.stats.tables) + ' টেবিল · ' + bn(d.stats.rows) + ' রো · ' + bn(d.ms) + ' ms</small>';
        $('#infoBody').innerHTML = html;
    }

    const diffLabels = {
        equal: ['মিল আছে', 'success'], live_higher: ['লাইভে বেশি', 'primary'], backup_higher: ['ব্যাকআপে বেশি', 'warning'],
        only_in_live: ['শুধু লাইভে', 'info'], only_in_backup: ['শুধু ব্যাকআপে', 'danger'],
    };

    async function diff(file) {
        openInfo('Compare', file, loading(), '<button type="button" class="btn btn-outline-danger" data-act="selective" data-file="' + esc(file) + '" data-bs-dismiss="modal"><i class="fa-solid fa-table-list me-1"></i>Tables — বাছাই করে রিস্টোর</button>');
        const d = await api('GET', url('diff', file));
        if (!d.success) { $('#infoFooter').classList.add('d-none'); return void ($('#infoBody').innerHTML = errorBox(d.message)); }
        let html = '<div class="row g-2 mb-3">' + stat('টেবিল', bn(d.total_tables)) + stat('পার্থক্য', bn(d.changed_tables_count), d.changed_tables_count ? 'text-warning' : 'text-success') + stat('লাইভ রো', Number(d.total_live_rows).toLocaleString('bn-BD')) + stat('ব্যাকআপ রো', Number(d.total_backup_rows).toLocaleString('bn-BD')) + '</div>';
        html += '<div class="d-flex gap-2 mb-2"><input type="search" class="form-control form-control-sm" id="diffSearch" placeholder="টেবিল খুঁজুন"><div class="form-check form-switch text-nowrap small mb-0 pt-1"><input class="form-check-input" type="checkbox" id="diffOnly"><label class="form-check-label" for="diffOnly">শুধু পার্থক্য</label></div></div>';
        html += '<div class="table-responsive border rounded-3" style="max-height:360px"><table class="table table-sm align-middle mb-0 small"><thead><tr><th>টেবিল</th><th class="text-end">লাইভ</th><th class="text-end">ব্যাকআপ</th><th class="text-end">অবস্থা</th></tr></thead><tbody id="diffBody">';
        (d.tables || []).forEach((r) => {
            const [label, tone] = diffLabels[r.status] || [r.status, 'secondary'];
            html += '<tr data-name="' + esc(r.table.toLowerCase()) + '" data-eq="' + (r.status === 'equal' ? 1 : 0) + '"><td class="font-monospace">' + esc(r.table) + '</td><td class="text-end">' + esc(r.live_rows ?? '—') + '</td><td class="text-end">' + esc(r.backup_rows ?? '—') + '</td><td class="text-end"><span class="badge rounded-pill text-bg-' + tone + '">' + esc(label) + '</span></td></tr>';
        });
        $('#infoBody').innerHTML = html + '</tbody></table></div>';
        const filter = () => {
            const q = $('#diffSearch').value.toLowerCase().trim(), only = $('#diffOnly').checked;
            $$('#diffBody tr').forEach((tr) => tr.classList.toggle('d-none', (q && !tr.dataset.name.includes(q)) || (only && tr.dataset.eq === '1')));
        };
        $('#diffSearch').addEventListener('input', filter);
        $('#diffOnly').addEventListener('change', filter);
    }

    // ------------------------------------------------------------ restore: verify first, then a typed confirmation
    let restoreOk = false;
    const restoreReady = () => { $('#restoreGo').disabled = !(restoreOk && $('#restoreConfirm').value.trim() === B.restoreWord); };

    async function restore(file) {
        restoreOk = false;
        $('#restoreFile').textContent = file;
        $('#restoreForm').action = url('restore', file);
        $('#restoreConfirm').value = '';
        $('#restoreCheck').innerHTML = loading('ফাইলটি আগে যাচাই হচ্ছে…');
        restoreReady();
        modal('restoreModal').show();
        const d = await api('POST', url('dry-run', file));
        if ($('#restoreFile').textContent !== file) return; // another file was opened meanwhile
        restoreOk = !!d.passed;
        if (d.checks) markVerified(file, restoreOk);
        $('#restoreCheck').innerHTML = restoreOk
            ? banner(true, 'যাচাই সফল — ' + (d.stats ? bn(d.stats.tables) + ' টেবিল, ' + bn(d.stats.rows) + ' রো' : 'ফাইল ঠিক আছে'))
            : banner(false, 'যাচাই ব্যর্থ — এই ফাইল রিস্টোর করা যাবে না।') + (d.checks ? checkList(d.checks.filter((c) => !c.ok)) : '<small class="text-muted">' + esc(d.message || '') + '</small>');
        restoreReady();
        if (restoreOk) $('#restoreConfirm').focus();
    }

    // ---- selective restore
    let selectiveFile = '';
    const picked = () => $$('.pick-check:checked').map((c) => c.value);
    const updatePicked = () => { const n = picked().length; $('#selCount').textContent = bn(n) + 'টি নির্বাচিত'; $('#selectiveRun').disabled = n === 0; };

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
            if (!(await ask('Restore tables', bn(tables.length) + 'টি টেবিল ব্যাকআপ থেকে বদলে যাবে। এগোবেন?', 'রিস্টোর করুন'))) return;
            const res = await busy($('#selectiveRun'), () => api('POST', url('selective-restore', selectiveFile), { tables }));
            modal('selectiveModal').hide();
            toast(res.success ? 'success' : 'danger', firstError(res, 'রিস্টোর ব্যর্থ'));
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
        setTimeout(() => $('#emailTo').focus(), 300);
    }

    async function sendEmail() {
        const to = $('#emailTo').value.trim();
        if (!to || !$('#emailTo').checkValidity()) return toast('warning', 'সঠিক ইমেইল ঠিকানা দিন।');
        const res = await busy($('#emailSend'), () => api('POST', url('email', emailFile), { email: to }));
        if (res.success) modal('emailModal').hide();
        toast(res.success ? 'success' : 'danger', firstError(res, 'পাঠানো যায়নি'));
    }

    // ---- delete
    async function remove(file) {
        if (!(await ask('Delete', file + ' স্থায়ীভাবে মুছে যাবে।', 'Delete'))) return;
        const res = await api('DELETE', B.routes.base + '/' + encodeURIComponent(file));
        if (!res.success) return toast('danger', firstError(res, 'মোছা যায়নি'));
        const tr = rows().find((r) => r.dataset.file === file);
        if (tr) { tr.classList.add('is-leaving'); setTimeout(() => { tr.remove(); applyFilter(); }, 250); }
        toast('success', res.message);
    }

    async function bulkRemove(_, btn) {
        const files = $$('.row-check:checked').map((c) => c.value);
        if (!files.length) return;
        if (!(await ask('Delete', bn(files.length) + 'টি ব্যাকআপ স্থায়ীভাবে মুছে যাবে।', 'Delete'))) return;
        const res = await busy(btn, () => api('POST', B.routes.bulkDelete, { filenames: files }));
        if (!res.success) return toast('danger', firstError(res, 'মোছা যায়নি'));
        rows().filter((r) => files.includes(r.dataset.file)).forEach((r) => r.remove());
        $('#selectAll').checked = false;
        toast('success', res.message);
        applyFilter();
    }
    function bulkClear() { $$('.row-check').forEach((c) => { c.checked = false; }); updateBulk(); }

    async function copy(file) {
        try { await navigator.clipboard.writeText(file); toast('success', 'ফাইলের নাম কপি হয়েছে'); } catch (e) { toast('danger', 'কপি করা যায়নি'); }
    }

    // ---- tools: check, optimize, cleanup, masked
    async function integrity(_, btn) {
        const d = await busy(btn, () => { progress('টেবিল পরীক্ষা চলছে', 'প্রতিটি টেবিল দেখা হচ্ছে', 'fa-stethoscope'); return api('POST', B.routes.integrity); });
        await progressDone();
        if (d.checked === undefined) return toast('danger', firstError(d, 'পরীক্ষা ব্যর্থ'));
        let html = '<div class="row g-2 mb-3">' + stat('পরীক্ষিত টেবিল', bn(d.checked)) + stat('সমস্যা', bn((d.bad || []).length), (d.bad || []).length ? 'text-danger' : 'text-success') + stat('সময়', bn(d.ms) + ' ms') + '</div>';
        html += banner(d.success, d.success ? 'সব টেবিল ঠিক আছে — কোনো ক্ষতিগ্রস্ত টেবিল নেই।' : 'কিছু টেবিলে সমস্যা আছে। Optimize চালান, তারপরও থাকলে ভালো ব্যাকআপ থেকে সেই টেবিল রিস্টোর করুন।');
        if ((d.bad || []).length) html += '<ul class="bk-badlist">' + d.bad.map((b) => '<li class="font-monospace">' + esc(b) + '</li>').join('') + '</ul>';
        openInfo('Check', 'ডাটাবেজ', html);
    }

    async function optimize(_, btn) {
        if (!(await ask('Optimize', 'সব টেবিল গুছিয়ে খালি জায়গা ফেরত নেওয়া হবে। বড় ডাটাবেজে এক মিনিট লাগতে পারে।', 'Optimize', 'info'))) return;
        const d = await busy(btn, () => { progress('Optimize চলছে', 'টেবিল গোছানো হচ্ছে', 'fa-wand-magic-sparkles'); return api('POST', B.routes.optimize); });
        await progressDone();
        if (!d.success) return toast('danger', firstError(d, 'অপটিমাইজ ব্যর্থ'));
        openInfo('Optimize', 'ডাটাবেজ', banner(true, 'অপটিমাইজ সম্পন্ন') + '<div class="row g-2 mt-1">' + stat('আগে', d.before) + stat('এখন', d.after, 'text-success') + stat('টেবিল', bn(d.tables)) + stat('সময়', bn(d.ms) + ' ms') + '</div>');
    }

    async function cleanup(_, btn) {
        const keep = btn?.dataset.keep || '';
        if (!(await ask('Cleanup', 'সর্বশেষ ' + bn(keep) + 'টি রেখে বাকি পুরনো ব্যাকআপ মুছে যাবে।', 'Cleanup'))) return;
        const d = await busy(btn, () => api('POST', B.routes.cleanup));
        toast(d.success ? 'success' : 'danger', firstError(d, 'মোছা যায়নি'));
        if (d.success && !/নেই/.test(d.message || '')) setTimeout(() => location.reload(), 900);
    }

    async function masked(_, btn) {
        if (!(await ask('Masked', 'পাসওয়ার্ড, ইমেইল ও ফোন নম্বর বদলে দেওয়া একটি ডাম্প তৈরি হবে (ডেভেলপারদের জন্য)।', 'Create', 'info'))) return;
        const res = await busy(btn, () => { progress('Masked ডাম্প তৈরি হচ্ছে', 'গোপন তথ্য বদলানো হচ্ছে', 'fa-user-shield'); return api('POST', B.routes.masked); });
        await progressDone();
        if (!res.success) return toast('danger', firstError(res, 'ডাম্প ব্যর্থ'));
        toast('success', res.message);
        if (res.download_url) location.href = res.download_url;
        setTimeout(() => location.reload(), 1500);
    }

    async function telegramTest() {
        const token = $('#tgToken').value.trim(), chat = $('#tgChat').value.trim();
        const res = await busy($('#tgTest'), () => api('POST', B.routes.notify, { type: 'telegram', telegram_bot_token: token || undefined, telegram_chat_id: chat || undefined }));
        toast(res.success ? 'success' : 'danger', firstError(res, 'পাঠানো যায়নি'));
    }

    async function mailTest() {
        const input = $('#bkEmail'), to = input.value.trim();
        if (!to || !input.checkValidity()) return toast('warning', 'সঠিক ইমেইল ঠিকানা দিন।');
        const res = await busy($('#mailTest'), () => api('POST', B.routes.notify, { type: 'email', email: to }));
        toast(res.success ? 'success' : 'danger', firstError(res, 'পাঠানো যায়নি'));
    }

    // ------------------------------------------------------------ chart
    function initChart() {
        const el = $('#usageChart');
        if (!el || typeof Chart === 'undefined') return;
        const dark = document.body.classList.contains('dark-mode');
        const ink = dark ? '#aab6cc' : '#64748b', grid = dark ? 'rgba(255,255,255,.06)' : 'rgba(15,23,42,.06)';
        const ctx = el.getContext('2d');
        const fill = ctx.createLinearGradient(0, 0, 0, 220);
        fill.addColorStop(0, dark ? '#818cf8' : '#4f46e5');
        fill.addColorStop(1, dark ? 'rgba(129,140,248,.3)' : 'rgba(124,58,237,.3)');
        new Chart(el, {
            type: 'bar',
            data: { labels: B.timeline.labels, datasets: [{ data: B.timeline.backup_sizes_mb, backgroundColor: fill, hoverBackgroundColor: dark ? '#a5b4fc' : '#4338ca', borderRadius: 10, borderSkipped: false, maxBarThickness: 42 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { padding: 10, cornerRadius: 10, callbacks: { label: (c) => c.parsed.y + ' MB · ' + bn(B.timeline.archive_counts[c.dataIndex]) + 'টি' } } },
                scales: { x: { grid: { display: false }, ticks: { color: ink } }, y: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { color: ink, callback: (v) => v + ' MB' } } },
            },
        });
    }

    // ------------------------------------------------------------ wiring
    const actions = {
        preview, verify, diff, restore, selective, email, copy, delete: remove, masked, integrity, optimize, cleanup,
        'bulk-delete': bulkRemove, 'bulk-clear': bulkClear,
    };

    document.addEventListener('click', (e) => {
        const create = e.target.closest('[data-backup]');
        if (create) return createBackup(create.dataset.backup);
        const act = e.target.closest('[data-act]');
        if (act && actions[act.dataset.act]) actions[act.dataset.act](act.dataset.file, act);
    });

    document.addEventListener('DOMContentLoaded', () => {
        initUpload(); initList(); initSelective(); initChart();
        $('#emailSend')?.addEventListener('click', sendEmail);
        $('#emailTo')?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); sendEmail(); } });
        $('#restoreConfirm')?.addEventListener('input', restoreReady);
        // a restore can take a while: show it is working so nobody clicks twice
        $('#restoreForm')?.addEventListener('submit', (e) => {
            if (!restoreOk || $('#restoreConfirm').value.trim() !== B.restoreWord) { e.preventDefault(); return; }
            $('#restoreGo').disabled = true;
            modal('restoreModal').hide();
            progress('Restore চলছে', 'আগে সেফটি কপি নেওয়া হচ্ছে, তারপর ডাটাবেজ বদলানো হবে', 'fa-rotate-left');
        });
        $('#settingsModal form')?.addEventListener('submit', () => {
            const b = $('#settingsSave'); if (b) { b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>সংরক্ষণ হচ্ছে…'; }
        });
        $('#tgTest')?.addEventListener('click', telegramTest);
        $('#mailTest')?.addEventListener('click', mailTest);
        applyFilter();
    });
})();
