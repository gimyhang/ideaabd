/*
 * Book-cover photo helper for the admin quick-add / quick-edit forms.
 *
 *  CoverCamera.bind(input)  – any image put into this <input type="file"> (gallery or camera) is turned
 *                             upright, shrunk to ≤1600 px and re-encoded as JPEG before it is uploaded,
 *                             so 5–12 MB phone photos no longer fail the upload limit.
 *  CoverCamera.take(input)  – "take photo": phones open their own camera app directly (rear camera);
 *                             computers open a live webcam window. Falls back to the file picker.
 */
(function () {
    'use strict';
    var MAX_EDGE = 1600;
    var QUALITY = 0.85;

    function isTouch() {
        return (window.matchMedia && matchMedia('(pointer: coarse)').matches) || /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);
    }

    function toast(msg) {
        var box = document.getElementById('ccToast');
        if (!box) {
            box = document.createElement('div');
            box.id = 'ccToast';
            box.setAttribute('role', 'status');
            box.style.cssText = 'position:fixed;left:50%;bottom:20px;transform:translateX(-50%);z-index:3000;max-width:min(420px,calc(100vw - 32px));background:#0f172a;color:#fff;padding:10px 16px;border-radius:12px;font-size:13px;line-height:1.5;box-shadow:0 12px 30px -10px rgba(0,0,0,.5);';
            document.body.appendChild(box);
        }
        box.textContent = msg;
        box.hidden = false;
        clearTimeout(box._t);
        box._t = setTimeout(function () { box.hidden = true; }, 6000);
    }

    // ── Shrink + upright JPEG ────────────────────────────────
    function decode(file) {
        if (window.createImageBitmap) {
            return createImageBitmap(file, { imageOrientation: 'from-image' }).catch(function () { return createImageBitmap(file); }).catch(function () { return viaImg(file); });
        }
        return viaImg(file);
    }
    function viaImg(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('decode')); };
            img.src = url;
        });
    }
    function compress(file) {
        if (!file || !/^image\//.test(file.type || 'image/')) return Promise.resolve(file);
        if (file.type === 'image/svg+xml' || file.type === 'image/gif') return Promise.resolve(file);
        return decode(file).then(function (img) {
            var w = img.width || img.naturalWidth, h = img.height || img.naturalHeight;
            var scale = Math.min(1, MAX_EDGE / Math.max(w, h));
            // Already small and a JPEG/WebP: keep the original bytes.
            if (scale === 1 && file.size < 900 * 1024 && /jpe?g|webp/.test(file.type)) return file;
            var c = document.createElement('canvas');
            c.width = Math.round(w * scale);
            c.height = Math.round(h * scale);
            var ctx = c.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, c.width, c.height);
            ctx.drawImage(img, 0, 0, c.width, c.height);
            return new Promise(function (res) {
                c.toBlob(function (b) {
                    if (!b) return res(file);
                    var name = (file.name || 'cover').replace(/\.[^.]+$/, '') + '.jpg';
                    try { res(new File([b], name, { type: 'image/jpeg', lastModified: Date.now() })); } catch (e) { b.name = name; res(b); }
                }, 'image/jpeg', QUALITY);
            });
        }).catch(function () { return file; });
    }

    /** Put a file into the form's input (so the normal form submit sends it) and run its preview handler. */
    function place(input, file) {
        input._ccBlob = file;
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input._ccPlacing = true;
            input.files = dt.files;
        } catch (e) { /* old browsers: the submit code reads input._ccBlob instead */ }
        input._ccPlacing = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input._ccPlacing = false;
    }

    function bind(input) {
        if (!input || input._ccBound) return;
        input._ccBound = true;
        // Capture phase: runs before the inline onchange preview, so the preview shows the processed photo.
        input.addEventListener('change', function (e) {
            if (input._ccPlacing) return;
            var f = input.files && input.files[0];
            input._ccBlob = null;
            if (!f) return;
            e.stopImmediatePropagation();
            compress(f).then(function (small) { place(input, small); });
        }, true);
    }

    // ── Live webcam window (computers) ───────────────────────
    var modal, video, stream, facing = 'environment', target;

    function buildModal() {
        modal = document.createElement('div');
        modal.id = 'ccModal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-label', 'ছবি তুলুন');
        modal.style.cssText = 'position:fixed;inset:0;z-index:2500;background:rgba(2,6,23,.82);display:none;align-items:center;justify-content:center;padding:16px;';
        modal.innerHTML =
            '<div style="background:#0b1220;border-radius:16px;padding:12px;width:min(560px,100%);box-shadow:0 30px 60px -20px rgba(0,0,0,.6);">' +
                '<div style="position:relative;background:#000;border-radius:12px;overflow:hidden;aspect-ratio:3/4;max-height:70vh;margin:0 auto;">' +
                    '<video playsinline autoplay muted style="width:100%;height:100%;object-fit:cover;display:block;"></video>' +
                    '<div style="position:absolute;inset:8% 14%;border:2px dashed rgba(255,255,255,.55);border-radius:8px;pointer-events:none;"></div>' +
                '</div>' +
                '<div style="display:flex;gap:8px;justify-content:center;margin-top:12px;flex-wrap:wrap;">' +
                    '<button type="button" data-cc="flip" class="btn btn-sm btn-outline-light rounded-pill px-3">↺ ক্যামেরা বদলান</button>' +
                    '<button type="button" data-cc="snap" class="btn btn-sm btn-light rounded-pill px-4 fw-bold">📷 ছবি তুলুন</button>' +
                    '<button type="button" data-cc="close" class="btn btn-sm btn-outline-light rounded-pill px-3">বাতিল</button>' +
                '</div>' +
                '<div style="color:#94a3b8;font-size:12px;text-align:center;margin-top:8px;">বইয়ের কভার দাগের ভেতরে রাখুন</div>' +
            '</div>';
        document.body.appendChild(modal);
        video = modal.querySelector('video');
        modal.addEventListener('click', function (e) {
            var a = e.target.closest('[data-cc]');
            if (!a) { if (e.target === modal) close(); return; }
            if (a.dataset.cc === 'close') close();
            if (a.dataset.cc === 'flip') { facing = facing === 'environment' ? 'user' : 'environment'; start(); }
            if (a.dataset.cc === 'snap') snap();
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.style.display === 'flex') close(); });
    }

    function stop() { if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; } }

    function start() {
        stop();
        return navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: facing }, width: { ideal: 1920 }, height: { ideal: 1440 } }, audio: false })
            .then(function (s) { stream = s; video.srcObject = s; return video.play().catch(function () {}); });
    }

    function close() {
        stop();
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function snap() {
        if (!video.videoWidth) return;
        var c = document.createElement('canvas');
        c.width = video.videoWidth;
        c.height = video.videoHeight;
        c.getContext('2d').drawImage(video, 0, 0);
        c.toBlob(function (b) {
            close();
            if (!b) return;
            var f;
            try { f = new File([b], 'camera-cover.jpg', { type: 'image/jpeg' }); } catch (e) { f = b; }
            compress(f).then(function (small) { place(target, small); });
        }, 'image/jpeg', 0.92);
    }

    function explain(err) {
        var n = err && err.name;
        if (!window.isSecureContext) return 'ক্যামেরা শুধু https ঠিকানায় চলে।';
        if (n === 'NotAllowedError' || n === 'SecurityError') return 'ক্যামেরার অনুমতি দেওয়া হয়নি। ব্রাউজারের ঠিকানা-বারের 🔒 আইকনে গিয়ে Camera → Allow করুন, তারপর আবার চেষ্টা করুন।';
        if (n === 'NotFoundError' || n === 'OverconstrainedError') return 'এই ডিভাইসে কোনো ক্যামেরা পাওয়া যায়নি।';
        if (n === 'NotReadableError') return 'ক্যামেরা অন্য কোনো অ্যাপ ব্যবহার করছে। সেটি বন্ধ করে আবার চেষ্টা করুন।';
        return 'ক্যামেরা চালু করা যায়নি।';
    }

    function take(input) {
        if (!input) return;
        bind(input);
        target = input;

        // Phones/tablets: the phone's own camera app, opened directly from this tap.
        if (isTouch()) {
            var cap = document.createElement('input');
            cap.type = 'file';
            cap.accept = 'image/*';
            cap.setAttribute('capture', 'environment');
            cap.style.display = 'none';
            cap.addEventListener('change', function () {
                var f = cap.files && cap.files[0];
                if (f) compress(f).then(function (small) { place(input, small); });
                cap.remove();
            });
            document.body.appendChild(cap);
            cap.click();
            return;
        }

        // Computers: live webcam window.
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            toast(window.isSecureContext ? 'এই ব্রাউজারে ক্যামেরা সমর্থিত নয় — ফাইল থেকে ছবি বেছে নিন।' : 'ক্যামেরা শুধু https ঠিকানায় চলে — ফাইল থেকে ছবি বেছে নিন।');
            input.click();
            return;
        }
        if (!modal) buildModal();
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        start().catch(function (err) {
            close();
            toast(explain(err));
        });
    }

    window.CoverCamera = { bind: bind, take: take, compress: compress };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="file"][data-cover-camera]').forEach(bind);
    });
})();
