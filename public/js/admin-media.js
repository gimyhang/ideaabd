/* /admin/media — library, upload, details, editor and tools */
(function () {
    'use strict';

    var app = document.getElementById('mdApp');
    if (!app) return;

    var U = app.dataset;
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var FOLDERS = {};
    try { JSON.parse(document.getElementById('mdFoldersData').textContent).forEach(function (f) { FOLDERS[f.key] = f; }); } catch (e) {}

    var grid = document.getElementById('mdGrid');
    var $ = function (id) { return document.getElementById(id); };
    var state = { folder: 'all', search: '', format: 'all', shape: 'all', usage: 'all', sort: 'latest', page: 1, pages: 1, count: 0, view: 'grid' };
    var items = [];            // loaded items, in order
    var byId = {};
    var selected = new Map();  // id -> item (or {id} when selected through "select all")
    var loadSeq = 0;

    try {
        var saved = JSON.parse(localStorage.getItem('md_view') || 'null');
        if (saved === 'list' || saved === 'grid') state.view = saved;
    } catch (e) {}
    var q = new URLSearchParams(location.search);
    ['folder', 'search', 'format', 'shape', 'usage', 'sort'].forEach(function (k) { if (q.get(k)) state[k] = q.get(k); });
    if (q.get('dim') && !q.get('shape')) state.shape = { banner: 'wide', thumb: 'small', square: 'square', portrait: 'portrait' }[q.get('dim')] || 'all';
    if (state.folder === 'books') state.folder = 'covers';

    // ---------------------------------------------------------------- helpers
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function bytes(n) {
        n = Number(n) || 0;
        if (n < 1024) return n + ' B';
        var u = ['KB', 'MB', 'GB'], i = -1;
        do { n /= 1024; i++; } while (n >= 1024 && i < 2);
        return (n >= 100 ? Math.round(n) : n.toFixed(1)) + ' ' + u[i];
    }
    function num(n) { return Number(n || 0).toLocaleString('en'); }
    function ago(t) {
        var s = Math.max(1, Math.floor(Date.now() / 1000 - t));
        var d = [[31536000, 'y'], [2592000, 'mo'], [86400, 'd'], [3600, 'h'], [60, 'm']];
        for (var i = 0; i < d.length; i++) if (s >= d[i][0]) return Math.floor(s / d[i][0]) + d[i][1] + ' ago';
        return 'just now';
    }
    function absUrl(u) { return new URL(String(u).split('?')[0], location.href).href; }

    function request(method, url, body, opts) {
        opts = opts || {};
        var init = { method: method, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } };
        if (body instanceof FormData) init.body = body;
        else if (body !== undefined) { init.headers['Content-Type'] = 'application/json'; init.body = JSON.stringify(body); }
        return fetch(url, init).then(function (res) {
            if (opts.blob && res.ok) return res.blob();
            return res.json().catch(function () { return {}; }).then(function (data) {
                if (!res.ok) {
                    var msg = data.message;
                    if (data.errors) for (var k in data.errors) { msg = data.errors[k][0]; break; }
                    var err = new Error(msg || (res.status === 403 ? 'You do not have permission' : res.status === 419 ? 'Session expired — reload the page' : res.status === 413 ? 'File too large for the server' : 'Something went wrong'));
                    err.status = res.status; err.data = data;
                    throw err;
                }
                return data;
            });
        });
    }

    var toastTimer;
    function toast(msg, isError) {
        var el = $('mdToast');
        el.innerHTML = '<i class="fa-solid ' + (isError ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i><span>' + esc(msg) + '</span>';
        el.classList.toggle('is-error', !!isError);
        el.classList.toggle('is-lifted', !$('mdSelbar').hidden);
        el.hidden = false;
        el.style.animation = 'none'; void el.offsetWidth; el.style.animation = '';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.hidden = true; }, 3400);
    }

    function confirmBox(title, text, danger, okText) {
        if (window.SwalConfirm) {
            return window.SwalConfirm({ title: title, text: text, icon: danger ? 'warning' : 'question', isDanger: !!danger, confirmButtonText: okText || (danger ? 'Delete' : 'Yes'), cancelButtonText: 'Cancel' })
                .then(function (r) { return !!(r && r.isConfirmed); });
        }
        return Promise.resolve(window.confirm(title + '\n\n' + text));
    }

    function copyText(text, label) {
        var done = function () { toast((label || 'Link') + ' copied'); };
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text); done(); });
        fallbackCopy(text); done();
    }
    function fallbackCopy(text) {
        var t = document.createElement('textarea');
        t.value = text; t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); } catch (e) {}
        t.remove();
    }

    function modal(id) { return window.bootstrap ? bootstrap.Modal.getOrCreateInstance($(id)) : null; }

    // ---------------------------------------------------------------- loading
    function params(extra) {
        var p = new URLSearchParams();
        ['folder', 'search', 'format', 'shape', 'usage', 'sort'].forEach(function (k) { if (state[k] && state[k] !== 'all' && !(k === 'sort' && state[k] === 'latest')) p.set(k, state[k]); });
        Object.keys(extra || {}).forEach(function (k) { p.set(k, extra[k]); });
        return p;
    }

    function syncUrl() {
        var p = params();
        history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : ''));
        var filtered = state.search || state.format !== 'all' || state.shape !== 'all' || state.usage !== 'all';
        $('mdClearFilters').hidden = !filtered;
        ['Format', 'Shape', 'Usage'].forEach(function (k) { $('md' + k).classList.toggle('is-set', state[k.toLowerCase()] !== 'all'); });
    }

    function load(append) {
        var seq = ++loadSeq;
        if (!append) {
            state.page = 1;
            grid.innerHTML = '<div class="md-skel-wrap" style="display:contents">' + new Array(12).join('<div class="md-skel"></div>') + '</div>';
        }
        syncUrl();
        var btn = $('mdMoreBtn');
        btn.classList.add('is-busy');
        return request('GET', U.items + '?' + params({ page: state.page })).then(function (d) {
            if (seq !== loadSeq) return;
            if (!append) { items = []; byId = {}; grid.innerHTML = ''; }
            state.pages = d.pages; state.count = d.count;
            d.items.forEach(function (it) { items.push(it); byId[it.id] = it; });
            grid.insertAdjacentHTML('beforeend', d.items.map(card).join(''));
            if (!items.length) grid.innerHTML = emptyHtml();
            $('mdResultText').textContent = d.count ? num(d.count) + ' image' + (d.count === 1 ? '' : 's') + ' · ' + bytes(d.bytes) : 'No images';
            $('mdMore').hidden = state.page >= state.pages;
            paintStats(d.stats);
            paintSelection();
        }).catch(function (e) {
            if (seq !== loadSeq) return;
            grid.innerHTML = '<div class="md-empty"><i class="fa-solid fa-triangle-exclamation"></i><strong>Could not load</strong><span>' + esc(e.message) + '</span></div>';
        }).then(function () { if (seq === loadSeq) btn.classList.remove('is-busy'); });
    }

    function emptyHtml() {
        var filtered = state.search || state.format !== 'all' || state.shape !== 'all' || state.usage !== 'all';
        return '<div class="md-empty"><i class="fa-regular fa-image"></i><strong>' + (filtered ? 'Nothing matches' : 'This folder is empty') + '</strong><span>' +
            (filtered ? 'Try another search or clear the filters.' : 'Drop images anywhere on this page to upload.') + '</span></div>';
    }

    function card(it) {
        var f = FOLDERS[it.folder] || {};
        var heavy = it.size >= 200 * 1024;
        var dims = it.w ? it.w + '×' + it.h : it.format.toUpperCase();
        var used = it.used === true ? 'is-used' : it.used === false ? 'is-unused' : '';
        var usedTitle = it.used === true ? 'Used on the site' : it.used === false ? 'Not used anywhere' : 'Use unknown';
        return '<article class="md-card' + (selected.has(it.id) ? ' is-selected' : '') + '" data-id="' + esc(it.id) + '">' +
            '<label class="md-check" title="Select"><input type="checkbox" data-select' + (selected.has(it.id) ? ' checked' : '') + '><span></span></label>' +
            '<div class="md-media"><button type="button" class="md-thumb" data-open title="' + esc(it.name) + '"><img src="' + esc(it.url) + '" alt="' + esc(it.title) + '" loading="lazy" decoding="async"></button>' +
            '<div class="md-badges"><span class="md-badge' + (it.format === 'webp' ? ' is-webp' : '') + '">' + esc(it.format) + '</span>' + (heavy ? '<span class="md-badge is-heavy">' + bytes(it.size) + '</span>' : '') + '</div>' +
            '<div class="md-hover">' +
                '<button type="button" data-open title="Details"><i class="fa-solid fa-expand"></i></button>' +
                '<button type="button" data-copy title="Copy link"><i class="fa-regular fa-copy"></i></button>' +
                (isEditable(it) ? '<button type="button" data-edit title="Edit"><i class="fa-solid fa-sliders"></i></button>' : '') +
            '</div></div>' +
            '<div class="md-info">' +
                '<div class="md-name" title="' + esc(it.title) + '">' + esc(it.title) + '</div>' +
                '<div class="md-meta"><i class="md-dot ' + used + '" title="' + usedTitle + '"></i><span>' + esc(dims) + ' · ' + bytes(it.size) + '</span>' +
                (state.folder === 'all' ? '<span class="md-folder-tag" style="--c:' + esc(f.color || '#64748b') + '">' + esc(f.label || '') + '</span>' : '') + '</div>' +
            '</div>' +
            '<div class="md-row-acts">' +
                '<button type="button" class="md-icon" data-copy title="Copy link"><i class="fa-regular fa-copy"></i></button>' +
                '<button type="button" class="md-icon" data-open title="Details"><i class="fa-solid fa-chevron-right"></i></button>' +
            '</div>' +
        '</article>';
    }

    function isEditable(it) { return U.gd === '1' && ['jpg', 'png', 'webp'].indexOf(it.format) !== -1; }

    function paintStats(s) {
        if (!s) return;
        var put = function (k, v) { app.querySelectorAll('[data-stat="' + k + '"]').forEach(function (el) { el.textContent = v; }); };
        put('count', num(s.count)); put('bytes', bytes(s.bytes)); put('heavy', num(s.heavy)); put('unused', num(s.unused)); put('webp_pct', s.webp_pct + '%');
        put('formats', Object.keys(s.formats).slice(0, 3).map(function (k) { return k.toUpperCase(); }).join(' · ') || '—');
        var ring = app.querySelector('[data-webp-ring] .md-ring-value');
        if (ring) ring.setAttribute('stroke-dasharray', s.webp_pct + ' 100');
        $('mdFolders').querySelector('[data-folder-count="all"]').textContent = num(s.count);
        var bar = '';
        Object.keys(s.folders).forEach(function (k) {
            var el = $('mdFolders').querySelector('[data-folder-count="' + k + '"]');
            if (el) el.textContent = num(s.folders[k].count);
            var pct = s.bytes ? s.folders[k].bytes * 100 / s.bytes : 0;
            if (pct > 0) bar += '<button type="button" data-folder-jump="' + k + '" style="--c:' + esc((FOLDERS[k] || {}).color) + ';width:' + pct.toFixed(2) + '%" title="' + esc((FOLDERS[k] || {}).label) + ': ' + bytes(s.folders[k].bytes) + ' · ' + s.folders[k].count + ' files"></button>';
        });
        $('mdStorageBar').innerHTML = bar;
    }

    // ---------------------------------------------------------------- filters
    function setFolder(key) {
        state.folder = key;
        $('mdFolders').querySelectorAll('.md-folder').forEach(function (b) { b.classList.toggle('is-on', b.dataset.folder === key); });
        var on = $('mdFolders').querySelector('.is-on');
        if (on && on.scrollIntoView) on.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        load();
    }
    $('mdFolders').addEventListener('click', function (e) { var b = e.target.closest('[data-folder]'); if (b) setFolder(b.dataset.folder); });
    $('mdStorageBar').addEventListener('click', function (e) { var b = e.target.closest('[data-folder-jump]'); if (b) setFolder(b.dataset.folderJump); });

    var searchTimer;
    $('mdSearch').value = state.search;
    $('mdSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        var v = this.value.trim();
        searchTimer = setTimeout(function () { state.search = v; load(); }, 260);
    });
    [['mdFormat', 'format'], ['mdShape', 'shape'], ['mdUsage', 'usage'], ['mdSort', 'sort']].forEach(function (p) {
        var el = $(p[0]);
        el.value = state[p[1]];
        if (el.value !== state[p[1]]) { state[p[1]] = p[1] === 'sort' ? 'latest' : 'all'; el.value = state[p[1]]; }
        el.addEventListener('change', function () { state[p[1]] = el.value; load(); });
    });
    app.querySelectorAll('[data-quick-usage]').forEach(function (b) {
        b.addEventListener('click', function () {
            state.usage = b.dataset.quickUsage; $('mdUsage').value = state.usage;
            if (state.usage === 'heavy') { state.sort = 'largest'; $('mdSort').value = 'largest'; }
            load();
        });
    });
    $('mdClearFilters').addEventListener('click', function () {
        state.search = ''; state.format = state.shape = state.usage = 'all';
        $('mdSearch').value = ''; $('mdFormat').value = $('mdShape').value = $('mdUsage').value = 'all';
        load();
    });

    function setView(v) {
        state.view = v;
        grid.classList.toggle('is-list', v === 'list');
        app.querySelectorAll('[data-view]').forEach(function (b) { b.classList.toggle('is-on', b.dataset.view === v); });
        try { localStorage.setItem('md_view', JSON.stringify(v)); } catch (e) {}
    }
    app.querySelectorAll('[data-view]').forEach(function (b) { b.addEventListener('click', function () { setView(b.dataset.view); }); });
    setView(state.view);
    $('mdFolders').querySelectorAll('.md-folder').forEach(function (b) { b.classList.toggle('is-on', b.dataset.folder === state.folder); });

    $('mdMoreBtn').addEventListener('click', function () { state.page++; load(true); });
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting && !$('mdMore').hidden && !$('mdMoreBtn').classList.contains('is-busy')) { state.page++; load(true); }
        }, { rootMargin: '400px' }).observe($('mdMore'));
    }

    document.addEventListener('keydown', function (e) {
        var tag = (document.activeElement || {}).tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || document.querySelector('.modal.show')) return;
        if (e.key === '/' && !e.ctrlKey) { e.preventDefault(); $('mdSearch').focus(); }
        if ($('mdDrawer').classList.contains('show')) {
            if (e.key === 'ArrowRight') navDrawer(1);
            if (e.key === 'ArrowLeft') navDrawer(-1);
        }
        if (e.key === 'Escape' && selected.size && !$('mdDrawer').classList.contains('show')) clearSelection();
    });

    // ---------------------------------------------------------------- grid clicks & selection
    var lastClicked = null;
    grid.addEventListener('click', function (e) {
        var c = e.target.closest('.md-card');
        if (!c) return;
        var it = byId[c.dataset.id];
        if (e.target.closest('[data-select]')) {
            var box = e.target.closest('[data-select]');
            if (e.shiftKey && lastClicked) {
                var ids = items.map(function (x) { return x.id; });
                var a = ids.indexOf(lastClicked), b = ids.indexOf(it.id);
                ids.slice(Math.min(a, b), Math.max(a, b) + 1).forEach(function (id) { toggleSelect(byId[id], box.checked); });
            } else {
                toggleSelect(it, box.checked);
            }
            lastClicked = it.id;
            paintSelection();
            return;
        }
        if (e.target.closest('[data-copy]')) return copyText(absUrl(it.url));
        if (e.target.closest('[data-edit]')) return openEditor(it);
        if (e.target.closest('[data-open]')) {
            if (selected.size && !e.target.closest('.md-hover, .md-row-acts')) { toggleSelect(it, !selected.has(it.id)); paintSelection(); return; }
            openDrawer(it.id);
        }
    });

    // a picture that does not load shows a clean placeholder
    grid.addEventListener('error', function (e) {
        if (e.target.tagName === 'IMG') e.target.closest('.md-thumb').classList.add('is-broken');
    }, true);

    function toggleSelect(it, on) {
        if (!it) return;
        if (on) selected.set(it.id, it); else selected.delete(it.id);
    }
    function clearSelection() { selected.clear(); paintSelection(); }

    function paintSelection() {
        grid.querySelectorAll('.md-card').forEach(function (c) {
            var on = selected.has(c.dataset.id);
            c.classList.toggle('is-selected', on);
            var box = c.querySelector('[data-select]');
            if (box) box.checked = on;
        });
        var n = selected.size;
        app.classList.toggle('has-selection', n > 0);
        $('mdSelbar').hidden = n === 0;
        $('mdSelCount').textContent = num(n);
        $('mdSelAll').hidden = n >= state.count;
        $('mdSelAllCount').textContent = '(' + num(state.count) + ')';
        var page = $('mdCheckPage');
        var onPage = items.filter(function (it) { return selected.has(it.id); }).length;
        page.checked = items.length > 0 && onPage === items.length;
        page.indeterminate = onPage > 0 && onPage < items.length;
    }

    $('mdCheckPage').addEventListener('change', function () {
        var on = this.checked;
        items.forEach(function (it) { toggleSelect(it, on); });
        paintSelection();
    });
    $('mdSelAll').addEventListener('click', function () {
        request('GET', U.items + '?' + params({ ids: 1 })).then(function (d) {
            d.ids.forEach(function (id) { if (!selected.has(id)) selected.set(id, byId[id] || { id: id }); });
            paintSelection();
        }).catch(function (e) { toast(e.message, true); });
    });

    // ---------------------------------------------------------------- bulk actions
    function selectedIds() { return Array.from(selected.keys()); }

    $('mdSelbar').addEventListener('click', function (e) {
        var b = e.target.closest('[data-bulk], [data-move]');
        if (!b) return;
        if (b.dataset.move) return runBulk('move', selectedIds(), { folder: b.dataset.move }, 'Move ' + selected.size + ' file(s) to ' + FOLDERS[b.dataset.move].label + '?');
        var act = b.dataset.bulk;
        if (act === 'clear') return clearSelection();
        if (act === 'copy') {
            var urls = Array.from(selected.values()).filter(function (it) { return it.url; }).map(function (it) { return absUrl(it.url); });
            if (!urls.length) return toast('Links are copied for the files shown on screen', true);
            return copyText(urls.join('\n'), urls.length + ' links');
        }
        if (act === 'zip') return downloadZip(selectedIds());
        if (act === 'delete') return runBulk('delete', selectedIds(), {}, 'Delete ' + selected.size + ' file(s)? This cannot be undone.', true);
        if (act === 'webp') return openTool('webp', selectedIds());
        if (act === 'optimize') return openTool('optimize', selectedIds());
    });

    function runBulk(action, ids, extra, question, danger) {
        return confirmBox(action === 'delete' ? 'Delete files' : 'Are you sure?', question, danger, action === 'move' ? 'Move' : undefined).then(function (ok) {
            if (!ok) return;
            return sendBulk(action, ids, extra);
        });
    }

    function sendBulk(action, ids, extra) {
        var body = Object.assign({ action: action, ids: ids }, extra || {});
        return request('POST', U.bulk, body).then(function (d) {
            toast(d.message);
            selected.clear();
            load();
            return d;
        }).catch(function (e) {
            if (e.status === 409 && e.data && e.data.needs_force) {
                return confirmBox('Files in use', e.data.in_use + ' of these files are shown on the site (books, authors, banners…). Deleting them leaves broken images. Delete anyway?', true, 'Delete anyway')
                    .then(function (ok) { if (ok) return sendBulk(action, ids, Object.assign({}, extra, { force: true })); });
            }
            toast(e.message, true);
        });
    }

    function downloadZip(ids) {
        toast('Preparing ZIP…');
        request('POST', U.zip, { ids: ids }, { blob: true }).then(function (blob) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'media-' + new Date().toISOString().slice(0, 10) + '.zip';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(function () { URL.revokeObjectURL(a.href); }, 4000);
        }).catch(function (e) { toast(e.message, true); });
    }

    // ---------------------------------------------------------------- drawer
    var drawerId = null;
    function openDrawer(id) {
        var it = byId[id];
        if (!it) return;
        drawerId = id;
        var f = FOLDERS[it.folder] || {};
        var status = it.used === true ? ['is-used', 'fa-link', 'Used on the site'] : it.used === false ? ['is-unused', 'fa-link-slash', 'Not used anywhere'] : ['is-unknown', 'fa-circle-question', 'Use not tracked in this folder'];
        var url = absUrl(it.url);
        $('mdDrawerTitle').textContent = it.name;
        $('mdDrawerBody').innerHTML =
            '<div class="md-preview"><img src="' + esc(it.url) + '" alt="' + esc(it.title) + '"></div>' +
            '<div><h6 class="md-d-title">' + (it.link ? '<a href="' + esc(it.link) + '" target="_blank" rel="noopener">' + esc(it.title) + ' <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px"></i></a>' : esc(it.title)) + '</h6>' +
            '<p class="md-d-sub">' + esc(it.sub) + '</p></div>' +
            '<div><span class="md-status ' + status[0] + '"><i class="fa-solid ' + status[1] + '"></i>' + status[2] + '</span></div>' +
            '<dl class="md-facts">' +
                '<div><dt>Size</dt><dd>' + bytes(it.size) + '</dd></div>' +
                '<div><dt>Dimensions</dt><dd>' + (it.w ? it.w + ' × ' + it.h + ' px' : '—') + '</dd></div>' +
                '<div><dt>Format</dt><dd>' + esc(it.format.toUpperCase()) + '</dd></div>' +
                '<div><dt>Folder</dt><dd style="color:' + esc(f.color) + '">' + esc(f.label || '') + '</dd></div>' +
                '<div><dt>Updated</dt><dd title="' + esc(new Date(it.t * 1000).toLocaleString()) + '">' + ago(it.t) + '</dd></div>' +
                '<div><dt>File</dt><dd title="' + esc(it.name) + '">' + esc(it.name) + '</dd></div>' +
            '</dl>' +
            '<div class="md-copyrow"><input type="text" readonly value="' + esc(url) + '" aria-label="Link"><button type="button" class="md-btn" data-d="copy"><i class="fa-regular fa-copy"></i><span>Copy</span></button></div>' +
            '<div class="md-copyrow"><input type="text" readonly value="' + esc('<img src="' + url + '" alt="' + it.title + '" loading="lazy">') + '" aria-label="HTML"><button type="button" class="md-btn" data-d="copy-html"><i class="fa-solid fa-code"></i><span>HTML</span></button></div>' +
            '<div class="md-d-acts">' +
                (isEditable(it) ? '<button type="button" class="md-btn" data-d="edit"><i class="fa-solid fa-sliders"></i><span>Edit</span></button>' : '') +
                '<button type="button" class="md-btn" data-d="replace"><i class="fa-solid fa-arrow-right-arrow-left"></i><span>Replace</span></button>' +
                '<a class="md-btn" href="' + esc(url) + '" download="' + esc(it.name) + '"><i class="fa-solid fa-download"></i><span>Save</span></a>' +
                '<button type="button" class="md-btn" data-d="open-tab"><i class="fa-solid fa-up-right-from-square"></i><span>Open</span></button>' +
                (U.gd === '1' && ['jpg', 'png'].indexOf(it.format) !== -1 ? '<button type="button" class="md-btn" data-d="webp"><i class="fa-solid fa-bolt"></i><span>WebP</span></button>' : '') +
                '<button type="button" class="md-btn is-danger-soft" data-d="delete"><i class="fa-regular fa-trash-can"></i><span>Delete</span></button>' +
            '</div>' +
            '<div><div class="md-field"><span>Rename</span></div><div class="md-rename"><input type="text" class="md-input" id="mdRename" value="' + esc(it.name.replace(/\.[^.]+$/, '')) + '"' + (it.used === true ? ' disabled title="Used on the site"' : '') + '><button type="button" class="md-btn" data-d="rename"' + (it.used === true ? ' disabled' : '') + '>Save</button></div>' +
            (it.used === true ? '<p class="md-hint"><i class="fa-solid fa-shield-halved"></i>Used on the site, so its name and folder are locked. Replace keeps the same link.</p>' : '') + '</div>';
        if (window.bootstrap) bootstrap.Offcanvas.getOrCreateInstance($('mdDrawer')).show();
    }

    function navDrawer(step) {
        var i = items.findIndex(function (x) { return x.id === drawerId; });
        var next = items[i + step];
        if (next) {
            openDrawer(next.id);
            var c = grid.querySelector('[data-id="' + CSS.escape(next.id) + '"]');
            if (c) c.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else if (step > 0 && state.page < state.pages) {
            state.page++;
            load(true).then(function () { navDrawer(1); });
        }
    }

    $('mdDrawer').addEventListener('click', function (e) {
        var nav = e.target.closest('[data-nav]');
        if (nav) return navDrawer(parseInt(nav.dataset.nav, 10));
        var b = e.target.closest('[data-d]');
        if (!b) return;
        var it = byId[drawerId];
        if (!it) return;
        var act = b.dataset.d;
        if (act === 'copy') copyText(absUrl(it.url));
        else if (act === 'copy-html') copyText('<img src="' + absUrl(it.url) + '" alt="' + it.title.replace(/"/g, '&quot;') + '" loading="lazy">', 'HTML');
        else if (act === 'open-tab') window.open(absUrl(it.url), '_blank', 'noopener');
        else if (act === 'edit') openEditor(it);
        else if (act === 'replace') { replaceTarget = it; $('mdReplaceInput').value = ''; $('mdReplaceInput').click(); }
        else if (act === 'webp') { closeDrawer(); sendBulk('webp', [it.id], { replace_original: false }); }
        else if (act === 'delete') {
            runBulk('delete', [it.id], {}, 'Delete ' + it.name + '? This cannot be undone.', true).then(function (d) { if (d) closeDrawer(); });
        } else if (act === 'rename') {
            var name = $('mdRename').value.trim();
            if (!name) return;
            b.classList.add('is-busy');
            request('POST', U.rename, { id: it.id, name: name }).then(function (d) {
                toast(d.message);
                replaceItem(it.id, d.item);
                openDrawer(d.item.id);
            }).catch(function (err) { toast(err.message, true); }).then(function () { b.classList.remove('is-busy'); });
        }
    });
    $('mdDrawer').addEventListener('focusin', function (e) { if (e.target.matches('.md-copyrow input')) e.target.select(); });
    function closeDrawer() { if (window.bootstrap) bootstrap.Offcanvas.getOrCreateInstance($('mdDrawer')).hide(); }

    function replaceItem(oldId, item) {
        var i = items.findIndex(function (x) { return x.id === oldId; });
        if (i === -1) return;
        delete byId[oldId];
        items[i] = item; byId[item.id] = item;
        if (selected.has(oldId)) { selected.delete(oldId); selected.set(item.id, item); }
        var el = grid.querySelector('[data-id="' + CSS.escape(oldId) + '"]');
        if (el) el.outerHTML = card(item);
    }

    // replace
    var replaceTarget = null;
    $('mdReplaceInput').addEventListener('change', function () {
        var file = this.files[0], it = replaceTarget;
        if (!file || !it) return;
        confirmBox('Replace image', 'Put "' + file.name + '" in place of ' + it.name + '? The link stays the same, so every page shows the new picture.', false, 'Replace').then(function (ok) {
            if (!ok) return;
            var fd = new FormData();
            fd.append('id', it.id); fd.append('file', file);
            toast('Replacing…');
            request('POST', U.replace, fd).then(function (d) {
                toast(d.message);
                replaceItem(it.id, Object.assign({}, it, d.item, { title: it.title, sub: it.sub, link: it.link }));
                if (drawerId === it.id) openDrawer(d.item.id);
            }).catch(function (e) { toast(e.message, true); });
        });
    });

    // ---------------------------------------------------------------- upload
    var queue = [];
    var uploading = false;

    function openUpload(files) {
        var sel = $('mdUpFolder');
        if (state.folder !== 'all' && FOLDERS[state.folder]) sel.value = state.folder;
        updateMaxHint();
        modal('mdUpload').show();
        if (files && files.length) addFiles(files);
    }
    app.querySelectorAll('[data-open-upload]').forEach(function (b) { b.addEventListener('click', function () { openUpload(); }); });

    function updateMaxHint() {
        var opt = $('mdUpFolder').selectedOptions[0];
        $('mdUpMax').options[0].textContent = 'Folder default (' + (opt ? opt.dataset.max : '') + ' px)';
    }
    $('mdUpFolder').addEventListener('change', updateMaxHint);

    function addFiles(list) {
        Array.prototype.forEach.call(list, function (file) {
            if (!/^image\//.test(file.type)) return toast(file.name + ' is not an image', true);
            if (file.size > 10 * 1024 * 1024) return toast(file.name + ' is over 10 MB', true);
            queue.push({ file: file, state: 'wait', key: Math.random().toString(36).slice(2) });
        });
        paintQueue();
    }

    function paintQueue() {
        var wrap = $('mdQueue');
        wrap.innerHTML = queue.map(function (q) {
            if (!q.thumb) q.thumb = URL.createObjectURL(q.file);
            var icon = q.state === 'done' ? 'fa-circle-check' : q.state === 'error' ? 'fa-circle-exclamation' : q.state === 'up' ? 'fa-spinner fa-spin' : 'fa-xmark';
            return '<div class="md-q ' + (q.state === 'done' ? 'is-done' : q.state === 'error' ? 'is-error' : '') + '" data-key="' + q.key + '">' +
                '<img src="' + q.thumb + '" alt="">' +
                '<div><div class="md-q-name">' + esc(q.file.name) + '</div><div class="md-q-sub">' + esc(q.note || bytes(q.file.size)) + '</div><div class="md-q-bar"><i style="width:' + (q.pct || 0) + '%"></i></div></div>' +
                '<button type="button" class="md-icon md-q-state" data-remove="' + q.key + '"' + (q.state === 'wait' ? ' title="Remove"' : ' disabled') + '><i class="fa-solid ' + icon + '"></i></button>' +
            '</div>';
        }).join('');
        var waiting = queue.filter(function (q) { return q.state === 'wait'; }).length;
        $('mdUpStart').disabled = !waiting || uploading;
        $('mdQueueNote').textContent = queue.length ? waiting + ' waiting · ' + bytes(queue.reduce(function (n, q) { return n + q.file.size; }, 0)) : '';
    }

    $('mdQueue').addEventListener('click', function (e) {
        var b = e.target.closest('[data-remove]');
        if (!b || uploading) return;
        queue = queue.filter(function (q) { if (q.key === b.dataset.remove) { URL.revokeObjectURL(q.thumb); return false; } return true; });
        paintQueue();
    });

    var drop = $('mdDrop');
    $('mdFiles').addEventListener('change', function () { addFiles(this.files); this.value = ''; });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
    drop.addEventListener('drop', function (e) { e.stopPropagation(); addFiles(e.dataTransfer.files); });

    $('mdUpStart').addEventListener('click', function () {
        if (uploading) return;
        uploading = true;
        var btn = this;
        btn.classList.add('is-busy');
        var ok = 0, failed = 0;
        var next = function () {
            var q = queue.find(function (x) { return x.state === 'wait'; });
            if (!q) {
                uploading = false;
                btn.classList.remove('is-busy');
                paintQueue();
                toast(ok + ' uploaded' + (failed ? ', ' + failed + ' failed' : ''), failed && !ok);
                if (ok) {
                    var target = $('mdUpFolder').value;
                    if (state.folder !== 'all' && state.folder !== target) setFolder(target); else load();
                }
                return;
            }
            q.state = 'up'; paintQueue();
            sendFile(q).then(function (d) {
                q.state = 'done';
                var item = d.items && d.items[0];
                q.note = item ? (item.reused ? 'Already in the library' : bytes(q.file.size) + ' → ' + bytes(item.size) + (item.w ? ' · ' + item.w + '×' + item.h : '')) : 'Done';
                ok++;
            }).catch(function (e) {
                q.state = 'error'; q.note = (e.data && e.data.failed && e.data.failed[0] && e.data.failed[0].reason) || e.message; failed++;
            }).then(next);
        };
        next();
    });

    function sendFile(q) {
        return new Promise(function (resolve, reject) {
            var fd = new FormData();
            fd.append('files[]', q.file, q.file.name || 'pasted.png');
            fd.append('folder', $('mdUpFolder').value);
            fd.append('webp', $('mdUpWebp').checked ? '1' : '0');
            if ($('mdUpMax').value !== '') fd.append('max', $('mdUpMax').value);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', U.upload);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.onprogress = function (e) {
                if (!e.lengthComputable) return;
                q.pct = Math.round(e.loaded * 95 / e.total);
                var bar = $('mdQueue').querySelector('[data-key="' + q.key + '"] .md-q-bar i');
                if (bar) bar.style.width = q.pct + '%';
            };
            xhr.onload = function () {
                var d = {};
                try { d = JSON.parse(xhr.responseText); } catch (e) {}
                if (xhr.status >= 200 && xhr.status < 300) return resolve(d);
                var msg = d.message;
                if (d.errors) for (var k in d.errors) { msg = d.errors[k][0]; break; }
                var err = new Error(msg || (xhr.status === 413 ? 'File too large for the server' : xhr.status === 419 ? 'Session expired — reload' : 'Upload failed'));
                err.data = d; reject(err);
            };
            xhr.onerror = function () { reject(new Error('Network error')); };
            xhr.send(fd);
        });
    }

    $('mdUpload').addEventListener('hidden.bs.modal', function () {
        if (uploading) return;
        queue.filter(function (q) { return q.state !== 'wait'; }).forEach(function (q) { URL.revokeObjectURL(q.thumb); });
        queue = queue.filter(function (q) { return q.state === 'wait'; });
        paintQueue();
    });

    // drop anywhere / paste
    var dragDepth = 0;
    var hasFiles = function (e) { return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') !== -1; };
    document.addEventListener('dragenter', function (e) { if (!hasFiles(e) || document.querySelector('.modal.show')) return; dragDepth++; $('mdOverlay').hidden = false; });
    document.addEventListener('dragleave', function (e) { if (!hasFiles(e)) return; dragDepth = Math.max(0, dragDepth - 1); if (!dragDepth) $('mdOverlay').hidden = true; });
    document.addEventListener('dragover', function (e) { if (hasFiles(e)) e.preventDefault(); });
    document.addEventListener('drop', function (e) {
        if (!hasFiles(e)) return;
        e.preventDefault(); dragDepth = 0; $('mdOverlay').hidden = true;
        if (!document.querySelector('.modal.show')) openUpload(e.dataTransfer.files);
    });
    document.addEventListener('paste', function (e) {
        var tag = (document.activeElement || {}).tagName;
        if ((tag === 'INPUT' || tag === 'TEXTAREA') && !$('mdUpload').classList.contains('show')) return;
        var files = Array.prototype.filter.call((e.clipboardData || {}).files || [], function (f) { return /^image\//.test(f.type); });
        if (!files.length) return;
        e.preventDefault();
        if ($('mdUpload').classList.contains('show')) addFiles(files); else openUpload(files);
    });

    // ---------------------------------------------------------------- editor
    var ed = null;
    var canvas = $('mdCanvas');
    var ctx = canvas.getContext('2d');

    function openEditor(it) {
        var img = new Image();
        img.onload = function () {
            ed = { it: it, img: img, rot: 0, fx: 1, fy: 1, ratio: 0, tone: { brightness: 100, contrast: 100, saturate: 100 }, width: 0, mark: '', markPos: 'br', markOp: 60 };
            $('mdEdMeta').textContent = it.name + ' · ' + img.naturalWidth + '×' + img.naturalHeight + ' · ' + bytes(it.size);
            $('mdEdName').value = it.name.replace(/\.[^.]+$/, '') + '-edit';
            $('mdEdFolder').value = it.folder;
            $('mdEdFormat').value = 'webp';
            $('mdEdMark').value = '';
            $('mdEdRatio').querySelectorAll('button').forEach(function (b) { b.classList.toggle('is-on', b.dataset.ratio === '0'); });
            resetTone();
            var full = cropBox();
            $('mdEdW').value = full.w;
            ed.width = full.w;
            $('mdEdOverwrite').disabled = false;
            render();
            modal('mdEditor').show();
        };
        img.onerror = function () { toast('Could not open this image', true); };
        img.src = it.url;
    }

    function srcSize() {
        var swap = Math.abs(ed.rot) % 180 === 90;
        return { w: swap ? ed.img.naturalHeight : ed.img.naturalWidth, h: swap ? ed.img.naturalWidth : ed.img.naturalHeight };
    }
    function cropBox() {
        var s = srcSize();
        if (!ed.ratio) return { x: 0, y: 0, w: s.w, h: s.h };
        var w = s.w, h = Math.round(s.w / ed.ratio);
        if (h > s.h) { h = s.h; w = Math.round(s.h * ed.ratio); }
        return { x: Math.round((s.w - w) / 2), y: Math.round((s.h - h) / 2), w: w, h: h };
    }

    function render() {
        if (!ed) return;
        var s = srcSize(), box = cropBox();
        var outW = Math.max(16, Math.min(4000, ed.width || box.w));
        var scale = outW / box.w;
        var outH = Math.max(1, Math.round(box.h * scale));
        $('mdEdH').value = outH;

        // turn + flip onto a work canvas at full size
        var work = document.createElement('canvas');
        work.width = s.w; work.height = s.h;
        var w = work.getContext('2d');
        w.translate(s.w / 2, s.h / 2);
        w.rotate(ed.rot * Math.PI / 180);
        w.scale(ed.fx, ed.fy);
        w.drawImage(ed.img, -ed.img.naturalWidth / 2, -ed.img.naturalHeight / 2);

        canvas.width = outW; canvas.height = outH;
        ctx.save();
        ctx.filter = 'brightness(' + ed.tone.brightness + '%) contrast(' + ed.tone.contrast + '%) saturate(' + ed.tone.saturate + '%)';
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(work, box.x, box.y, box.w, box.h, 0, 0, outW, outH);
        ctx.restore();

        if (ed.mark) {
            var size = Math.max(12, Math.round(outW / 28));
            ctx.save();
            ctx.globalAlpha = ed.markOp / 100;
            ctx.font = '700 ' + size + 'px system-ui, sans-serif';
            ctx.fillStyle = '#fff';
            ctx.shadowColor = 'rgba(0,0,0,.55)'; ctx.shadowBlur = size / 3;
            var pad = size;
            var tw = ctx.measureText(ed.mark).width;
            var pos = { br: [outW - tw - pad, outH - pad], bl: [pad, outH - pad], tr: [outW - tw - pad, pad + size], c: [(outW - tw) / 2, outH / 2 + size / 3] }[ed.markPos];
            ctx.fillText(ed.mark, pos[0], pos[1]);
            ctx.restore();
        }
        $('mdEdOut').textContent = 'Result: ' + outW + ' × ' + outH + ' px';
    }

    function resetTone() {
        $('mdEditor').querySelectorAll('[data-tone]').forEach(function (r) { r.value = 100; r.nextElementSibling.textContent = '100%'; });
        if (ed) ed.tone = { brightness: 100, contrast: 100, saturate: 100 };
    }

    $('mdEdRatio').addEventListener('click', function (e) {
        var b = e.target.closest('[data-ratio]');
        if (!b || !ed) return;
        this.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-on', x === b); });
        ed.ratio = parseFloat(b.dataset.ratio);
        ed.width = cropBox().w; $('mdEdW').value = ed.width;
        render();
    });
    $('mdEditor').addEventListener('click', function (e) {
        if (!ed) return;
        var t = e.target.closest('[data-turn]'), f = e.target.closest('[data-flip]');
        if (t) { ed.rot = (ed.rot + parseInt(t.dataset.turn, 10) + 360) % 360; ed.width = cropBox().w; $('mdEdW').value = ed.width; render(); }
        if (f) { if (f.dataset.flip === 'x') ed.fx *= -1; else ed.fy *= -1; render(); }
    });
    $('mdEditor').querySelectorAll('[data-tone]').forEach(function (r) {
        r.addEventListener('input', function () { r.nextElementSibling.textContent = r.value + '%'; if (ed) { ed.tone[r.dataset.tone] = +r.value; render(); } });
    });
    $('mdEdResetTone').addEventListener('click', function () { resetTone(); render(); });
    $('mdEdW').addEventListener('input', function () { if (ed) { ed.width = parseInt(this.value, 10) || 0; render(); } });
    $('mdEdMark').addEventListener('input', function () { if (ed) { ed.mark = this.value.trim(); render(); } });
    $('mdEdMarkPos').addEventListener('change', function () { if (ed) { ed.markPos = this.value; render(); } });
    $('mdEdMarkOp').addEventListener('input', function () { this.nextElementSibling.textContent = this.value + '%'; if (ed) { ed.markOp = +this.value; render(); } });
    $('mdEdQuality').addEventListener('input', function () { this.nextElementSibling.textContent = this.value; });

    function saveEdit(mode) {
        if (!ed) return;
        var it = ed.it;
        var fmt = mode === 'overwrite' ? it.format : $('mdEdFormat').value;
        var mime = fmt === 'jpg' ? 'image/jpeg' : 'image/' + fmt;
        var quality = +$('mdEdQuality').value;
        var data = canvas.toDataURL(mime === 'image/webp' && !canvas.toDataURL('image/webp').startsWith('data:image/webp') ? 'image/png' : mime, quality / 100);
        var btn = mode === 'overwrite' ? $('mdEdOverwrite') : $('mdEdCopy');
        var go = function () {
            btn.classList.add('is-busy');
            request('POST', U.save, { data_url: data, mode: mode, id: it.id, name: $('mdEdName').value, folder: $('mdEdFolder').value, format: fmt, quality: quality }).then(function (d) {
                toast(d.message);
                modal('mdEditor').hide();
                if (mode === 'overwrite') {
                    replaceItem(it.id, Object.assign({}, it, d.item, { title: it.title, sub: it.sub, link: it.link, used: it.used }));
                    if (drawerId === it.id && $('mdDrawer').classList.contains('show')) openDrawer(d.item.id);
                } else {
                    load();
                }
            }).catch(function (e) { toast(e.message, true); }).then(function () { btn.classList.remove('is-busy'); });
        };
        if (mode === 'overwrite') {
            confirmBox('Overwrite original', 'Save over ' + it.name + '? Every page that shows it will show the edited picture. This cannot be undone.', true, 'Overwrite').then(function (ok) { if (ok) go(); });
        } else go();
    }
    $('mdEdCopy').addEventListener('click', function () { saveEdit('new_copy'); });
    $('mdEdOverwrite').addEventListener('click', function () { saveEdit('overwrite'); });

    // ---------------------------------------------------------------- tools
    var tool = null;

    function openTool(kind, ids) {
        tool = { kind: kind, ids: ids || null, running: false, stop: false };
        var body = $('mdToolBody');
        var run = $('mdToolRun');
        run.hidden = false; run.disabled = false; run.classList.remove('is-busy');
        $('mdToolNote').textContent = '';
        var scope = ids ? ids.length + ' selected file(s)' : (state.folder === 'all' ? 'the whole library' : (FOLDERS[state.folder] || {}).label + ' folder');

        if (kind === 'unused') {
            $('mdToolIcon').innerHTML = '<i class="fa-solid fa-broom"></i>';
            $('mdToolTitle').textContent = 'Unused files';
            $('mdToolSub').textContent = 'Images no book, author, publisher, e-book, user or setting uses';
            body.innerHTML = '<div class="md-empty" style="padding:30px"><i class="fa-solid fa-spinner fa-spin"></i><span>Checking the database…</span></div>';
            run.querySelector('span').textContent = 'Delete selected';
            run.disabled = true;
            modal('mdTool').show();
            request('GET', U.unused).then(function (d) {
                tool.unused = d.items;
                if (!d.count) {
                    body.innerHTML = '<div class="md-empty" style="padding:30px"><i class="fa-solid fa-circle-check" style="color:#059669"></i><strong>All clean</strong><span>Every image in ' + esc(d.folders.join(', ')) + ' is in use.</span></div>';
                    run.hidden = true;
                    return;
                }
                body.innerHTML = '<div class="md-note-box is-warn"><i class="fa-solid fa-triangle-exclamation"></i><div>Only ' + esc(d.folders.join(', ')) + ' are checked — other folders can be used inside page text, so they are never listed. Files added in the last 24 hours are skipped. Deleting cannot be undone.</div></div>' +
                    '<div class="md-resultbar"><label class="md-check"><input type="checkbox" id="mdUnusedAll" checked><span></span></label><span><b>' + num(d.count) + '</b> unused · ' + bytes(d.bytes) + (d.count > d.items.length ? ' · first ' + d.items.length + ' shown' : '') + '</span></div>' +
                    '<div class="md-unused-grid">' + d.items.map(function (it) {
                        return '<label class="md-unused" title="' + esc(it.name + ' · ' + bytes(it.size)) + '"><input type="checkbox" checked value="' + esc(it.id) + '"><img src="' + esc(it.url) + '" alt="" loading="lazy"><small>' + esc(it.name) + '</small></label>';
                    }).join('') + '</div>';
                var sync = function () {
                    var n = body.querySelectorAll('.md-unused input:checked').length;
                    run.disabled = !n;
                    $('mdToolNote').textContent = n + ' selected';
                };
                $('mdUnusedAll').addEventListener('change', function () { var on = this.checked; body.querySelectorAll('.md-unused input').forEach(function (c) { c.checked = on; }); sync(); });
                body.addEventListener('change', sync);
                sync();
            }).catch(function (e) { body.innerHTML = '<div class="md-empty"><span>' + esc(e.message) + '</span></div>'; });
            return;
        }

        var isWebp = kind === 'webp';
        $('mdToolIcon').innerHTML = '<i class="fa-solid ' + (isWebp ? 'fa-bolt' : 'fa-gauge-high') + '"></i>';
        $('mdToolTitle').textContent = isWebp ? 'Make WebP' : 'Optimize';
        $('mdToolSub').textContent = 'For ' + scope;
        body.innerHTML =
            '<div class="md-note-box"><i class="fa-solid fa-circle-info"></i><div>' + (isWebp
                ? 'Makes a lighter WebP copy of each JPG/PNG. Choose below whether the site should switch to the new files.'
                : 'Re-compresses JPG, PNG, WebP and SVG files and scales oversized pictures down to the folder size. Files only change when they get smaller; links stay the same.') + '</div></div>' +
            (isWebp ? '<label class="md-switch"><input type="checkbox" id="mdToolReplace" checked><span></span><b>Switch the site to WebP and remove the old file</b></label>' +
                '<p class="md-hint"><i class="fa-solid fa-shield-halved"></i>Books, authors, publishers, banners and settings are updated to the new file. Turn this off to keep both files.</p>' : '') +
            '<div class="md-tool-progress" id="mdToolProg" hidden><div class="md-tool-bar"><i></i></div>' +
            '<div class="md-tool-stats"><div><strong data-t="done">0</strong><span>Done</span></div><div><strong data-t="skipped">0</strong><span>Skipped</span></div><div><strong data-t="saved">0 B</strong><span>Saved</span></div></div></div>';
        run.querySelector('span').textContent = 'Start';
        modal('mdTool').show();
    }

    app.querySelectorAll('[data-tool]').forEach(function (b) { b.addEventListener('click', function () { openTool(b.dataset.tool); }); });

    $('mdToolRun').addEventListener('click', function () {
        if (!tool || tool.running) return;
        var run = this;
        if (tool.kind === 'unused') {
            var ids = Array.prototype.map.call($('mdToolBody').querySelectorAll('.md-unused input:checked'), function (c) { return c.value; });
            confirmBox('Delete unused files', 'Delete ' + ids.length + ' file(s)? This cannot be undone.', true).then(function (ok) {
                if (!ok) return;
                run.classList.add('is-busy');
                request('POST', U.bulk, { action: 'delete', ids: ids }).then(function (d) {
                    toast(d.message); modal('mdTool').hide(); load();
                }).catch(function (e) { toast(e.message, true); }).then(function () { run.classList.remove('is-busy'); });
            });
            return;
        }

        tool.running = true;
        run.classList.add('is-busy');
        $('mdToolProg').hidden = false;
        var replace = $('mdToolReplace') ? $('mdToolReplace').checked : false;
        var totals = { done: 0, skipped: 0, saved: 0 };
        // tools work on the open folder, not on the search filters
        var idsFor = function (format) {
            var p = { ids: 1 };
            if (state.folder !== 'all') p.folder = state.folder;
            if (format) p.format = format;
            return request('GET', U.items + '?' + new URLSearchParams(p)).then(function (d) { return d.ids; });
        };
        var getIds = tool.ids ? Promise.resolve(tool.ids)
            : tool.kind === 'webp' ? Promise.all([idsFor('jpg'), idsFor('png')]).then(function (r) { return r[0].concat(r[1]); })
            : idsFor(null);
        getIds.then(function (ids) {
            var queueIds = ids.slice(), total = ids.length;
            if (!total) { $('mdToolNote').textContent = 'Nothing to do'; return; }
            var step = function () {
                if (!queueIds.length || !$('mdTool').classList.contains('show')) return Promise.resolve();
                var chunk = queueIds.splice(0, 12);
                return request('POST', U.bulk, { action: tool.kind, ids: chunk, replace_original: replace }).then(function (d) {
                    totals.done += d.done; totals.skipped += d.skipped; totals.saved += d.saved;
                }).catch(function () { totals.skipped += chunk.length; }).then(function () {
                    var pct = Math.round((total - queueIds.length) * 100 / total);
                    $('mdToolProg').querySelector('.md-tool-bar i').style.width = pct + '%';
                    $('mdToolProg').querySelector('[data-t="done"]').textContent = num(totals.done);
                    $('mdToolProg').querySelector('[data-t="skipped"]').textContent = num(totals.skipped);
                    $('mdToolProg').querySelector('[data-t="saved"]').textContent = bytes(totals.saved);
                    $('mdToolNote').textContent = (total - queueIds.length) + ' of ' + total + ' checked';
                    return step();
                });
            };
            return step();
        }).then(function () {
            tool.running = false;
            run.classList.remove('is-busy');
            run.hidden = true;
            toast((tool.kind === 'webp' ? 'WebP' : 'Optimize') + ' finished · saved ' + bytes(totals.saved));
            selected.clear();
            load();
        }).catch(function (e) {
            tool.running = false; run.classList.remove('is-busy'); toast(e.message, true);
        });
    });

    // ---------------------------------------------------------------- start
    load();
})();
