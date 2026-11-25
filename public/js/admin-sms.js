/**
 * Admin › Messaging (SMS & e-mail).
 *
 * Send      one composer: SMS, e-mail or both; {name} {role} {phone} make every SMS personal
 * Test      one panel for SMS / e-mail / OTP tests
 * History   campaign list (search, channel filter, details)
 * Templates saved texts → "use" puts them into the composer
 * Settings  SMS gateway key check, show / hide key
 * + live progress of running SMS campaigns, balance refresh for the health banner.
 */
(function () {
    'use strict';

    const $ = (sel, root) => (root || document).querySelector(sel);
    const $$ = (sel, root) => Array.from((root || document).querySelectorAll(sel));
    const csrf = () => $('meta[name="csrf-token"]')?.content || '';
    const bn = (v) => Number(v || 0).toLocaleString('bn-BD');
    const tk = (v) => '৳' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* private mode */ } },
        del(k) { try { localStorage.removeItem(k); } catch (e) { /* private mode */ } },
    };

    /** Server and gateway texts go into the page as text, never as HTML. */
    window.escapeHtml = function (value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    };
    const esc = window.escapeHtml;

    function showTab(id) {
        const btn = document.getElementById(id);
        if (btn && window.bootstrap) bootstrap.Tab.getOrCreateInstance(btn).show();
    }
    window.switchMessagingTab = showTab;

    /** Characters → SMS parts (Bangla / any non-ASCII = Unicode: 70, then 67 per part). */
    function smsParts(text) {
        const len = [...(text || '')].length;
        const unicode = /[^\x00-\x7F]/.test(text || '');
        const single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
        const parts = len === 0 ? 0 : (len <= single ? 1 : Math.ceil(len / multi));
        const limit = parts <= 1 ? single : multi;
        const used = len === 0 ? 0 : (len % limit || limit);
        return { len, unicode, parts, pct: Math.min(100, Math.round(used / limit * 100)) };
    }

    window.insertShortcode = function (code, textareaId) {
        const ta = document.getElementById(textareaId);
        if (!ta) return;
        const s = ta.selectionStart ?? ta.value.length, e = ta.selectionEnd ?? ta.value.length;
        ta.value = ta.value.slice(0, s) + code + ta.value.slice(e);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = s + code.length;
        ta.dispatchEvent(new Event('input', { bubbles: true }));
    };

    /* ---------------- tabs: remember the last one, a #tab-… link wins ---------------- */
    (function tabs() {
        const KEY = 'sms_hub_tab_v2';
        const hash = location.hash && $(`#messagingHubTabs button[data-bs-target="${location.hash}"]`) ? location.hash : null;
        const target = hash || store.get(KEY);
        const btn = target && $(`#messagingHubTabs button[data-bs-target="${target}"]`);
        if (btn && window.bootstrap) bootstrap.Tab.getOrCreateInstance(btn).show();
        $$('#messagingHubTabs button[data-bs-toggle="pill"]').forEach((b) => b.addEventListener('shown.bs.tab', (e) => store.set(KEY, e.target.getAttribute('data-bs-target'))));
    })();

    /* ---------------- Send: the composer ---------------- */
    const form = $('#composeForm');
    if (form) {
        const msg = $('#composeMessage'), subj = $('#composeSubject');
        const actText = $('#composeActionText'), actUrl = $('#composeActionUrl');
        const customBox = $('#customListBox'), customTa = $('[name="custom_list"]', form);
        const phones = window.messagingCounts || {}, mails = window.emailCounts || {};
        const cost = Number(window.smsCostPerSms || 0.35);
        const DRAFT = 'sms_compose_draft_v1';
        const sample = { '{name}': 'রহিম', '{role}': 'গ্রাহক', '{phone}': '01712345678' };

        const channel = () => ($('input[name="channel"]:checked', form) || {}).value || 'sms';
        const group = () => ($('input[name="target_group"]:checked', form) || {}).value || 'customers';
        const countKey = (g) => (g === 'all' ? 'total_users' : g);

        function customCounts() {
            let p = 0, m = 0;
            (customTa?.value || '').split(/\r?\n/).forEach((line) => {
                const parts = line.split('|').map((x) => x.trim());
                const contacts = parts.length > 1 ? [parts[0]] : parts[0].split(/[\s,;]+/);
                contacts.filter(Boolean).forEach((c) => { if (c.includes('@')) m++; else if (c.replace(/\D/g, '').length >= 10) p++; });
            });
            return { p, m };
        }

        function audience() {
            const g = group();
            if (g === 'custom') return customCounts();
            return { p: Number(phones[countKey(g)] || 0), m: Number(mails[countKey(g)] || 0) };
        }

        function paintAudience() {
            const ch = channel();
            $$('[data-aud-count]', form).forEach((el) => {
                const k = countKey(el.dataset.audCount);
                const p = Number(phones[k] || 0), m = Number(mails[k] || 0);
                el.textContent = ch === 'sms' ? bn(p) : (ch === 'email' ? bn(m) : bn(p) + ' · ' + bn(m));
                el.title = ch === 'both' ? `মোবাইল ${p} · ইমেইল ${m}` : '';
            });
        }

        function fill(text, forMail) {
            return text.replace(/\{(name|role|phone)\}/g, (all) => (forMail ? (all === '{name}' ? 'সম্মানিত পাঠক' : '') : sample[all]));
        }

        function paint() {
            const ch = channel(), text = msg.value, info = smsParts(text);
            const mailOn = ch !== 'sms', smsOn = ch !== 'email';
            $$('[data-mail-only]').forEach((el) => { el.hidden = !mailOn; });
            $$('[data-sms-only]').forEach((el) => { el.hidden = !smsOn; });
            if (subj) subj.required = mailOn;
            customBox.hidden = group() !== 'custom';

            $('#composeCounter').innerHTML = `${bn(info.len)} অক্ষর${smsOn ? ` · <b>${bn(info.parts)} SMS</b> · ${info.unicode ? 'বাংলা' : 'English'}` : ''}`;
            const bar = $('#composeProgress');
            bar.style.width = (smsOn ? info.pct : 0) + '%';
            bar.className = 'sms-counter-progress' + (info.parts > 2 ? ' danger' : (info.parts > 1 ? ' warning' : ''));
            $('#composePersonal').hidden = !/\{(name|role|phone)\}/.test(text) || !smsOn;

            const a = audience();
            const people = ch === 'sms' ? a.p : (ch === 'email' ? a.m : Math.max(a.p, a.m));
            $('[data-sum="people"]').textContent = ch === 'both' ? `${bn(a.p)} মোবাইল · ${bn(a.m)} ইমেইল` : bn(people) + ' জন';
            $('[data-sum="credits"]').textContent = bn(a.p * Math.max(1, info.parts));
            $('[data-sum="cost"]').textContent = tk(a.p * Math.max(1, info.parts) * cost);
            $('[data-sum="emails"]').textContent = bn(a.m) + ' টি';

            $('#previewSms').textContent = text.trim() ? fill(text, false) : 'বার্তা লিখলে এখানে দেখাবে…';
            $('#previewSubject').textContent = (subj?.value || '').trim() || 'বিষয়';
            $('#previewBody').textContent = text.trim() ? fill(text, true) : 'বার্তা লিখলে এখানে দেখাবে…';
            const btn = $('#previewBtn');
            btn.hidden = !(actText?.value || '').trim();
            btn.textContent = (actText?.value || '').trim();

            const label = { sms: 'SMS পাঠান', email: 'ইমেইল পাঠান', both: 'SMS ও ইমেইল পাঠান' }[ch];
            $('#composeSend span').textContent = people > 0 ? `${label} — ${bn(people)} জন` : label;

            store.set(DRAFT, JSON.stringify({ c: ch, g: group(), m: text, s: subj?.value || '', t: actText?.value || '', u: actUrl?.value || '' }));
        }

        // restore an unsent draft unless the server sent the form back (validation error)
        if (!msg.value.trim()) {
            try {
                const d = JSON.parse(store.get(DRAFT) || 'null');
                if (d && d.m) {
                    msg.value = d.m; if (subj) subj.value = d.s || ''; if (actText) actText.value = d.t || ''; if (actUrl) actUrl.value = d.u || '';
                    const c = $(`#ch-${d.c}`); if (c) c.checked = true;
                    const g = $(`#tg-${d.g}`); if (g && d.g !== 'custom') g.checked = true;
                }
            } catch (e) { /* ignore */ }
        }

        form.addEventListener('input', paint);
        form.addEventListener('change', () => { paintAudience(); paint(); });
        $$('[data-insert]', form).forEach((b) => b.addEventListener('click', () => window.insertShortcode(b.dataset.insert, 'composeMessage')));

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (form.dataset.sending) return;
            const ch = channel(), a = audience(), info = smsParts(msg.value);
            const problems = [];
            if (!msg.value.trim()) problems.push('বার্তা লিখুন।');
            if (ch !== 'sms' && !(subj?.value || '').trim()) problems.push('ইমেইলের বিষয় দিন।');
            if (actUrl?.value && !/^https?:\/\//i.test(actUrl.value)) problems.push('বোতামের লিংক http:// বা https:// দিয়ে শুরু হতে হবে।');
            if ((ch === 'sms' && !a.p) || (ch === 'email' && !a.m) || (ch === 'both' && !a.p && !a.m)) problems.push('এই প্রাপক তালিকায় কেউ নেই।');
            if (problems.length) {
                if (window.Swal) Swal.fire({ icon: 'warning', title: 'আগে ঠিক করুন', html: problems.map(esc).join('<br>') });
                else alert(problems.join('\n'));
                return;
            }
            const lines = [];
            if (ch !== 'email') lines.push(`<b>${bn(a.p)}</b> জনকে SMS — ${bn(a.p * info.parts)} ক্রেডিট (প্রায় ${tk(a.p * info.parts * cost)})`);
            if (ch !== 'sms') lines.push(`<b>${bn(a.m)}</b> জনকে ইমেইল`);
            const go = () => {
                form.dataset.sending = '1';
                const b = $('#composeSend');
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> পাঠানো শুরু হচ্ছে…';
                store.del(DRAFT);
                HTMLFormElement.prototype.submit.call(form);
            };
            if (window.SwalConfirm) {
                SwalConfirm({ title: 'পাঠাবেন?', html: lines.join('<br>') + '<div class="small text-muted mt-2">একবার শুরু হলে Campaigns কার্ড থেকে থামানো যায়।</div>', icon: 'question', confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> হ্যাঁ, পাঠান' })
                    .then((r) => { if (r.isConfirmed) go(); });
            } else if (confirm(lines.join('\n').replace(/<[^>]+>/g, ''))) {
                go();
            }
        });

        // templates → composer
        window.useTemplate = function (id) {
            const t = (window.savedTemplates || []).find((x) => String(x.id) === String(id));
            if (!t) return;
            if (t.channel === 'sms' || t.channel === 'email') { const c = $(`#ch-${t.channel}`); if (c) c.checked = true; }
            msg.value = t.body || '';
            if (subj && t.subject) subj.value = t.subject;
            if (actText) actText.value = t.action_text || '';
            if (actUrl) actUrl.value = t.action_url || '';
            paintAudience(); paint();
            showTab('tab-send-btn');
            setTimeout(() => { msg.focus(); msg.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 200);
        };

        paintAudience();
        paint();
    }
    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-use-template]');
        if (b && window.useTemplate) window.useTemplate(b.dataset.useTemplate);
    });

    /* ---------------- Test: SMS / e-mail / OTP ---------------- */
    (function tests() {
        const out = $('#testResult');
        if (!out) return;
        $$('input[name="test_kind"]').forEach((r) => r.addEventListener('change', () => {
            $$('form[data-test]').forEach((f) => { f.hidden = f.dataset.test !== r.value; });
            out.hidden = true;
        }));
        $$('form[data-test]').forEach((f) => f.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = $('button[type="submit"]', f), html = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> পাঠানো হচ্ছে…';
            out.hidden = true;
            let ok = false, text = '';
            try {
                const res = await fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' });
                const d = await res.json().catch(() => ({}));
                ok = !!d.success;
                if (res.status === 422 && d.errors) text = Object.values(d.errors).flat().join(' ');
                else if (f.dataset.test === 'otp') text = ok ? `OTP ${d.otp || ''} পাঠানো হয়েছে।` : (d.message || 'OTP পাঠানো যায়নি — গেটওয়ে / SMTP সেটিংস দেখুন।');
                else text = d.message || d.error || (ok ? 'পাঠানো হয়েছে।' : 'পাঠানো যায়নি।');
                if (ok && f.dataset.test === 'sms') window.refreshSmsBalance && window.refreshSmsBalance();
            } catch (err) {
                text = 'সার্ভারে পৌঁছানো যায়নি — আবার চেষ্টা করুন।';
            }
            btn.disabled = false;
            btn.innerHTML = html;
            out.className = 'sms-result-box mt-3 ' + (ok ? 'sms-result-success' : 'sms-result-error');
            out.innerHTML = `<strong><i class="fa-solid ${ok ? 'fa-circle-check' : 'fa-triangle-exclamation'} me-1"></i> ${ok ? 'সফল' : 'সমস্যা'}</strong><div class="small mt-1">${esc(text)}</div>`;
            out.hidden = false;
        }));
    })();

    /* ---------------- History: search, channel filter, details ---------------- */
    (function history() {
        const search = $('#campaignLogSearch'), chSel = $('#campaignLogChannel'), body = $('#campaignLogsTableBody');
        if (!body) return;
        const apply = () => {
            const term = (search?.value || '').toLowerCase().trim(), ch = chSel?.value || 'all';
            let shown = 0;
            $$('tr.log-row', body).forEach((row) => {
                const ok = (!term || row.innerText.toLowerCase().includes(term)) && (ch === 'all' || row.dataset.channel === ch);
                row.hidden = !ok;
                if (ok) shown++;
            });
            const none = $('#campaignLogsNoMatch');
            if (none) none.hidden = shown > 0 || !$$('tr.log-row', body).length;
        };
        search?.addEventListener('input', apply);
        chSel?.addEventListener('change', apply);
        body.addEventListener('click', (e) => {
            const b = e.target.closest('[data-log]');
            if (!b) return;
            let log = {};
            try { log = JSON.parse(b.dataset.log); } catch (err) { return; }
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            set('logDetailId', log.id || '');
            set('logDetailDate', log.created_at || '');
            set('logDetailChannel', ({ sms: 'SMS', email: 'ইমেইল' })[log.channel] || 'দুটোই');
            set('logDetailTarget', log.target_group || '');
            set('logDetailStats', `মোট ${log.total || 0} · সফল ${log.sent || 0} · ব্যর্থ ${log.failed || 0}${log.refused ? ' · বাদ ' + log.refused : ''}`);
            set('logDetailAdmin', log.admin_name || 'Admin');
            set('logDetailTitle', log.title || '');
            set('logDetailPreview', log.preview || '');
            const m = $('#logDetailsModal');
            if (m && window.bootstrap) bootstrap.Modal.getOrCreateInstance(m).show();
        });
    })();

    /* ---------------- Templates: add / edit modal ---------------- */
    (function templates() {
        const modal = $('#templateFormModal');
        if (!modal) return;
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
        const open = (t) => {
            set('templateId', t?.id || '');
            set('templateTitle', t?.title || '');
            set('templateChannel', t?.channel || 'both');
            set('templateTag', t?.tag || '');
            set('templateSubject', t?.subject || '');
            set('templateBody', t?.body || '');
            set('templateActionText', t?.action_text || '');
            set('templateActionUrl', t?.action_url || '');
            if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
        };
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-tpl-new]')) open(null);
            const ed = e.target.closest('[data-tpl-edit]');
            if (ed) { try { open(JSON.parse(ed.dataset.tplEdit)); } catch (err) { /* ignore */ } }
        });
        window.openNewTemplateModal = () => open(null);
    })();

    /* ---------------- balance (health banner, Balance tab) ---------------- */
    window.refreshSmsBalance = function (reload) {
        const icons = $$('#refreshIconHealth');
        icons.forEach((i) => i.classList.add('fa-spin'));
        fetch('/admin/sms/balance?fresh=1', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then((r) => r.json())
            .then((d) => {
                if (reload === true) { location.reload(); return; } // the health banner is built on the server
                const ok = d.balance !== null && d.balance !== undefined;
                $$('[data-sms-balance]').forEach((el) => { el.textContent = ok ? Number(d.balance).toLocaleString() : '—'; });
                $$('[data-sms-balance-status]').forEach((el) => {
                    el.innerHTML = ok
                        ? (Number(d.balance) < 100 ? '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> ব্যালান্স কম, শীঘ্র রিচার্জ করুন' : '<i class="fa-solid fa-circle-check text-success me-1"></i> সক্রিয়')
                        : `<i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> ${esc(d.error || 'অপ্রাপ্য')}`;
                });
            })
            .catch(() => { /* the banner keeps its last state */ })
            .finally(() => icons.forEach((i) => i.classList.remove('fa-spin')));
    };

    /* ---------------- running SMS campaigns: live progress, pause / resume / cancel ---------------- */
    (function campaigns() {
        const box = $('#smsCampaigns');
        if (!box) return;
        const list = $('[data-campaign-list]', box);
        let timer = null, busy = false;

        function render(items) {
            box.hidden = !items.length;
            list.innerHTML = items.map((c) => {
                const btns = [];
                if (c.status === 'running') btns.push(`<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0" data-act="pause" data-id="${c.id}"><i class="fa-solid fa-pause me-1"></i>থামান</button>`);
                if (c.status === 'paused') btns.push(`<button type="button" class="btn btn-primary btn-sm rounded-pill py-0" data-act="resume" data-id="${c.id}"><i class="fa-solid fa-play me-1"></i>চালু</button>`);
                if (c.status === 'running' || c.status === 'paused') btns.push(`<button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-0" data-act="cancel" data-id="${c.id}">বাতিল</button>`);
                const left = Math.max(0, c.total - c.position);
                const note = c.last_error
                    ? `<div class="sms-camp-note"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(c.last_error)}</div>`
                    : (c.stalled ? '<div class="sms-camp-note">কিছুক্ষণ থেমে আছে — এই পেজ খোলা থাকলে নিজে থেকেই আবার চলবে।</div>' : '');
                return `<div class="sms-camp">
                    <div class="sms-camp-top">
                        <span class="sms-camp-title">#${c.id} · ${esc(c.title)}</span>
                        <span class="sms-camp-badge ${esc(c.status)}">${esc(c.status_bn)}</span>
                        <small class="text-muted">${esc(c.started_at || '')}${c.segments > 1 ? ' · প্রতি মেসেজ ' + bn(c.segments) + ' SMS' : ''}</small>
                        <span class="sms-camp-actions">${btns.join('')}</span>
                    </div>
                    <div class="sms-camp-bar" role="progressbar" aria-valuenow="${c.percent}" aria-valuemin="0" aria-valuemax="100"><span style="width:${c.percent}%"></span></div>
                    <div class="sms-camp-nums">
                        <span>${bn(c.percent)}% · ${bn(c.position)}/${bn(c.total)}</span>
                        <span>সফল <b>${bn(c.sent)}</b></span>
                        <span>ব্যর্থ <b>${bn(c.failed)}</b></span>
                        <span>ভুয়া/অবৈধ বাদ <b>${bn(c.refused)}</b></span>
                        ${c.unknown ? `<span>অনিশ্চিত <b>${bn(c.unknown)}</b></span>` : ''}
                        ${left && c.status !== 'done' ? `<span>বাকি <b>${bn(left)}</b></span>` : ''}
                    </div>${note}
                </div>`;
            }).join('');
            clearTimeout(timer);
            // while a campaign runs, every poll also sends the next slice: an open page keeps it moving
            if (items.some((c) => c.status === 'running')) timer = setTimeout(() => tick(true), 3000);
            else if (items.some((c) => c.status === 'paused')) timer = setTimeout(() => tick(false), 30000);
        }

        async function post(url, body) {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body || {}), credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }

        async function tick(work) {
            if (busy) return;
            busy = true;
            try {
                const data = await post(box.dataset.tickUrl, { work: work ? 1 : 0 });
                render(data.campaigns || []);
            } catch (e) {
                clearTimeout(timer);
                timer = setTimeout(() => tick(work), 10000);
            } finally {
                busy = false;
            }
        }

        list.addEventListener('click', async (e) => {
            const b = e.target.closest('[data-act]');
            if (!b) return;
            if (b.dataset.act === 'cancel') {
                const ok = window.SwalConfirm
                    ? (await SwalConfirm({ title: 'ক্যাম্পেইন বাতিল?', text: 'যাদের কাছে এখনো যায়নি তারা আর পাবেন না।', icon: 'error' })).isConfirmed
                    : confirm('ক্যাম্পেইন বাতিল করবেন?');
                if (!ok) return;
            }
            b.disabled = true;
            try { await post(box.dataset.actionUrl.replace('__ID__', b.dataset.id), { action: b.dataset.act }); } catch (err) { alert('কাজটি করা যায়নি, আবার চেষ্টা করুন।'); }
            busy = false;
            tick(b.dataset.act === 'resume');
        });

        const initial = window.smsCampaigns || [];
        render(initial);
        if (initial.some((c) => c.status === 'running')) tick(true);
    })();

    /* ---------------- Settings: check the key, show / hide it; health banner jumps here ---------------- */
    (function settings() {
        const verify = $('#smsVerifyKey'), out = $('#smsVerifyOut'), input = $('#smsApiKeyInput');
        if (verify && out) {
            verify.addEventListener('click', () => {
                verify.disabled = true;
                out.className = 'sms-verify-out small mt-2 text-muted';
                out.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> গেটওয়েতে যাচাই হচ্ছে…';
                const body = new FormData();
                body.append('api_key', input ? input.value.trim() : '');
                fetch(verify.dataset.url, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then((r) => r.json())
                    .then((d) => {
                        out.className = 'sms-verify-out small mt-2 ' + (d.ok ? 'is-ok' : (d.ok === false ? 'is-bad' : 'is-warn'));
                        out.innerHTML = '<i class="fa-solid ' + (d.ok ? 'fa-circle-check' : 'fa-circle-xmark') + ' me-1"></i>' + esc(d.message || '') + (input && !input.value.trim() ? ' <span class="text-muted">(সংরক্ষিত key)</span>' : '');
                    })
                    .catch(() => { out.className = 'sms-verify-out small mt-2 is-warn'; out.textContent = 'যাচাই করা যায়নি — আবার চেষ্টা করুন।'; })
                    .finally(() => { verify.disabled = false; });
            });
        }
        $$('[data-sms-toggle-key]').forEach((btn) => btn.addEventListener('click', () => {
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.innerHTML = input.type === 'password' ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
        }));
        $$('[data-sms-goto-settings]').forEach((btn) => btn.addEventListener('click', () => {
            showTab('tab-settings-btn');
            setTimeout(() => { if (input) { input.scrollIntoView({ behavior: 'smooth', block: 'center' }); input.focus(); } }, 250);
        }));
    })();
})();
