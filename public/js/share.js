/**
 * Share sheet for posts, e-books, webzines, authors and site pages (resources/views/components/share.blade.php).
 * One <dialog id="shSheet"> per page, filled from the button that opened it ([data-share] JSON).
 * Channels: device share (with the picture where the browser allows files), WhatsApp, Facebook, Messenger,
 * Telegram, X, LinkedIn, e-mail, SMS, QR code and copy link. Links carry UTM tags; every share is counted.
 * Also exposes window.ideaShare(data) for pages that open the sheet from their own buttons.
 */
(function () {
    'use strict';

    const QR_LIB = 'https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js';
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const bnNum = n => Number(n).toLocaleString('bn-BD');
    const isMobile = () => /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent) || (navigator.maxTouchPoints > 1 && innerWidth < 900);

    let sheet = null;
    let current = null;      // { data, source } of the item in the sheet

    function toast(message, isError) {
        let box = document.getElementById('shToast');
        if (!box) {
            box = document.createElement('div');
            box.id = 'shToast';
            box.className = 'sh-toast';
            box.setAttribute('role', 'status');
            box.setAttribute('aria-live', 'polite');
            document.body.appendChild(box);
        }
        box.textContent = message;
        box.classList.toggle('is-error', !!isError);
        box.classList.add('is-on');
        clearTimeout(box._t);
        box._t = setTimeout(() => box.classList.remove('is-on'), 2600);
    }

    function readData(el) {
        try { return JSON.parse(el.dataset.share || '{}'); } catch (e) { return {}; }
    }

    function tagged(url, channel, type) {
        try {
            const u = new URL(url, location.href);
            u.searchParams.set('utm_source', 'share');
            u.searchParams.set('utm_medium', channel);
            u.searchParams.set('utm_campaign', (type || 'site') + '_share');
            return u.toString();
        } catch (e) { return url; }
    }

    function message() {
        const box = sheet && sheet.querySelector('[data-sh="message"]');
        return ((box && sheet.open ? box.value : '') || current?.data.text || current?.data.title || '').trim();
    }

    function linkFor(channel, d) {
        const url = tagged(d.url, channel, d.type);
        const msg = message();
        const e = encodeURIComponent;
        switch (channel) {
            case 'whatsapp': return 'https://wa.me/?text=' + e(msg + ' ' + url);
            case 'facebook': return 'https://www.facebook.com/sharer/sharer.php?u=' + e(url) + '&hashtag=' + e('#IdeaProkashon');
            case 'messenger':
                if (isMobile()) return 'fb-messenger://share/?link=' + e(url);
                return d.fbApp ? 'https://www.facebook.com/dialog/send?app_id=' + e(d.fbApp) + '&link=' + e(url) + '&redirect_uri=' + e(location.origin + '/') : '';
            case 'telegram': return 'https://t.me/share/url?url=' + e(url) + '&text=' + e(msg);
            case 'x':        return 'https://twitter.com/intent/tweet?text=' + e(msg) + '&url=' + e(url);
            case 'linkedin': return 'https://www.linkedin.com/sharing/share-offsite/?url=' + e(url);
            case 'email':    return 'mailto:?subject=' + e(d.title || '') + '&body=' + e(msg + '\n\n' + url);
            case 'sms':      return 'sms:?&body=' + e(msg + ' ' + url);
            default: return '';
        }
    }

    /** Counts the share and refreshes every counter of the same item on the page. */
    async function track(d, channel) {
        if (!d.endpoint) return;
        try {
            const res = await fetch(d.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ channel }),
                credentials: 'same-origin',
                keepalive: true,
            });
            const body = await res.json().catch(() => null);
            const n = Number(body && body.shares);
            if (!res.ok || !Number.isFinite(n) || n < 1) return;
            document.querySelectorAll('[data-share]').forEach(el => {
                const o = readData(el);
                if (o.type !== d.type || String(o.key) !== String(d.key)) return;
                el.querySelectorAll('[data-share-count]').forEach(c => {
                    c.textContent = c.hasAttribute('data-short') ? bnNum(n) : bnNum(n) + ' বার শেয়ার হয়েছে';
                    c.hidden = false;
                });
            });
        } catch (e) { /* counting is best effort */ }
    }

    function openFacebook(href) {
        if (isMobile()) { window.open(href, '_blank', 'noopener,noreferrer'); return; }
        const w = 620, h = 700;
        const left = Math.max(0, Math.round((screen.availWidth - w) / 2));
        const top = Math.max(0, Math.round((screen.availHeight - h) / 3));
        const win = window.open(href, 'sh_fb', 'popup=yes,width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',noopener,noreferrer');
        if (!win) window.open(href, '_blank', 'noopener,noreferrer');
    }

    async function copyText(text) {
        try { await navigator.clipboard.writeText(text); return true; } catch (e) { /* fall back */ }
        const t = document.createElement('textarea');
        t.value = text; t.setAttribute('readonly', ''); t.style.cssText = 'position:fixed;left:-9999px;top:0';
        document.body.appendChild(t); t.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        t.remove();
        return ok;
    }

    function say(text, isError) {
        const s = sheet && sheet.querySelector('[data-sh="status"]');
        if (!s) return;
        s.textContent = text;
        s.classList.toggle('is-error', !!isError);
    }

    async function nativeShare(d) {
        const data = { title: d.title, text: message(), url: tagged(d.url, 'native', d.type) };
        try {
            if (navigator.canShare && d.image) {
                const blob = await fetch(d.image, { mode: 'cors' }).then(r => r.ok ? r.blob() : null).catch(() => null);
                if (blob && blob.type.startsWith('image/')) {
                    const file = new File([blob], 'share.' + (blob.type.split('/')[1] || 'jpg'), { type: blob.type });
                    if (navigator.canShare({ files: [file] })) data.files = [file];
                }
            }
            await navigator.share(data);
            track(d, 'native');
            return true;
        } catch (e) {
            if (e && e.name === 'AbortError') return true;
            return false;
        }
    }

    async function showQr(d) {
        const box = sheet.querySelector('[data-sh="qr-box"]');
        const target = sheet.querySelector('[data-sh="qr"]');
        box.hidden = false;
        if (!window.qrcode) {
            await new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = QR_LIB; s.async = true; s.onload = resolve; s.onerror = reject;
                document.head.appendChild(s);
            }).catch(() => null);
        }
        if (!window.qrcode) { box.hidden = true; say('QR কোড এখন তৈরি করা যাচ্ছে না।', true); return; }
        const qr = window.qrcode(0, 'M');
        qr.addData(tagged(d.url, 'qr', d.type));
        qr.make();
        target.innerHTML = qr.createSvgTag({ cellSize: 6, margin: 2, scalable: true });
        track(d, 'qr');
    }

    function downloadQr() {
        const svg = sheet.querySelector('[data-sh="qr"] svg');
        if (!svg) return;
        const img = new Image();
        img.onload = () => {
            const c = document.createElement('canvas');
            c.width = c.height = 600;
            const g = c.getContext('2d');
            g.fillStyle = '#fff'; g.fillRect(0, 0, 600, 600); g.drawImage(img, 0, 0, 600, 600);
            const a = document.createElement('a');
            a.download = (current?.data.type || 'share') + '-qr.png'; a.href = c.toDataURL('image/png'); a.click();
        };
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(new XMLSerializer().serializeToString(svg));
    }

    /** Runs one channel for an item, from the sheet or straight from a bar button. */
    async function run(channel, d) {
        if (channel === 'copy') {
            const ok = await copyText(tagged(d.url, 'copy', d.type));
            if (ok) { say('লিংক কপি হয়েছে'); toast('লিংক কপি হয়েছে'); track(d, 'copy'); }
            else { say('কপি করা যায়নি, লিংকটি হাতে কপি করুন।', true); toast('কপি করা যায়নি', true); }
            return;
        }
        if (channel === 'native') { if (!(await nativeShare(d))) say('ডিভাইস থেকে শেয়ার করা যায়নি, অন্য উপায় বেছে নিন।', true); return; }
        if (channel === 'qr') { showQr(d); return; }

        const href = linkFor(channel, d);
        if (!href) return;
        if (channel === 'facebook') {
            openFacebook(href);
            // Facebook does not take pre-filled text: keep the message on the clipboard, ready to paste
            copyText(message() + '\n' + tagged(d.url, 'facebook', d.type)).then(ok => {
                if (ok) { say('বার্তা কপি হয়েছে, Facebook-এর পোস্টে পেস্ট করুন'); toast('বার্তা কপি হয়েছে, পোস্টে পেস্ট করুন'); }
            });
        } else if (channel === 'email' || channel === 'sms' || href.startsWith('fb-messenger:')) {
            location.href = href;
        } else {
            window.open(href, '_blank', 'noopener,noreferrer');
        }
        track(d, channel);
    }

    function fill(d) {
        const q = sel => sheet.querySelector(sel);
        q('[data-sh="heading"]').textContent = d.heading || 'শেয়ার করুন';
        q('[data-sh="title"]').textContent = d.title || document.title;
        const sub = q('[data-sh="subtitle"]');
        sub.textContent = d.subtitle || ''; sub.hidden = !d.subtitle;
        try { q('[data-sh="host"]').textContent = new URL(d.url, location.href).host.replace(/^www\./, ''); } catch (e) { /* bad url */ }
        const img = q('[data-sh="image"]');
        if (d.image) { img.src = d.image; img.hidden = false; img.onerror = () => { img.hidden = true; }; } else { img.hidden = true; img.removeAttribute('src'); }
        q('[data-sh="message"]').value = d.text || d.title || '';
        q('[data-sh="url"]').value = tagged(d.url, 'copy', d.type);
        q('[data-sh="qr-box"]').hidden = true;
        q('[data-sh="qr"]').innerHTML = '';
        q('[data-channel="native"]').hidden = !navigator.share;
        q('[data-channel="messenger"]').hidden = !(isMobile() || d.fbApp);
        say('');
    }

    function open(d, source) {
        d.url = d.url || location.href;
        current = { data: d, source };
        if (!sheet) { run('copy', d); return; }        // page without the sheet markup: copy the link
        fill(d);
        if (typeof sheet.showModal === 'function') { if (!sheet.open) sheet.showModal(); } else { sheet.setAttribute('open', ''); }
    }

    document.addEventListener('DOMContentLoaded', () => {
        sheet = document.getElementById('shSheet');
        const fbApp = document.querySelector('meta[property="fb:app_id"]')?.content || '';

        document.addEventListener('click', ev => {
            const host = ev.target.closest('[data-share]');
            if (!host) return;
            const d = Object.assign({ fbApp }, readData(host));
            if (ev.target.closest('[data-share-open]')) { ev.preventDefault(); open(d, host); return; }
            const quick = ev.target.closest('[data-share-channel]');
            if (quick) { ev.preventDefault(); current = { data: d, source: host }; run(quick.dataset.shareChannel, d); }
        });

        if (!sheet) return;
        sheet.addEventListener('click', ev => {
            if (ev.target === sheet) { sheet.close(); return; }       // backdrop
            const chip = ev.target.closest('[data-channel]');
            if (chip && current) run(chip.dataset.channel, current.data);
            if (ev.target.closest('[data-sh="qr-download"]')) downloadQr();
        });
    });

    /** For custom buttons: ideaShare({type, key, url, title, text, image, endpoint}) */
    window.ideaShare = d => open(Object.assign({}, d || {}), null);
})();
