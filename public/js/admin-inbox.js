/**
 * Admin › Inquiries inbox. Folders, clickable KPIs, search, paging, bulk actions, hover quick actions,
 * reading pane with prev/next, reply (SMS / e-mail), call / WhatsApp log, internal note (autosave),
 * history, other messages from the same number, keyboard shortcuts, and a poll that announces new messages.
 *
 * Everything from the server is put in the page with textContent; links are built only from checked patterns.
 */
(function () {
    'use strict';
    const root = document.getElementById('ib');
    if (!root) return;

    const $ = (s, r) => (r || document).querySelector(s);
    const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
    const bn = (v) => Number(v || 0).toLocaleString('bn-BD');
    const csrf = () => $('meta[name="csrf-token"]')?.content || '';
    const isAdmin = root.dataset.admin === '1';
    const base = root.dataset.baseUrl;
    const STATUS = { new: ['নতুন', 'new'], read: ['পড়া হয়েছে', 'read'], replied: ['উত্তর দেওয়া', 'done'], spam: ['স্প্যাম', 'spam'], archived: ['আর্কাইভ', 'arch'] };
    const CH = { sms: ['fa-comment-sms', 'SMS'], email: ['fa-envelope', 'ইমেইল'], call: ['fa-phone', 'কল'], whatsapp: ['fa-whatsapp', 'WhatsApp'], note: ['fa-note-sticky', 'নোট'] };
    const FOLDER_NAME = { inbox: 'ইনবক্স', new: 'নতুন', starred: 'তারকা', replied: 'উত্তর দেওয়া', archived: 'আর্কাইভ', spam: 'স্প্যাম', trash: 'ট্র্যাশ' };
    const QUICK = [
        ['ধন্যবাদ', 'ধন্যবাদ, আপনার বার্তা পেয়েছি। শীঘ্রই আমাদের প্রতিনিধি যোগাযোগ করবেন। — আইডিয়া প্রকাশন'],
        ['অর্ডার দেখছি', 'আপনার অর্ডারের বিষয়টি দেখছি, আজকের মধ্যেই জানাব। — আইডিয়া প্রকাশন'],
        ['কল করুন', 'বিস্তারিত জানতে কল করুন: 01726976982 — আইডিয়া প্রকাশন'],
    ];

    const state = { folder: 'inbox', q: '', page: 1, pages: 1, total: 0, items: [], selected: new Set(), current: null, latest: 0 };
    const toast = (ok, msg) => { if (window.SwalToast) SwalToast(ok ? 'success' : 'error', msg); };
    const confirmBox = async (title, text, icon, btn) => window.SwalConfirm
        ? (await SwalConfirm(Object.assign({ title, text, icon }, btn ? { confirmButtonText: btn } : {}))).isConfirmed
        : confirm(title);

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text != null) e.textContent = text;
        return e;
    }
    function icon(cls) { const i = el('i', cls); i.setAttribute('aria-hidden', 'true'); return i; }
    function initial(name) { return ([...(name || '?').trim()][0] || '?').toUpperCase(); }
    function avClass(name) { let h = 0; for (const c of String(name || '')) h = (h * 31 + c.codePointAt(0)) >>> 0; return 'ib-av-' + (h % 7); }

    async function api(url, opts) {
        const o = opts || {};
        const write = o.method && o.method !== 'GET';
        const res = await fetch(url, {
            method: o.method || 'GET',
            headers: Object.assign({ Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, o.body ? { 'Content-Type': 'application/json' } : {}, write ? { 'X-CSRF-TOKEN': csrf() } : {}),
            body: o.body ? JSON.stringify(o.body) : undefined,
            credentials: 'same-origin',
        });
        let data = {};
        try { data = await res.json(); } catch (e) { /* not JSON */ }
        if (res.status === 419) { toast(false, 'সেশন শেষ — পেজটি রিফ্রেশ করুন।'); throw new Error('csrf'); }
        if (res.status === 403) { toast(false, data.message || 'এই কাজের অনুমতি আপনার নেই।'); throw new Error('forbidden'); }
        if (res.status === 429) { toast(false, 'খুব দ্রুত চেষ্টা হচ্ছে — একটু পরে আবার করুন।'); throw new Error('throttle'); }
        if (!res.ok) {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'কাজটি হয়নি।');
            toast(false, msg);
            throw new Error(msg);
        }
        return data;
    }

    /** Runs fn with the button showing a spinner, and always puts the button back. */
    async function busy(btn, fn) {
        if (!btn) return fn();
        const old = [...btn.childNodes].map((n) => n.cloneNode(true));
        btn.disabled = true; btn.classList.add('is-busy');
        const ic = btn.querySelector('i');
        if (ic) ic.replaceWith(el('span', 'spinner-border spinner-border-sm'));
        try { return await fn(); } finally { if (btn.isConnected) { btn.replaceChildren(...old); btn.disabled = false; btn.classList.remove('is-busy'); } }
    }

    function paintCounts(c) {
        if (!c) return;
        $$('[data-count]', root).forEach((b) => {
            const k = b.dataset.count, v = Number(c[k] || 0);
            b.textContent = b.closest('.ib-folder') ? (v ? bn(v) : '') : bn(v);
        });
        const badge = document.querySelector('.adm-nav__link[href$="/admin/communication"] .badge');
        if (badge) { badge.textContent = c.new || ''; badge.hidden = !c.new; }
    }
    function paintKpis() { $$('[data-kpi]', root).forEach((k) => k.classList.toggle('is-on', k.dataset.kpi === state.folder && k.dataset.kpi !== 'inbox')); }

    /* ---------------- list ---------------- */
    async function load(keepCurrent) {
        const box = $('#ibItems');
        box.setAttribute('aria-busy', 'true');
        try {
            const p = new URLSearchParams({ folder: state.folder, page: state.page });
            if (state.q) p.set('q', state.q);
            const d = await api(root.dataset.listUrl + '?' + p);
            state.items = d.items; state.pages = d.pages; state.page = d.page; state.total = d.total ?? d.items.length;
            if (!keepCurrent) state.selected.clear();
            paintCounts(d.counts);
            renderList();
        } catch (e) {
            const x = el('div', 'ib-empty');
            x.append(el('div', 'ib-empty__ic'), el('b', null, 'তালিকা আনা যায়নি'));
            x.firstChild.append(icon('fa-solid fa-plug-circle-xmark'));
            const retry = el('button', 'ib-btn ib-btn--sm mt-2'); retry.type = 'button'; retry.append(icon('fa-solid fa-rotate-right'), document.createTextNode(' আবার চেষ্টা'));
            retry.addEventListener('click', () => load());
            x.append(retry);
            box.replaceChildren(x);
        } finally {
            box.removeAttribute('aria-busy');
        }
    }

    function renderList() {
        const box = $('#ibItems');
        box.replaceChildren();
        if (!state.items.length) {
            const e = el('div', 'ib-empty');
            const ic = el('div', 'ib-empty__ic'); ic.append(icon(state.q ? 'fa-solid fa-magnifying-glass' : 'fa-regular fa-face-smile'));
            e.append(ic, el('b', null, state.q ? 'মিল পাওয়া যায়নি' : 'সব পরিষ্কার!'), document.createTextNode(state.q ? 'অন্য শব্দ বা নম্বরের অংশ দিয়ে খুঁজুন।' : 'এই ফোল্ডারে কোনো বার্তা নেই।'));
            box.append(e);
        }
        state.items.forEach((it) => {
            const sel = state.selected.has(it.id);
            const row = el('div', 'ib-item' + (it.status === 'new' ? ' is-new' : '') + (state.current === it.id ? ' is-open' : '') + (sel ? ' is-sel' : ''));
            row.setAttribute('role', 'listitem');
            row.dataset.id = it.id;
            row.tabIndex = 0;

            const selBox = el('div', 'ib-sel');
            const ck = el('label', 'ib-check');
            const cb = el('input'); cb.type = 'checkbox'; cb.checked = sel; cb.setAttribute('aria-label', 'বাছাই: ' + (it.name || '')); cb.dataset.sel = it.id;
            ck.append(cb);
            selBox.append(ck, el('div', 'ib-avatar ib-avatar--sm ' + avClass(it.name), initial(it.name)));

            const body = el('div', 'ib-item__body');
            const top = el('div', 'ib-item__top');
            const time = el('time', 'ib-item__time', it.time); time.title = it.date || '';
            top.append(el('span', 'ib-item__name', it.name), time);
            const tags = el('div', 'ib-item__tags');
            if (it.spam >= 60) tags.append(el('span', 'ib-badge ib-badge--spam', 'স্প্যাম?'));
            if (it.status === 'replied' && state.folder !== 'replied') { const t = el('span', 'ib-badge ib-badge--done'); t.append(icon('fa-solid fa-check-double'), document.createTextNode(' উত্তর দেওয়া')); tags.append(t); }
            if (it.email) { const t = el('span', 'ib-badge ib-badge--mail'); t.append(icon('fa-regular fa-envelope'), document.createTextNode(' ইমেইল')); tags.append(t); }
            body.append(top, el('div', 'ib-item__sub', it.subject || '(বিষয় নেই)'), el('div', 'ib-item__snip', it.snippet), tags);

            const star = el('button', 'ib-star' + (it.starred ? ' is-on' : ''));
            star.type = 'button'; star.dataset.star = it.id; star.setAttribute('aria-label', it.starred ? 'তারকা সরান' : 'তারকা দিন'); star.title = 'তারকা';
            star.append(icon((it.starred ? 'fa-solid' : 'fa-regular') + ' fa-star'));

            row.append(selBox, body, star);
            if (state.folder !== 'trash') {
                const acts = el('div', 'ib-item__acts');
                const qa = [];
                if (it.status !== 'archived') qa.push(['archived', 'fa-box-archive', 'আর্কাইভ']);
                qa.push(it.status === 'new' ? ['read', 'fa-envelope-open', 'পড়া হয়েছে'] : ['new', 'fa-envelope', 'পড়া হয়নি']);
                qa.push(['trash', 'fa-trash-can', 'ট্র্যাশ']);
                qa.forEach(([a, i, l]) => { const b = el('button'); b.type = 'button'; b.dataset.qa = a; b.title = l; b.setAttribute('aria-label', l); b.append(icon('fa-solid ' + i)); acts.append(b); });
                row.append(acts);
            }
            box.append(row);
        });
        $('#ibPager').hidden = state.pages <= 1;
        $('#ibPageInfo').textContent = `পাতা ${bn(state.page)} / ${bn(state.pages)}`;
        $('[data-page="prev"]').disabled = state.page <= 1;
        $('[data-page="next"]').disabled = state.page >= state.pages;
        $('#ibListInfo').replaceChildren(el('b', null, FOLDER_NAME[state.folder] || ''), document.createTextNode(` · ${bn(state.total)}টি বার্তা` + (state.q ? ` · "${state.q}"` : '')));
        paintKpis();
        paintBulk();
    }

    function paintBulk() {
        const n = state.selected.size;
        $('#ibBulk').hidden = n === 0;
        root.classList.toggle('has-sel', n > 0);
        $('#ibSelN').textContent = bn(n);
        const all = $('#ibAll');
        const onPage = state.items.filter((i) => state.selected.has(i.id)).length;
        all.checked = onPage > 0 && onPage === state.items.length;
        all.indeterminate = onPage > 0 && onPage < state.items.length;
        $$('.ib-item', root).forEach((r) => r.classList.toggle('is-sel', state.selected.has(Number(r.dataset.id))));
        const acts = state.folder === 'trash'
            ? [['restore', 'fa-rotate-left', 'ফেরত'], ...(isAdmin ? [['purge', 'fa-fire', 'চিরতরে মুছুন']] : [])]
            : state.folder === 'spam'
                ? [['inbox', 'fa-inbox', 'স্প্যাম নয়'], ['trash', 'fa-trash-can', 'ট্র্যাশ']]
                : [['read', 'fa-envelope-open', 'পড়া'], ['new', 'fa-envelope', 'অপঠিত'], ['replied', 'fa-check-double', 'সম্পন্ন'], ['archived', 'fa-box-archive', 'আর্কাইভ'], ['spam', 'fa-shield-virus', 'স্প্যাম'], ['trash', 'fa-trash-can', 'ট্র্যাশ']];
        $('#ibBulkBtns').replaceChildren(...acts.map(([a, i, l]) => {
            const b = el('button', 'ib-bulk__btn' + (a === 'purge' || a === 'trash' ? ' ib-bulk__btn--danger' : ''));
            b.type = 'button'; b.dataset.bulk = a; b.title = l; b.append(icon('fa-solid ' + i), el('span', null, l));
            return b;
        }));
    }

    async function setStatus(id, status, btn) {
        return busy(btn, async () => {
            const d = await api(`${base}/${id}/status`, { method: 'POST', body: { status } });
            toast(true, d.message);
            return d;
        });
    }
    async function trash(id, btn) {
        if (!(await confirmBox('ট্র্যাশে পাঠাবেন?', '৩০ দিন পর্যন্ত ফেরত আনা যাবে।', 'warning'))) return false;
        return busy(btn, async () => { const d = await api(`${base}/${id}`, { method: 'DELETE' }); toast(true, d.message); return true; });
    }

    $('#ibItems').addEventListener('click', async (e) => {
        const cb = e.target.closest('[data-sel]');
        if (cb) { const id = Number(cb.dataset.sel); cb.checked ? state.selected.add(id) : state.selected.delete(id); paintBulk(); return; }
        if (e.target.closest('.ib-check')) return;
        const st = e.target.closest('[data-star]');
        if (st) { star(Number(st.dataset.star)); return; }
        const qa = e.target.closest('[data-qa]');
        if (qa) {
            const id = Number(qa.closest('.ib-item').dataset.id);
            try {
                if (qa.dataset.qa === 'trash') { if (!(await trash(id, qa))) return; if (state.current === id) closeRead(); }
                else { await setStatus(id, qa.dataset.qa, qa); if (state.current === id) open(id, true); }
                load(true);
            } catch (err) { /* toast shown */ }
            return;
        }
        const row = e.target.closest('.ib-item');
        if (row) open(Number(row.dataset.id));
    });
    $('#ibItems').addEventListener('keydown', (e) => {
        const row = e.target.closest('.ib-item');
        if (!row || e.target !== row) return;
        if (e.key === 'Enter') open(Number(row.dataset.id));
        if (e.key === ' ') { e.preventDefault(); const cb = $('[data-sel]', row); cb.click(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); row.nextElementSibling?.focus(); }
        if (e.key === 'ArrowUp') { e.preventDefault(); row.previousElementSibling?.focus(); }
    });
    $('#ibAll').addEventListener('change', (e) => {
        state.items.forEach((i) => e.target.checked ? state.selected.add(i.id) : state.selected.delete(i.id));
        renderList();
    });
    $('#ibBulkX').addEventListener('click', () => { state.selected.clear(); paintBulk(); $$('[data-sel]', root).forEach((c) => { c.checked = false; }); });
    $('#ibBulkBtns').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-bulk]');
        if (!b) return;
        const action = b.dataset.bulk, n = bn(state.selected.size);
        if (action === 'purge' && !(await confirmBox(`${n}টি বার্তা চিরতরে মুছবেন?`, 'আর ফেরত আনা যাবে না।', 'error'))) return;
        if (action === 'trash' && !(await confirmBox(`${n}টি বার্তা ট্র্যাশে পাঠাবেন?`, '৩০ দিন পর্যন্ত ফেরত আনা যাবে।', 'warning'))) return;
        try {
            await busy(b, async () => {
                const d = await api(root.dataset.bulkUrl, { method: 'POST', body: { ids: [...state.selected], action } });
                toast(true, d.message);
            });
            if (state.current && state.selected.has(state.current)) closeRead();
            state.selected.clear();
            load();
        } catch (err) { /* toast shown */ }
    });
    $$('[data-page]').forEach((b) => b.addEventListener('click', () => {
        const next = b.dataset.page === 'next' ? state.page + 1 : state.page - 1;
        if (next < 1 || next > state.pages) return;
        state.page = next; load();
        $('#ibItems').scrollTop = 0;
    }));

    function goFolder(folder) {
        const b = $(`[data-folder="${folder}"]`, root);
        if (!b) return;
        state.folder = folder; state.page = 1;
        $$('[data-folder]', root).forEach((x) => { x.classList.toggle('is-on', x === b); x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        closeRead(); load();
    }
    $$('[data-folder]', root).forEach((b) => b.addEventListener('click', () => goFolder(b.dataset.folder)));
    $$('[data-kpi]', root).forEach((k) => k.addEventListener('click', () => goFolder(k.dataset.kpi)));

    const search = $('#ibSearch'), searchX = $('#ibSearchX');
    let deb;
    const runSearch = () => { state.q = search.value.trim(); state.page = 1; load(); };
    search.addEventListener('input', () => { searchX.hidden = !search.value; clearTimeout(deb); deb = setTimeout(runSearch, 300); });
    search.addEventListener('keydown', (e) => { if (e.key === 'Escape') { search.value = ''; searchX.hidden = true; runSearch(); search.blur(); } });
    searchX.addEventListener('click', () => { search.value = ''; searchX.hidden = true; runSearch(); search.focus(); });

    document.querySelector('[data-ib-refresh]')?.addEventListener('click', (e) => {
        const b = e.currentTarget;
        busy(b, async () => { await load(true); if (state.current) await open(state.current, true); });
    });

    async function star(id) {
        try {
            const d = await api(`${base}/${id}/star`, { method: 'POST', body: {} });
            const it = state.items.find((x) => x.id === id);
            if (it) it.starred = d.starred;
            renderList();
            if (state.current === id) paintStar(d.starred);
            load(true);
        } catch (e) { /* toast shown */ }
    }

    /* ---------------- reading pane ---------------- */
    const read = $('#ibRead');
    function closeRead() {
        state.current = null;
        root.classList.remove('is-reading');
        read.replaceChildren($('#ibEmptyTpl').content.cloneNode(true));
        $$('.ib-item.is-open', root).forEach((r) => r.classList.remove('is-open'));
    }
    function paintStar(on) {
        const b = $('[data-act="star"]', read);
        if (b) { b.classList.toggle('is-on', on); $('i', b).className = (on ? 'fa-solid' : 'fa-regular') + ' fa-star'; }
    }
    function neighbour(step) {
        const i = state.items.findIndex((x) => x.id === state.current);
        if (i === -1) return state.items[step > 0 ? 0 : state.items.length - 1] || null;
        return state.items[i + step] || null;
    }
    function go(step) {
        const n = neighbour(step);
        if (n) { open(n.id); $(`.ib-item[data-id="${n.id}"]`)?.scrollIntoView({ block: 'nearest' }); }
    }

    async function open(id, quiet) {
        state.current = id;
        root.classList.add('is-reading');
        $$('.ib-item', root).forEach((r) => r.classList.toggle('is-open', Number(r.dataset.id) === id));
        if (!quiet) {
            const sk = el('div', 'ib-read__scroll');
            for (let i = 0; i < 3; i++) sk.append($('#ibItems .ib-skel')?.cloneNode(true) || (() => { const s = el('div', 'ib-skel'); s.innerHTML = '<i></i><div><span></span><span></span><span></span></div>'; return s; })());
            read.replaceChildren(sk);
        }
        let d;
        try { d = await api(`${base}/${id}`); } catch (e) { closeRead(); return; }
        if (state.current !== id) return;
        paintCounts(d.counts);
        const it = state.items.find((x) => x.id === id);
        if (it && it.status === 'new') { it.status = 'read'; $(`.ib-item[data-id="${id}"]`)?.classList.remove('is-new'); }

        const draft = quiet ? ($('[data-f="rbody"]', read)?.value || '') : '';
        read.replaceChildren($('#ibReadTpl').content.cloneNode(true));
        const f = (k) => $(`[data-f="${k}"]`, read);
        f('initial').textContent = initial(d.name);
        f('initial').classList.add(avClass(d.name));
        f('name').textContent = d.name;
        if (d.member) { f('member').hidden = false; f('member').textContent = 'সদস্য: ' + d.member; }
        const [sl, sc] = STATUS[d.status] || [d.status, 'read'];
        f('status').textContent = d.trashed ? 'ট্র্যাশে' : sl;
        f('status').className = 'ib-badge ib-badge--' + (d.trashed ? 'spam' : sc);
        f('date').textContent = d.date; f('ago').textContent = d.ago;
        if (d.ip) { f('ipwrap').hidden = false; f('ip').textContent = d.ip; }
        if (d.spam >= 40) { f('spamwarn').hidden = false; f('spam').textContent = bn(d.spam); }
        f('phone').textContent = d.phone;
        const tel = (d.phone || '').replace(/[^\d+]/g, '');
        if (tel) f('tel').href = 'tel:' + tel; else { f('tel').hidden = true; $('[data-act="copy"]', read).hidden = true; }
        if (d.whatsapp && /^https:\/\/wa\.me\/\d{10,15}$/.test(d.whatsapp)) f('wa').href = d.whatsapp; else f('wa').hidden = true;
        if (d.email && /^[^\s@<>"]+@[^\s@<>"]+$/.test(d.email)) {
            f('mail').hidden = false; f('email').textContent = d.email;
            f('mail').href = 'mailto:' + encodeURIComponent(d.email).replace('%40', '@') + '?subject=' + encodeURIComponent('Re: ' + (d.subject || ''));
        }
        f('subject').textContent = d.subject || '(বিষয় নেই)';
        f('message').textContent = d.message;
        f('note').value = d.note || '';
        paintStar(d.starred);

        // tools per state
        const show = (a, on) => { const b = $(`[data-act="${a}"]`, read); if (b) b.hidden = !on; };
        show('restore', d.trashed); show('trash', !d.trashed); show('star', !d.trashed);
        show('archive', !d.trashed && d.status !== 'archived'); show('spam', !d.trashed && d.status !== 'spam');
        show('inbox', !d.trashed && (d.status === 'spam' || d.status === 'archived')); show('unread', !d.trashed && d.status !== 'spam');
        show('done', !d.trashed && !['replied', 'spam'].includes(d.status));
        $('[data-act="prev"]', read).disabled = !neighbour(-1) || neighbour(-1).id === id;
        $('[data-act="next"]', read).disabled = !neighbour(1) || neighbour(1).id === id;
        $('.ib-reply', read).hidden = d.trashed || d.status === 'spam';

        // reply
        const body = f('rbody'), subj = f('rsubject');
        body.value = draft;
        const mailOpt = $('#ibChMail', read);
        mailOpt.disabled = !d.email;
        $('label[for="ibChMail"]', read).title = d.email ? '' : 'এই বার্তায় ইমেইল নেই';
        subj.value = 'Re: ' + (d.subject || '');
        const ch = () => ($('input[name="ibCh"]:checked', read) || {}).value || 'sms';
        const paintReply = () => {
            const isSms = ch() === 'sms', t = body.value, uni = /[^\x00-\x7F]/.test(t), len = [...t].length;
            subj.hidden = isSms;
            body.maxLength = isSms ? 600 : 5000;
            const max = isSms ? 600 : 5000;
            const parts = len ? (len <= (uni ? 70 : 160) ? 1 : Math.ceil(len / (uni ? 67 : 153))) : 0;
            f('rcount').textContent = isSms ? `${bn(len)}/৬০০ · ${bn(parts)} SMS` : `${bn(len)} অক্ষর`;
            f('meterbar').style.width = Math.min(100, (len / max) * 100) + '%';
            f('meter').classList.toggle('is-warn', isSms && parts > 2);
            f('sendlbl').textContent = isSms ? 'SMS পাঠান' : 'ইমেইল পাঠান';
        };
        $$('input[name="ibCh"]', read).forEach((r) => r.addEventListener('change', paintReply));
        body.addEventListener('input', paintReply);
        body.addEventListener('keydown', (e) => { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); $('[data-act="send"]', read).click(); } });
        f('quick').replaceChildren(...QUICK.map(([label, text]) => {
            const b = el('button', 'ib-qbtn'); b.type = 'button'; b.title = text;
            b.append(icon('fa-solid fa-bolt'), el('span', null, label));
            b.addEventListener('click', () => { body.value = text; paintReply(); body.focus(); });
            return b;
        }));
        paintReply();

        // history
        const tl = f('timeline');
        f('tlcount').textContent = d.replies.length ? `${bn(d.replies.length)}টি কাজ` : '';
        if (!d.replies.length) tl.append(el('li', 'ib-timeline__empty', 'এখনো কোনো উত্তর বা কাজ নেই।'));
        d.replies.forEach((r) => {
            const [ic, lb] = CH[r.channel] || ['fa-circle', r.channel];
            const li = el('li', r.ok ? '' : 'is-fail');
            const head = el('div', 'ib-timeline__head');
            head.append(icon((r.channel === 'whatsapp' ? 'fa-brands ' : 'fa-solid ') + ic), el('b', null, lb), el('span', null, `${r.by || ''} · ${r.time || ''}`));
            if (!r.ok) head.append(el('span', 'ib-badge ib-badge--spam', 'ব্যর্থ'));
            li.append(head);
            if (r.body) li.append(el('div', 'ib-timeline__body', r.body));
            if (r.error) li.append(el('div', 'ib-timeline__err', r.error));
            tl.append(li);
        });

        // other messages from the number
        if (d.others.length) {
            f('otherswrap').hidden = false;
            f('others').replaceChildren(...d.others.map((o) => {
                const li = el('li'); const b = el('button', 'ib-other');
                b.type = 'button';
                b.append(el('span', null, o.subject || 'বার্তা'), el('small', null, `${o.date} · ${(STATUS[o.status] || [o.status])[0]}`));
                b.addEventListener('click', () => open(o.id));
                li.append(b); return li;
            }));
        }

        // note autosave
        let nt; const ns = f('notestate');
        f('note').addEventListener('input', () => {
            clearTimeout(nt); ns.className = 'ib-saved'; ns.textContent = 'লেখা হচ্ছে…';
            nt = setTimeout(async () => {
                try { await api(`${base}/${id}/note`, { method: 'POST', body: { note: f('note').value } }); ns.className = 'ib-saved is-ok'; ns.textContent = '✓ সংরক্ষিত'; }
                catch (e) { ns.className = 'ib-saved is-bad'; ns.textContent = 'সংরক্ষণ হয়নি'; }
            }, 800);
        });
    }

    read.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-act]');
        if (!b || !state.current) return;
        const id = state.current, act = b.dataset.act;
        const run = async (url, body, after) => {
            try {
                await busy(b, async () => { const d = await api(url, { method: 'POST', body: body || {} }); toast(true, d.message); });
                if (after) after(); else open(id, true);
                load(true);
            } catch (err) { /* toast shown */ }
        };
        switch (act) {
            case 'back': return closeRead();
            case 'prev': return go(-1);
            case 'next': return go(1);
            case 'star': return star(id);
            case 'copy': {
                const num = $('[data-f="phone"]', read).textContent;
                try { await navigator.clipboard.writeText(num); toast(true, 'নম্বর কপি হয়েছে'); } catch (err) { toast(false, 'কপি করা যায়নি'); }
                return;
            }
            case 'unread': return run(`${base}/${id}/status`, { status: 'new' }, () => closeRead());
            case 'done': return run(`${base}/${id}/status`, { status: 'replied' });
            case 'archive': return run(`${base}/${id}/status`, { status: 'archived' }, () => (neighbour(1) ? go(1) : closeRead()));
            case 'spam': return run(`${base}/${id}/status`, { status: 'spam' }, () => (neighbour(1) ? go(1) : closeRead()));
            case 'inbox': return run(`${base}/${id}/status`, { status: 'read' });
            case 'restore': return run(`${base}/${id}/restore`, {});
            case 'trash':
                try { if (await trash(id, b)) { closeRead(); load(); } } catch (err) { /* toast */ }
                return;
            case 'logcall':
            case 'logwa':
                return run(`${base}/${id}/log`, { channel: act === 'logcall' ? 'call' : 'whatsapp', body: $('[data-f="rbody"]', read).value.trim() || null });
            case 'send': {
                const channel = ($('input[name="ibCh"]:checked', read) || {}).value || 'sms';
                const body = $('[data-f="rbody"]', read).value.trim();
                if (body.length < 2) { toast(false, 'উত্তর লিখুন।'); $('[data-f="rbody"]', read).focus(); return; }
                if (!(await confirmBox(channel === 'sms' ? 'SMS পাঠাবেন?' : 'ইমেইল পাঠাবেন?', body.slice(0, 160), 'question', 'পাঠান'))) return;
                return run(`${base}/${id}/reply`, { channel, body, subject: $('[data-f="rsubject"]', read).value }, () => { $('[data-f="rbody"]', read).value = ''; open(id, true); });
            }
        }
    });

    /* ---------------- keyboard ---------------- */
    const help = $('#ibHelp');
    const openHelp = () => { if (help.showModal) help.showModal(); };
    document.querySelector('[data-ib-help]')?.addEventListener('click', openHelp);
    help.addEventListener('click', (e) => { if (e.target === help || e.target.closest('[data-close]')) help.close(); });

    document.addEventListener('keydown', (e) => {
        const t = e.target;
        const typing = t.closest && t.closest('input, textarea, select, [contenteditable="true"]');
        if (e.key === 'Escape') {
            if (help.open) return;
            if (root.classList.contains('is-reading') && window.innerWidth < 992 && !typing) closeRead();
            return;
        }
        if (typing || e.ctrlKey || e.metaKey || e.altKey || document.querySelector('.swal2-container')) return;
        const tool = (a) => { const b = $(`[data-act="${a}"]`, read); if (b && !b.hidden && !b.disabled) b.click(); };
        switch (e.key) {
            case '/': e.preventDefault(); search.focus(); break;
            case '?': e.preventDefault(); openHelp(); break;
            case 'j': case 'J': e.preventDefault(); go(1); break;
            case 'k': case 'K': e.preventDefault(); go(-1); break;
            case 'r': case 'R': if (state.current) { e.preventDefault(); $('[data-f="rbody"]', read)?.focus(); } break;
            case 's': case 'S': if (state.current) tool('star'); break;
            case 'e': case 'E': if (state.current) tool('archive'); break;
            case 'u': case 'U': if (state.current) tool('unread'); break;
            case '#': if (state.current) tool('trash'); break;
        }
    });

    /* ---------------- new messages ---------------- */
    async function poll() {
        try {
            const d = await api(root.dataset.pollUrl + '?after=' + state.latest);
            paintCounts(d.counts);
            if (state.latest && d.fresh > 0) {
                toast(true, `${bn(d.fresh)}টি নতুন বার্তা এসেছে`);
                if (['inbox', 'new'].includes(state.folder) && state.page === 1 && !state.q) load(true);
            }
            state.latest = Math.max(state.latest, d.latest || 0);
        } catch (e) { /* next round */ }
    }
    setInterval(() => { if (!document.hidden) poll(); }, 30000);

    closeRead();
    load();
    poll();
})();
