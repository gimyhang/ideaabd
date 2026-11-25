/**
 * Admin › Visitors: charts and lists from one JSON report (period switch without reload),
 * a live feed that refreshes itself, and IP details with block / unblock.
 */
(function () {
    'use strict';
    const root = document.getElementById('vrRoot');
    if (!root) return;

    const $ = (s, r) => (r || document).querySelector(s);
    const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
    const bn = (v) => Number(v || 0).toLocaleString('bn-BD');
    const esc = (v) => { const d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; };
    const csrf = () => $('meta[name="csrf-token"]')?.content || '';
    const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || getComputedStyle(root).getPropertyValue(name).trim();
    const DEVICE_BN = { mobile: 'মোবাইল', desktop: 'ডেস্কটপ', tablet: 'ট্যাবলেট', bot: 'বট' };
    const DEVICE_IC = { mobile: 'fa-mobile-screen', desktop: 'fa-desktop', tablet: 'fa-tablet-screen-button', bot: 'fa-robot' };
    let data = window.vrInitial || {};
    let trendChart = null, deviceChart = null, tech = 'browsers';

    /* ---------- bars & lists ---------- */
    function bars(el, items, opts) {
        if (!el) return;
        const o = opts || {};
        if (!items || !items.length) { el.innerHTML = '<div class="vr-empty">এই সময়ে তথ্য নেই।</div>'; return; }
        const max = Math.max(...items.map((i) => i.count), 1), total = items.reduce((a, i) => a + i.count, 0) || 1;
        el.innerHTML = items.map((i) => `
            <div class="vr-bar">
                <div class="vr-bar__top"><span class="vr-bar__lb">${o.icon ? o.icon(i) : ''}${esc(o.label ? o.label(i) : i.label)}</span><span class="vr-bar__n">${bn(i.count)} <small>${Math.round(i.count / total * 100)}%</small></span></div>
                <div class="vr-bar__track"><span style="width:${(i.count / max * 100).toFixed(1)}%"></span></div>
            </div>`).join('');
    }

    function kpis() {
        const k = data.kpi || {}, p = data.prev || {};
        const fmt = { views: (v) => bn(v), uniques: (v) => bn(v), ppv: (v) => bn(v), mobile: (v) => bn(v) + '%' };
        $$('[data-kpi]').forEach((card) => {
            const key = card.dataset.kpi, now = Number(k[key] || 0), before = Number(p[key] || 0);
            $('.vr-kpi__v', card).textContent = fmt[key](now);
            const d = $('.vr-kpi__d', card);
            if (!before && !now) { d.innerHTML = ''; return; }
            if (!before) { d.innerHTML = '<span class="is-up"><i class="fa-solid fa-arrow-up"></i> নতুন</span> আগের সময়ের তুলনায়'; return; }
            const pct = Math.round((now - before) / before * 100);
            d.innerHTML = `<span class="${pct >= 0 ? 'is-up' : 'is-down'}"><i class="fa-solid fa-arrow-${pct >= 0 ? 'up' : 'down'}"></i> ${bn(Math.abs(pct))}%</span> আগের সময়ের তুলনায়`;
        });
    }

    function trend() {
        const c = $('#vrTrend');
        if (!c || !window.Chart) return;
        const t = data.trend || { labels: [], views: [], uniques: [] };
        const a = css('--vr-a') || '#2563eb', b = css('--vr-b') || '#10b981';
        const g = c.getContext('2d').createLinearGradient(0, 0, 0, 280);
        g.addColorStop(0, a + '55'); g.addColorStop(1, a + '00');
        const ds = [
            { label: 'ভিউ', data: t.views, borderColor: a, backgroundColor: g, fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: t.labels.length > 31 ? 0 : 3, pointHoverRadius: 5 },
            { label: 'ইউনিক', data: t.uniques, borderColor: b, backgroundColor: 'transparent', tension: 0.35, borderWidth: 2, borderDash: [5, 4], pointRadius: 0, pointHoverRadius: 4 },
        ];
        if (trendChart) { trendChart.data.labels = t.labels; trendChart.data.datasets[0].data = t.views; trendChart.data.datasets[1].data = t.uniques; trendChart.data.datasets[0].pointRadius = ds[0].pointRadius; trendChart.update(); return; }
        trendChart = new Chart(c, {
            type: 'line', data: { labels: t.labels, datasets: ds },
            options: {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8, callbacks: { label: (x) => ` ${x.dataset.label}: ${bn(x.parsed.y)}` } } },
                scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 10, color: '#94a3b8' } }, y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.18)' }, ticks: { precision: 0, color: '#94a3b8' } } },
            },
        });
    }

    function devices() {
        const items = data.devices || [];
        const total = items.reduce((a, i) => a + i.count, 0) || 1;
        const mob = items.find((i) => i.label === 'mobile');
        $('#vrMobilePct').textContent = bn(Math.round((mob ? mob.count : 0) / total * 100)) + '%';
        bars($('#vrDeviceList'), items, { label: (i) => DEVICE_BN[i.label] || i.label, icon: (i) => `<i class="fa-solid ${DEVICE_IC[i.label] || 'fa-circle'} me-1"></i>` });
        const c = $('#vrDevices');
        if (!c || !window.Chart) return;
        const colors = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'];
        const cfg = { labels: items.map((i) => DEVICE_BN[i.label] || i.label), datasets: [{ data: items.map((i) => i.count), backgroundColor: colors, borderWidth: 0, hoverOffset: 6 }] };
        if (deviceChart) { deviceChart.data = cfg; deviceChart.update(); return; }
        deviceChart = new Chart(c, { type: 'doughnut', data: cfg, options: { cutout: '72%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (x) => ` ${x.label}: ${bn(x.parsed)}` } } } } });
    }

    function heat() {
        const el = $('#vrHeat');
        const h = data.heat || [];
        const max = Math.max(1, ...h.flat());
        const days = ['শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ', 'শুক্র'];
        let html = '<div class="vr-heat__grid"><span></span>';
        for (let x = 0; x < 24; x++) html += `<span class="vr-heat__h">${x % 3 === 0 ? (x === 0 ? '১২a' : (x < 12 ? bn(x) + 'a' : (x === 12 ? '১২p' : bn(x - 12) + 'p'))) : ''}</span>`;
        h.forEach((row, d) => {
            html += `<span class="vr-heat__d">${days[d]}</span>`;
            row.forEach((v, x) => {
                const lvl = v === 0 ? 0 : Math.min(5, Math.ceil(v / max * 5));
                html += `<span class="vr-heat__c l${lvl}" title="${days[d]}, ${x}:00 — ${v} ভিউ"></span>`;
            });
        });
        el.innerHTML = html + '</div><div class="vr-heat__legend">কম <span class="vr-heat__c l1"></span><span class="vr-heat__c l2"></span><span class="vr-heat__c l3"></span><span class="vr-heat__c l4"></span><span class="vr-heat__c l5"></span> বেশি</div>';
    }

    function pages() {
        const rows = data.pages || [];
        const max = Math.max(1, ...rows.map((r) => r.count));
        $('#vrPages').innerHTML = rows.length ? rows.map((r) => `
            <tr>
                <td class="vr-page"><a href="${esc(r.url)}" target="_blank" rel="noopener">${esc(r.title || r.path)}</a><div class="small text-muted text-truncate">${esc(r.path)}</div></td>
                <td class="text-end fw-semibold">${bn(r.count)}</td>
                <td class="text-end text-muted">${bn(r.uniques)}</td>
                <td><div class="vr-bar__track"><span style="width:${(r.count / max * 100).toFixed(1)}%"></span></div></td>
            </tr>`).join('') : '<tr><td colspan="4" class="vr-empty">এই সময়ে তথ্য নেই।</td></tr>';
        const books = data.books || [];
        $('#vrBooks').innerHTML = books.length ? books.map((b) => `<li><a href="${esc(b.url)}" target="_blank" rel="noopener">${esc(b.title)}</a><span>${bn(b.count)}</span></li>`).join('') : '<li class="vr-empty">এই সময়ে তথ্য নেই।</li>';
        $('#vrBuilt').textContent = data.built_at ? 'হালনাগাদ ' + data.built_at : '';
    }

    function render() {
        kpis(); trend(); devices(); heat(); pages();
        bars($('#vrChannels'), data.channels);
        bars($('#vrCountries'), data.countries, { icon: (i) => `<span class="me-1">${esc(i.flag)}</span>` });
        bars($('#vrTech'), data[tech]);
    }

    /* ---------- period switch without reload ---------- */
    $$('[data-period]').forEach((a) => a.addEventListener('click', async (e) => {
        e.preventDefault();
        $$('[data-period]').forEach((x) => { x.classList.toggle('is-on', x === a); x.setAttribute('aria-selected', x === a ? 'true' : 'false'); });
        root.classList.add('is-loading');
        try {
            const r = await fetch(root.dataset.dataUrl + '?period=' + encodeURIComponent(a.dataset.period), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!r.ok) throw new Error(r.status);
            data = await r.json();
            render();
            history.replaceState(null, '', '?period=' + a.dataset.period + location.hash);
        } catch (err) {
            location.href = a.href;
        } finally {
            root.classList.remove('is-loading');
        }
    }));
    $$('#vrTechSeg [data-tech]').forEach((b) => b.addEventListener('click', () => {
        tech = b.dataset.tech;
        $$('#vrTechSeg [data-tech]').forEach((x) => x.classList.toggle('is-on', x === b));
        bars($('#vrTech'), data[tech]);
    }));

    /* ---------- live feed ---------- */
    const feed = $('#vrFeed'), search = $('#vrFeedSearch');
    let lastId = 0, timer = null, searching = '';

    function rowHtml(r, fresh) {
        const who = r.user ? `<span class="vr-member"><i class="fa-solid fa-user-check"></i> ${esc(r.user)}</span>` : '';
        const bot = r.bot === 'bad' ? '<span class="vr-tag vr-tag--bot">বট</span>' : (r.bot === 'good' ? '<span class="vr-tag vr-tag--good">সার্চ ইঞ্জিন</span>' : '');
        const blk = r.blocked ? '<span class="vr-tag vr-tag--blocked">ব্লক</span>' : '';
        return `<tr class="${fresh ? 'is-new' : ''}" data-id="${r.id}">
            <td class="text-nowrap" data-l="সময়"><span title="${esc(r.time)}">${esc(r.ago)}</span></td>
            <td data-l="ভিজিটর"><button type="button" class="vr-ip" data-ip="${esc(r.ip)}">${esc(r.flag)} ${esc(r.ip)}</button> ${bot}${blk}<div class="small text-muted">${esc(r.place)} ${who}</div></td>
            <td class="vr-page" data-l="পেজ"><a href="${esc(r.url)}" target="_blank" rel="noopener">${esc(r.title || r.path)}</a><div class="small text-muted text-truncate">${esc(r.path)}</div></td>
            <td data-l="ডিভাইস"><i class="fa-solid ${DEVICE_IC[r.device] || 'fa-desktop'} me-1 text-muted"></i>${esc(r.client)}</td>
            <td data-l="উৎস"><span class="vr-src">${esc(r.source)}</span></td>
        </tr>`;
    }

    async function poll() {
        clearTimeout(timer);
        try {
            const q = searching ? 'q=' + encodeURIComponent(searching) : 'after=' + lastId;
            const r = await fetch(root.dataset.liveUrl + '?' + q, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const d = await r.json();
            $('#vrLiveCount').textContent = bn(d.live);
            if (searching) {
                feed.innerHTML = d.rows.length ? d.rows.map((x) => rowHtml(x, false)).join('') : '<tr><td colspan="5" class="text-center text-muted py-4">মিল পাওয়া যায়নি।</td></tr>';
            } else if (d.rows.length) {
                const first = !lastId;
                if (first) feed.innerHTML = '';
                feed.insertAdjacentHTML('afterbegin', d.rows.map((x) => rowHtml(x, !first)).join(''));
                lastId = Math.max(lastId, ...d.rows.map((x) => x.id));
                $$('tr', feed).slice(60).forEach((tr) => tr.remove());
            } else if (!lastId) {
                feed.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">এখনো কোনো ভিজিট নেই।</td></tr>';
            }
        } catch (e) { /* try again next round */ }
        if (!searching && !document.hidden) timer = setTimeout(poll, 15000);
    }
    let deb = null;
    search?.addEventListener('input', () => {
        clearTimeout(deb);
        deb = setTimeout(() => {
            searching = search.value.trim();
            $('#vrAuto').classList.toggle('is-off', !!searching);
            if (!searching) { lastId = 0; }
            poll();
        }, 350);
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden && !searching) poll(); });
    poll();

    /* ---------- IP details: visits, block / unblock ---------- */
    const modalEl = $('#vrIpModal');
    document.addEventListener('click', async (e) => {
        const b = e.target.closest('.vr-ip[data-ip]');
        if (!b || !modalEl) return;
        const ip = b.dataset.ip;
        $('#vrIpTitle').textContent = ip;
        $('#vrIpBody').innerHTML = '<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>লোড হচ্ছে…</div>';
        $('#vrIpFoot').innerHTML = '';
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        try {
            const r = await fetch(root.dataset.ipUrl + '?ip=' + encodeURIComponent(ip), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const d = await r.json();
            const kind = { bad: '<span class="vr-tag vr-tag--bot">বট / স্ক্রিপ্ট</span>', good: '<span class="vr-tag vr-tag--good">সার্চ ইঞ্জিন</span>', human: '<span class="vr-tag">মানুষ (ব্রাউজার)</span>' }[d.kind] || '';
            $('#vrIpBody').innerHTML = `
                <div class="vr-ipstats">
                    <div><span>মোট ভিউ</span><b>${bn(d.total)}</b></div>
                    <div><span>২৪ ঘণ্টায়</span><b>${bn(d.day)}</b></div>
                    <div><span>প্রথম দেখা</span><b>${esc(d.first || '—')}</b></div>
                    <div><span>জায়গা</span><b>${esc(d.place || '—')}</b></div>
                </div>
                <div class="small mb-2">${kind} ${d.blocked ? `<span class="vr-tag vr-tag--blocked">ব্লক${d.blocked.until ? ' · ' + esc(d.blocked.until) + ' পর্যন্ত' : ' · স্থায়ী'}</span>` : ''} ${d.mine ? '<span class="vr-tag vr-tag--good">আপনার IP</span>' : ''}</div>
                <div class="small text-muted font-monospace mb-3 vr-ua">${esc(d.agent)}</div>
                <ol class="vr-visits">${d.visits.map((v) => `<li><span>${esc(v.time)}</span><b>${esc(v.title || v.path)}</b><small>${esc(v.path)}</small></li>`).join('')}</ol>`;
            const foot = $('#vrIpFoot');
            if (d.mine) {
                foot.innerHTML = '<span class="small text-muted me-auto">নিজের IP ব্লক করা যায় না।</span>';
            } else if (d.blocked) {
                foot.innerHTML = `<button type="button" class="btn btn-outline-success rounded-pill px-3" data-unblock="${esc(ip)}"><i class="fa-solid fa-lock-open me-1"></i> আনব্লক</button>`;
            } else {
                foot.innerHTML = `<select class="form-select form-select-sm w-auto" id="vrIpDur"><option value="60">১ ঘণ্টা</option><option value="1440" selected>২৪ ঘণ্টা</option><option value="10080">৭ দিন</option><option value="forever">স্থায়ী</option></select>
                    <button type="button" class="btn btn-danger rounded-pill px-3" data-block="${esc(ip)}"><i class="fa-solid fa-ban me-1"></i> ব্লক</button>`;
            }
        } catch (err) {
            $('#vrIpBody').innerHTML = '<div class="alert alert-danger">তথ্য আনা যায়নি।</div>';
        }
    });

    async function act(url, body) {
        const r = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body), credentials: 'same-origin' });
        const d = await r.json().catch(() => ({}));
        if (window.SwalToast) SwalToast(r.ok && d.ok ? 'success' : 'error', d.message || 'কাজটি হয়নি');
        if (r.ok && d.ok) setTimeout(() => location.reload(), 700);
    }
    modalEl?.addEventListener('click', async (e) => {
        const bl = e.target.closest('[data-block]'), ub = e.target.closest('[data-unblock]');
        if (bl) {
            const ok = window.SwalConfirm ? (await SwalConfirm({ title: bl.dataset.block + ' ব্লক?', text: 'এই IP থেকে সাইট খোলা যাবে না।', icon: 'error' })).isConfirmed : confirm('ব্লক করবেন?');
            if (ok) act(root.dataset.blockUrl, { ip: bl.dataset.block, duration: $('#vrIpDur').value, reason: 'IP বিস্তারিত থেকে অ্যাডমিন ব্লক করেছেন' });
        }
        if (ub) act(root.dataset.unblockUrl, { ip: ub.dataset.unblock });
    });

    render();
})();
