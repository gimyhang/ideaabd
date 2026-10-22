/**
 * Book page: share sheet + wishlist. No framework, no icon font.
 *  - Share: device share sheet (with the cover image when the browser allows files), WhatsApp, Facebook, Telegram,
 *    X, LinkedIn, e-mail, SMS, copy link and a QR code. Every link carries UTM tags and every share is counted.
 *  - Wishlist: saved to the account when signed in, to this browser for guests (and moved into the account on login).
 */
(function () {
    'use strict';

    const QR_LIB = 'https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js';
    const LS_KEY = 'idea_wishlist_v1';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const $ = (root, sel) => root.querySelector(sel);

    const store = {
        read() { try { const v = JSON.parse(localStorage.getItem(LS_KEY) || '[]'); return Array.isArray(v) ? v.map(Number).filter(Boolean) : []; } catch (e) { return []; } },
        write(ids) { try { localStorage.setItem(LS_KEY, JSON.stringify([...new Set(ids)].slice(0, 100))); } catch (e) { /* private mode */ } },
    };

    function toast(message, isError) {
        let box = document.getElementById('baToast');
        if (!box) {
            box = document.createElement('div');
            box.id = 'baToast';
            box.setAttribute('role', 'status');
            box.setAttribute('aria-live', 'polite');
            box.style.cssText = 'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:2000;max-width:92vw;padding:.65rem 1.1rem;border-radius:999px;background:#0f172a;color:#fff;font-weight:600;font-size:.9rem;box-shadow:0 10px 30px rgba(0,0,0,.3);transition:opacity .2s;opacity:0;pointer-events:none';
            document.body.appendChild(box);
        }
        box.textContent = message;
        box.style.background = isError ? '#b91c1c' : '#0f172a';
        box.style.opacity = '1';
        clearTimeout(box._t);
        box._t = setTimeout(() => { box.style.opacity = '0'; }, 2600);
    }

    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body || {}),
            credentials: 'same-origin',
            keepalive: true,
        });
        let data = null;
        try { data = await res.json(); } catch (e) { /* empty body */ }
        return { ok: res.ok, status: res.status, data };
    }

    /* ------------------------------------------------------------------ share */

    function tagged(url, channel) {
        try {
            const u = new URL(url, location.href);
            u.searchParams.set('utm_source', 'share');
            u.searchParams.set('utm_medium', channel);
            u.searchParams.set('utm_campaign', 'book_share');
            return u.toString();
        } catch (e) { return url; }
    }

    const isMobile = () => /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent) || (navigator.maxTouchPoints > 1 && innerWidth < 900);

    /** Opens Facebook's share dialog as a centred popup on desktop, a normal tab on phones. */
    function openFacebook(href) {
        if (isMobile()) { window.open(href, '_blank', 'noopener,noreferrer'); return; }
        const w = 620, h = 700;
        const left = Math.max(0, Math.round((screen.availWidth - w) / 2));
        const top = Math.max(0, Math.round((screen.availHeight - h) / 3));
        const win = window.open(href, 'ba_fb_share', 'popup=yes,width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',noopener,noreferrer');
        if (!win) window.open(href, '_blank', 'noopener,noreferrer');      // popup blocked
    }

    function linkFor(channel, ctx) {
        const url = tagged(ctx.url, channel);
        const msg = ctx.message();
        const e = encodeURIComponent;
        switch (channel) {
            case 'whatsapp': return 'https://wa.me/?text=' + e(msg + ' ' + url);
            // Facebook ignores pre-filled text; the message is copied to the clipboard instead (see shareToFacebook)
            case 'facebook': return 'https://www.facebook.com/sharer/sharer.php?u=' + e(url) + '&hashtag=' + e('#IdeaProkashon');
            case 'messenger': {
                if (isMobile()) return 'fb-messenger://share/?link=' + e(url);
                return ctx.fbApp ? 'https://www.facebook.com/dialog/send?app_id=' + e(ctx.fbApp) + '&link=' + e(url) + '&redirect_uri=' + e(location.origin + '/') : '';
            }
            case 'telegram': return 'https://t.me/share/url?url=' + e(url) + '&text=' + e(msg);
            case 'x':        return 'https://twitter.com/intent/tweet?text=' + e(msg) + '&url=' + e(url);
            case 'linkedin': return 'https://www.linkedin.com/sharing/share-offsite/?url=' + e(url);
            case 'email':    return 'mailto:?subject=' + e(ctx.title) + '&body=' + e(msg + '\n\n' + url);
            case 'sms':      return 'sms:?&body=' + e(msg + ' ' + url);
            default: return '';
        }
    }

    function setupShare(root) {
        const sheet = document.getElementById('baShareSheet');
        if (!sheet) return;
        const status = $(sheet, '[data-ba="status"]');
        const ctx = {
            id: root.dataset.bookId, title: root.dataset.title, url: root.dataset.url, cover: root.dataset.cover, fbApp: root.dataset.fbApp || '',
            message: () => ($(sheet, '[data-ba="message"]')?.value || root.dataset.text || '').trim(),
        };
        const say = (text, isError) => { status.textContent = text; status.classList.toggle('is-error', !!isError); };

        async function track(channel) {
            const r = await post(root.dataset.shareEndpoint, { channel }).catch(() => null);
            const count = r?.data?.shares;
            const badge = $(root, '[data-ba="share-count"]');
            if (badge && Number.isFinite(count) && count > 0) {
                badge.textContent = count.toLocaleString('bn-BD');
                badge.hidden = false;
            }
        }

        async function copy() {
            const text = tagged(ctx.url, 'copy');
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                const input = $(sheet, '[data-ba="url"]');
                input.value = text; input.select();
                if (!document.execCommand('copy')) { say('কপি করা যায়নি, লিংকটি হাতে কপি করুন।', true); return; }
            }
            say('লিংক কপি হয়েছে');
            toast('লিংক কপি হয়েছে');
            track('copy');
        }

        async function nativeShare() {
            const data = { title: ctx.title, text: ctx.message(), url: tagged(ctx.url, 'native') };
            try {
                // attach the cover picture where the browser can share files (phones, mostly)
                if (navigator.canShare && ctx.cover) {
                    const blob = await fetch(ctx.cover, { mode: 'cors' }).then(r => r.ok ? r.blob() : null).catch(() => null);
                    if (blob && blob.type.startsWith('image/')) {
                        const file = new File([blob], 'book-cover.' + (blob.type.split('/')[1] || 'jpg'), { type: blob.type });
                        if (navigator.canShare({ files: [file] })) data.files = [file];
                    }
                }
                await navigator.share(data);
                track('native');
            } catch (e) {
                if (e && e.name === 'AbortError') return;       // the reader closed the sheet
                say('ডিভাইস থেকে শেয়ার করা যায়নি, অন্য উপায় বেছে নিন।', true);
            }
        }

        async function showQr() {
            const box = $(sheet, '[data-ba="qr-box"]');
            const target = $(sheet, '[data-ba="qr"]');
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
            qr.addData(tagged(ctx.url, 'qr'));
            qr.make();
            target.innerHTML = qr.createSvgTag({ cellSize: 6, margin: 2, scalable: true });
            track('qr');
        }

        function downloadQr() {
            const svg = $(sheet, '[data-ba="qr"] svg');
            if (!svg) return;
            const xml = new XMLSerializer().serializeToString(svg);
            const img = new Image();
            img.onload = () => {
                const c = document.createElement('canvas');
                c.width = c.height = 600;
                const g = c.getContext('2d');
                g.fillStyle = '#fff'; g.fillRect(0, 0, 600, 600); g.drawImage(img, 0, 0, 600, 600);
                const a = document.createElement('a');
                a.download = 'book-qr.png'; a.href = c.toDataURL('image/png'); a.click();
            };
            img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
        }

        function open() {
            say('');
            if (navigator.share) $(sheet, '[data-channel="native"]').hidden = false;
            // Messenger: deep link on phones, the send dialog on desktop only when an app id is configured
            $(sheet, '[data-channel="messenger"]').hidden = !(isMobile() || ctx.fbApp);
            $(sheet, '[data-ba="url"]').value = tagged(ctx.url, 'copy');
            if (typeof sheet.showModal === 'function') { if (!sheet.open) sheet.showModal(); } else { sheet.setAttribute('open', ''); }
        }

        sheet.addEventListener('click', ev => {
            // click on the dimmed backdrop closes the sheet
            if (ev.target === sheet) sheet.close();
            const chip = ev.target.closest('[data-channel]');
            if (chip && sheet.dataset.owner === ctx.id) {
                const ch = chip.dataset.channel;
                if (ch === 'copy') copy();
                else if (ch === 'native') nativeShare();
                else if (ch === 'qr') showQr();
                else {
                    const href = linkFor(ch, ctx);
                    if (!href) return;
                    if (ch === 'facebook') {
                        openFacebook(href);
                        // the dialog cannot be pre-filled: keep the message on the clipboard, ready to paste
                        navigator.clipboard?.writeText(ctx.message() + String.fromCharCode(10) + tagged(ctx.url, 'facebook')).then(() => {
                            say('বার্তা কপি হয়েছে, Facebook-এর পোস্টে পেস্ট করুন');
                            toast('বার্তা কপি হয়েছে, পোস্টে পেস্ট করুন');
                        }).catch(() => {});
                    }
                    else if (ch === 'email' || ch === 'sms' || ch === 'messenger' && href.startsWith('fb-messenger:')) location.href = href;
                    else window.open(href, '_blank', 'noopener,noreferrer');
                    track(ch);
                }
            }
            if (ev.target.closest('[data-ba="qr-download"]')) downloadQr();
        });

        root.addEventListener('click', ev => {
            if (!ev.target.closest('[data-ba="share"]')) return;
            sheet.dataset.owner = ctx.id;
            open();
        });

        window.shareBook = () => { sheet.dataset.owner = ctx.id; open(); };      // older inline callers
    }

    /* --------------------------------------------------------------- wishlist */

    function setupWishlist(root) {
        const btn = $(root, '[data-ba="wishlist"]');
        const label = $(root, '[data-ba="wishlist-label"]');
        if (!btn) return;
        const id = Number(root.dataset.bookId);
        const loggedIn = root.dataset.auth === '1';
        let saved = root.dataset.saved === '1';

        const paint = () => {
            btn.setAttribute('aria-pressed', saved ? 'true' : 'false');
            label.textContent = saved ? 'সংরক্ষিত' : 'উইশলিস্ট';
        };

        if (!loggedIn) { saved = store.read().includes(id); paint(); }

        // signed in with books saved as a guest: move them into the account once
        if (loggedIn) {
            const local = store.read();
            if (local.length) {
                post(root.dataset.wishlistSync, { ids: local }).then(r => {
                    if (r.ok) { store.write([]); saved = (r.data.ids || []).map(Number).includes(id); paint(); }
                }).catch(() => {});
            }
        }

        btn.addEventListener('click', async () => {
            btn.classList.add('is-busy');
            try {
                if (loggedIn) {
                    const r = await post(root.dataset.wishlistEndpoint);
                    if (!r.ok) throw new Error('failed');
                    saved = !!r.data.saved;
                    toast(saved ? 'উইশলিস্টে যুক্ত হয়েছে' : 'উইশলিস্ট থেকে সরানো হয়েছে');
                } else {
                    const ids = store.read();
                    saved = !ids.includes(id);
                    store.write(saved ? [...ids, id] : ids.filter(x => x !== id));
                    toast(saved ? 'এই ব্রাউজারে সংরক্ষিত হয়েছে। লগইন করলে সব ডিভাইসে পাবেন।' : 'উইশলিস্ট থেকে সরানো হয়েছে');
                }
                paint();
            } catch (e) {
                toast('এখন সংরক্ষণ করা যাচ্ছে না, আবার চেষ্টা করুন।', true);
            } finally {
                btn.classList.remove('is-busy');
            }
        });

        window.toggleWishlist = () => btn.click();                                 // older inline callers
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.ba-actions').forEach(root => { setupShare(root); setupWishlist(root); });
    });
})();
