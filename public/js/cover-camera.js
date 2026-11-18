/*
 * Book-cover photo helper for the admin quick-add / quick-edit forms.
 *
 *  CoverCamera.bind(input)  – every photo put into this <input type="file"> (gallery or camera) goes through
 *                             the cover scanner, then is uploaded as an upright JPEG ≤1600 px.
 *  CoverCamera.take(input)  – "take photo": phones open their own camera app directly; computers open a live
 *                             webcam window. Falls back to the file picker.
 *
 * Cover scanner (no library): finds the book's four corners against the background, lets the user drag the
 * corners if needed, straightens a tilted / skewed cover with a perspective warp and fixes the colours
 * (per-channel auto-levels = white balance + contrast).
 */
(function () {
    'use strict';
    var MAX_EDGE = 1600;      // uploaded cover, longest side
    var WORK_EDGE = 2000;     // working copy while scanning
    var DETECT_EDGE = 480;    // copy used to find the corners
    var QUALITY = 0.86;

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

    // ── Decode / encode ──────────────────────────────────────
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
    function canvasOf(src, maxEdge) {
        var w = src.width || src.naturalWidth, h = src.height || src.naturalHeight;
        var s = Math.min(1, maxEdge / Math.max(w, h));
        var c = document.createElement('canvas');
        c.width = Math.max(1, Math.round(w * s));
        c.height = Math.max(1, Math.round(h * s));
        var ctx = c.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, c.width, c.height);
        ctx.drawImage(src, 0, 0, c.width, c.height);
        return c;
    }
    function toJpeg(canvas, baseName) {
        return new Promise(function (res) {
            canvas.toBlob(function (b) {
                var name = (baseName || 'cover').replace(/\.[^.]+$/, '') + '.jpg';
                if (!b) return res(null);
                try { res(new File([b], name, { type: 'image/jpeg', lastModified: Date.now() })); } catch (e) { b.name = name; res(b); }
            }, 'image/jpeg', QUALITY);
        });
    }
    /** Plain shrink (used when the scanner is skipped). */
    function compress(file) {
        if (!file || !/^image\//.test(file.type || 'image/') || /svg|gif/.test(file.type || '')) return Promise.resolve(file);
        return decode(file).then(function (img) {
            var w = img.width || img.naturalWidth, h = img.height || img.naturalHeight;
            if (Math.max(w, h) <= MAX_EDGE && file.size < 900 * 1024 && /jpe?g|webp/.test(file.type)) return file;
            return toJpeg(canvasOf(img, MAX_EDGE), file.name).then(function (f) { return f || file; });
        }).catch(function () { return file; });
    }

    // ── Corner detection ─────────────────────────────────────
    function median(arr) { arr.sort(function (a, b) { return a - b; }); return arr[arr.length >> 1] || 0; }

    function otsu(hist, total) {
        var sum = 0, i;
        for (i = 0; i < 256; i++) sum += i * hist[i];
        var sumB = 0, wB = 0, best = 0, t = 0;
        for (i = 0; i < 256; i++) {
            wB += hist[i]; if (!wB) continue;
            var wF = total - wB; if (!wF) break;
            sumB += i * hist[i];
            var mB = sumB / wB, mF = (sum - sumB) / wF, between = wB * wF * (mB - mF) * (mB - mF);
            if (between > best) { best = between; t = i; }
        }
        return t;
    }

    function morph(mask, w, h, grow) {
        var out = new Uint8Array(w * h);
        for (var y = 0; y < h; y++) for (var x = 0; x < w; x++) {
            var hit = grow ? 0 : 1;
            for (var dy = -1; dy <= 1; dy++) for (var dx = -1; dx <= 1; dx++) {
                var yy = y + dy, xx = x + dx;
                var v = (yy < 0 || xx < 0 || yy >= h || xx >= w) ? 0 : mask[yy * w + xx];
                if (grow && v) hit = 1;
                if (!grow && !v) hit = 0;
            }
            out[y * w + x] = hit;
        }
        return out;
    }

    function orderCorners(pts) {
        // TL = min(x+y), BR = max(x+y), TR = max(x−y), BL = min(x−y)
        var tl = pts[0], tr = pts[0], br = pts[0], bl = pts[0];
        pts.forEach(function (p) {
            if (p.x + p.y < tl.x + tl.y) tl = p;
            if (p.x + p.y > br.x + br.y) br = p;
            if (p.x - p.y > tr.x - tr.y) tr = p;
            if (p.x - p.y < bl.x - bl.y) bl = p;
        });
        return [tl, tr, br, bl].map(function (p) { return { x: p.x, y: p.y }; });
    }

    function polyArea(q) {
        var a = 0;
        for (var i = 0; i < 4; i++) { var p = q[i], n = q[(i + 1) % 4]; a += p.x * n.y - n.x * p.y; }
        return Math.abs(a) / 2;
    }

    /** Four corners of the cover in canvas coordinates, or null when no clear cover is found. */
    function detect(canvas) {
        var small = canvasOf(canvas, DETECT_EDGE);
        var w = small.width, h = small.height;
        var px = small.getContext('2d', { willReadFrequently: true }).getImageData(0, 0, w, h).data;

        // Background colour = median of a thin frame around the photo.
        var band = Math.max(2, Math.round(Math.min(w, h) * 0.04)), rs = [], gs = [], bs = [];
        for (var y = 0; y < h; y++) for (var x = 0; x < w; x++) {
            if (x >= band && x < w - band && y >= band && y < h - band) continue;
            var o = (y * w + x) * 4; rs.push(px[o]); gs.push(px[o + 1]); bs.push(px[o + 2]);
        }
        var bg = [median(rs), median(gs), median(bs)];

        // Distance from the background colour, thresholded with Otsu.
        var dist = new Uint8Array(w * h), hist = new Array(256).fill(0);
        for (var i = 0; i < w * h; i++) {
            var dr = px[i * 4] - bg[0], dg = px[i * 4 + 1] - bg[1], db = px[i * 4 + 2] - bg[2];
            var d = Math.min(255, Math.round(Math.sqrt(dr * dr + dg * dg + db * db) * 0.58));
            dist[i] = d; hist[d]++;
        }
        var t = Math.max(14, otsu(hist, w * h));
        var mask = new Uint8Array(w * h);
        for (i = 0; i < w * h; i++) mask[i] = dist[i] > t ? 1 : 0;
        mask = morph(morph(morph(mask, w, h, true), w, h, true), w, h, false); // close small gaps
        mask = morph(mask, w, h, false);

        // Largest connected region.
        var seen = new Uint8Array(w * h), best = null, bestN = 0, stack = new Int32Array(w * h);
        for (i = 0; i < w * h; i++) {
            if (!mask[i] || seen[i]) continue;
            var sp = 0, n = 0, pts = [];
            stack[sp++] = i; seen[i] = 1;
            while (sp) {
                var k = stack[--sp], kx = k % w, ky = (k / w) | 0;
                n++;
                if ((n & 3) === 0) pts.push({ x: kx, y: ky }); // sample of the region's pixels
                if (kx > 0 && mask[k - 1] && !seen[k - 1]) { seen[k - 1] = 1; stack[sp++] = k - 1; }
                if (kx < w - 1 && mask[k + 1] && !seen[k + 1]) { seen[k + 1] = 1; stack[sp++] = k + 1; }
                if (ky > 0 && mask[k - w] && !seen[k - w]) { seen[k - w] = 1; stack[sp++] = k - w; }
                if (ky < h - 1 && mask[k + w] && !seen[k + w]) { seen[k + w] = 1; stack[sp++] = k + w; }
            }
            if (n > bestN) { bestN = n; best = pts; }
        }
        if (!best || bestN < w * h * 0.12 || bestN > w * h * 0.985) return null;

        var q = orderCorners(best);
        if (polyArea(q) < w * h * 0.12) return null;
        var sx = canvas.width / w, sy = canvas.height / h;
        return q.map(function (p) { return { x: (p.x + 0.5) * sx, y: (p.y + 0.5) * sy }; });
    }

    function fullQuad(c) { return [{ x: 0, y: 0 }, { x: c.width, y: 0 }, { x: c.width, y: c.height }, { x: 0, y: c.height }]; }

    // ── Straighten (perspective warp) ────────────────────────
    function solve(A, b) {
        var n = b.length, i, j, k;
        for (i = 0; i < n; i++) A[i] = A[i].concat([b[i]]);
        for (i = 0; i < n; i++) {
            var max = i;
            for (k = i + 1; k < n; k++) if (Math.abs(A[k][i]) > Math.abs(A[max][i])) max = k;
            var tmp = A[i]; A[i] = A[max]; A[max] = tmp;
            if (Math.abs(A[i][i]) < 1e-10) return null;
            for (k = i + 1; k < n; k++) {
                var f = A[k][i] / A[i][i];
                for (j = i; j <= n; j++) A[k][j] -= f * A[i][j];
            }
        }
        var x = new Array(n);
        for (i = n - 1; i >= 0; i--) {
            var s = A[i][n];
            for (j = i + 1; j < n; j++) s -= A[i][j] * x[j];
            x[i] = s / A[i][i];
        }
        return x;
    }
    /** Homography taking output-rectangle points (u,v) to source points (x,y). */
    function homography(dst, src) {
        var A = [], b = [];
        for (var i = 0; i < 4; i++) {
            var u = dst[i].x, v = dst[i].y, x = src[i].x, y = src[i].y;
            A.push([u, v, 1, 0, 0, 0, -u * x, -v * x]); b.push(x);
            A.push([0, 0, 0, u, v, 1, -u * y, -v * y]); b.push(y);
        }
        return solve(A, b);
    }
    function dist2(a, b) { return Math.hypot(a.x - b.x, a.y - b.y); }

    function warp(canvas, q) {
        var W = Math.max(dist2(q[0], q[1]), dist2(q[3], q[2]));
        var H = Math.max(dist2(q[0], q[3]), dist2(q[1], q[2]));
        var s = Math.min(1, MAX_EDGE / Math.max(W, H));
        W = Math.max(8, Math.round(W * s)); H = Math.max(8, Math.round(H * s));
        var hm = homography([{ x: 0, y: 0 }, { x: W, y: 0 }, { x: W, y: H }, { x: 0, y: H }], q);
        var out = document.createElement('canvas');
        out.width = W; out.height = H;
        if (!hm) { out.getContext('2d').drawImage(canvas, 0, 0, W, H); return out; }

        var sw = canvas.width, sh = canvas.height;
        var src = canvas.getContext('2d', { willReadFrequently: true }).getImageData(0, 0, sw, sh).data;
        var octx = out.getContext('2d');
        var img = octx.createImageData(W, H), d = img.data;
        for (var v = 0; v < H; v++) {
            for (var u = 0; u < W; u++) {
                var den = hm[6] * u + hm[7] * v + 1;
                var x = (hm[0] * u + hm[1] * v + hm[2]) / den, y = (hm[3] * u + hm[4] * v + hm[5]) / den;
                var x0 = Math.floor(x), y0 = Math.floor(y), fx = x - x0, fy = y - y0;
                if (x0 < 0) { x0 = 0; fx = 0; } if (y0 < 0) { y0 = 0; fy = 0; }
                if (x0 >= sw - 1) { x0 = sw - 2; fx = 1; } if (y0 >= sh - 1) { y0 = sh - 2; fy = 1; }
                var i00 = (y0 * sw + x0) * 4, i10 = i00 + 4, i01 = i00 + sw * 4, i11 = i01 + 4, o = (v * W + u) * 4;
                for (var c = 0; c < 3; c++) {
                    d[o + c] = (src[i00 + c] * (1 - fx) + src[i10 + c] * fx) * (1 - fy) + (src[i01 + c] * (1 - fx) + src[i11 + c] * fx) * fy;
                }
                d[o + 3] = 255;
            }
        }
        octx.putImageData(img, 0, 0);
        return out;
    }

    // ── Colour correction: per-channel auto-levels ───────────
    function fixColours(canvas) {
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        var img = ctx.getImageData(0, 0, canvas.width, canvas.height), d = img.data, n = d.length / 4;
        var hists = [new Uint32Array(256), new Uint32Array(256), new Uint32Array(256)];
        for (var i = 0; i < d.length; i += 4) { hists[0][d[i]]++; hists[1][d[i + 1]]++; hists[2][d[i + 2]]++; }
        var luts = hists.map(function (hst) {
            var lo = 0, hi = 255, acc = 0, cut = n * 0.005;
            while (lo < 255 && (acc += hst[lo]) < cut) lo++;
            acc = 0;
            while (hi > 0 && (acc += hst[hi]) < cut) hi--;
            var lut = new Uint8ClampedArray(256);
            if (hi - lo < 40) { for (var k = 0; k < 256; k++) lut[k] = k; return lut; } // nearly flat: leave it
            for (var k2 = 0; k2 < 256; k2++) lut[k2] = (k2 - lo) * 255 / (hi - lo);
            return lut;
        });
        for (i = 0; i < d.length; i += 4) {
            var r = luts[0][d[i]], g = luts[1][d[i + 1]], b = luts[2][d[i + 2]];
            // A touch more colour after the stretch.
            var l = 0.299 * r + 0.587 * g + 0.114 * b;
            d[i] = l + (r - l) * 1.08; d[i + 1] = l + (g - l) * 1.08; d[i + 2] = l + (b - l) * 1.08;
        }
        ctx.putImageData(img, 0, 0);
        return canvas;
    }

    // ── Scanner window ───────────────────────────────────────
    var ui = null;

    function buildScanner() {
        ui = document.createElement('div');
        ui.className = 'cs-overlay';
        ui.hidden = true;
        ui.setAttribute('role', 'dialog');
        ui.setAttribute('aria-modal', 'true');
        ui.setAttribute('aria-label', 'কভার স্ক্যান');
        ui.innerHTML =
            '<div class="cs-top"><div><b>কভার ঠিক করুন</b><div class="cs-hint">নীল কোণগুলো বইয়ের চার কোণে না থাকলে টেনে বসান</div></div></div>' +
            '<div class="cs-stage"><div class="cs-wrap"><svg preserveAspectRatio="none"><polygon></polygon></svg></div><div class="cs-busy" hidden>প্রস্তুত হচ্ছে…</div></div>' +
            '<div class="cs-bar">' +
                '<button type="button" class="btn btn-outline-light" data-cs="rotate">↻ ঘোরান</button>' +
                '<button type="button" class="btn btn-outline-light" data-cs="auto">✨ অটো</button>' +
                '<button type="button" class="btn btn-outline-light" data-cs="full">⛶ পুরো ছবি</button>' +
                '<label><input type="checkbox" class="form-check-input m-0" data-cs="colour" checked> রং ঠিক করুন</label>' +
                '<button type="button" class="btn btn-outline-secondary text-light" data-cs="skip">স্ক্যান ছাড়া</button>' +
                '<button type="button" class="btn btn-primary px-4" data-cs="use">✔ ব্যবহার করুন</button>' +
            '</div>';
        document.body.appendChild(ui);
        ui._wrap = ui.querySelector('.cs-wrap');
        ui._svg = ui.querySelector('svg');
        ui._poly = ui.querySelector('polygon');
        ui._busy = ui.querySelector('.cs-busy');
        ui._handles = [0, 1, 2, 3].map(function (i) {
            var hd = document.createElement('div');
            hd.className = 'cs-handle';
            hd.setAttribute('role', 'slider');
            hd.setAttribute('aria-label', ['উপর-বাম', 'উপর-ডান', 'নিচ-ডান', 'নিচ-বাম'][i] + ' কোণ');
            hd.tabIndex = 0;
            ui._wrap.appendChild(hd);
            hd.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                hd.setPointerCapture(e.pointerId);
                hd._drag = true;
            });
            hd.addEventListener('pointermove', function (e) {
                if (!hd._drag) return;
                var r = ui._canvas.getBoundingClientRect();
                var x = Math.min(Math.max(e.clientX - r.left, 0), r.width), y = Math.min(Math.max(e.clientY - r.top, 0), r.height);
                ui._quad[i] = { x: x / r.width * ui._canvas.width, y: y / r.height * ui._canvas.height };
                paint();
            });
            hd.addEventListener('pointerup', function () { hd._drag = false; });
            hd.addEventListener('keydown', function (e) {
                var step = ui._canvas.width / 100, q = ui._quad[i];
                var mv = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] }[e.key];
                if (!mv) return;
                e.preventDefault();
                ui._quad[i] = { x: Math.min(Math.max(q.x + mv[0], 0), ui._canvas.width), y: Math.min(Math.max(q.y + mv[1], 0), ui._canvas.height) };
                paint();
            });
            return hd;
        });
        ui.addEventListener('click', function (e) {
            var b = e.target.closest('button[data-cs]');
            if (!b) return;
            var a = b.dataset.cs;
            if (a === 'rotate') rotate();
            if (a === 'auto') { ui._quad = detect(ui._canvas) || fullQuad(ui._canvas); paint(); }
            if (a === 'full') { ui._quad = fullQuad(ui._canvas); paint(); }
            if (a === 'skip') finish(false);
            if (a === 'use') finish(true);
        });
        document.addEventListener('keydown', function (e) { if (!ui.hidden && e.key === 'Escape') finish(false); });
    }

    function paint() {
        var c = ui._canvas, q = ui._quad;
        ui._svg.setAttribute('viewBox', '0 0 ' + c.width + ' ' + c.height);
        ui._poly.setAttribute('points', q.map(function (p) { return p.x + ',' + p.y; }).join(' '));
        ui._handles.forEach(function (hd, i) {
            hd.style.left = (q[i].x / c.width * 100) + '%';
            hd.style.top = (q[i].y / c.height * 100) + '%';
        });
    }

    function rotate() {
        var c = ui._canvas, r = document.createElement('canvas');
        r.width = c.height; r.height = c.width;
        var ctx = r.getContext('2d', { willReadFrequently: true });
        ctx.translate(r.width, 0);
        ctx.rotate(Math.PI / 2);
        ctx.drawImage(c, 0, 0);
        var q = orderCorners(ui._quad.map(function (p) { return { x: c.height - p.y, y: p.x }; }));
        ui._wrap.replaceChild(r, c);
        ui._canvas = r;
        ui._quad = q;
        paint();
    }

    function finish(apply) {
        var done = ui._done;
        if (!done) return;
        ui._done = null;
        if (!apply) {
            ui.hidden = true;
            document.body.style.overflow = '';
            compress(ui._file).then(done);
            return;
        }
        ui._busy.hidden = false;
        var colour = ui.querySelector('[data-cs="colour"]').checked;
        setTimeout(function () { // let the "busy" note paint first
            var out = warp(ui._canvas, orderCorners(ui._quad));
            if (colour) fixColours(out);
            toJpeg(out, ui._file.name || 'cover').then(function (f) {
                ui._busy.hidden = true;
                ui.hidden = true;
                document.body.style.overflow = '';
                done(f || ui._file);
            });
        }, 30);
    }

    /** Open the scanner for a photo; resolves with the processed JPEG (or the plain-shrunk photo if skipped). */
    function scan(file) {
        if (!file || /svg|gif/.test(file.type || '')) return Promise.resolve(file);
        return decode(file).then(function (img) {
            if (!ui) buildScanner();
            var c = canvasOf(img, WORK_EDGE);
            if (ui._canvas) ui._wrap.removeChild(ui._canvas);
            ui._wrap.insertBefore(c, ui._svg);
            ui._canvas = c;
            ui._file = file;
            ui._quad = detect(c) || fullQuad(c);
            ui.hidden = false;
            document.body.style.overflow = 'hidden';
            paint();
            return new Promise(function (resolve) { ui._done = resolve; });
        }).catch(function () { return compress(file); });
    }

    // ── Form input wiring ────────────────────────────────────
    /** Put a file into the form's input (so the normal form submit sends it) and run its preview handler. */
    function place(input, file) {
        if (!file) return;
        input._ccBlob = file;
        input._ccPlacing = true;
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) { /* old browsers: the submit code reads input._ccBlob instead */ }
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
            scan(f).then(function (out) { place(input, out); });
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
                '<div style="color:#94a3b8;font-size:12px;text-align:center;margin-top:8px;">বইটি গাঢ় বা সাদা সাধারণ পটভূমিতে রাখুন — কোণ খুঁজে সোজা করা সহজ হবে</div>' +
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
            try { f = new File([b], 'camera-cover.jpg', { type: 'image/jpeg' }); } catch (e) { f = b; f.name = 'camera-cover.jpg'; }
            scan(f).then(function (out) { place(target, out); });
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
                if (f) scan(f).then(function (out) { place(input, out); });
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

    window.CoverCamera = { bind: bind, take: take, compress: compress, scan: scan, _detect: detect, _warp: warp, _fixColours: fixColours };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="file"][data-cover-camera]').forEach(bind);
    });
})();
