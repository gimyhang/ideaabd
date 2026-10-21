/* Admin › Settings — AJAX save with conflict guard, unsaved-changes guard + draft recovery,
 * twin-field sync, search, live health, cache, backup, restorable history */
(function () {
    'use strict';

    var form = document.getElementById('systemSettingsForm');
    if (!form) return;

    var csrf = (form.querySelector('input[name="_token"]') || {}).value || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var $ = function (s, c) { return (c || document).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
    var esc = function (v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    };
    var JSON_HEADERS = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf };

    function toast(msg, bad, actions) {
        var el = document.createElement('div');
        el.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2000;max-width:min(440px,90vw);padding:10px 14px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 12px 30px -10px rgba(0,0,0,.5);display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:' + (bad ? '#dc2626' : '#0f172a');
        var t = document.createElement('span'); t.textContent = msg; el.appendChild(t);
        (actions || []).forEach(function (a) {
            var b = document.createElement('button');
            b.type = 'button'; b.textContent = a.label;
            b.style.cssText = 'border:1px solid rgba(255,255,255,.4);background:transparent;color:#fff;border-radius:8px;padding:3px 10px;font-size:12px;font-weight:700;cursor:pointer';
            b.addEventListener('click', function () { el.remove(); a.run(); });
            el.appendChild(b);
        });
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, actions && actions.length ? 15000 : (bad ? 4500 : 2600));
        return el;
    }

    /* ---------- dirty tracking ---------- */
    function serialize() {
        var parts = [];
        new FormData(form).forEach(function (v, k) {
            if (k === '_token' || k === '_version') return;
            parts.push(k + '=' + (v instanceof File ? 'file:' + v.name + ':' + v.size : v));
        });
        return parts.join('&');
    }
    var baseline = serialize(), forced = false, saving = false;
    var bar = document.getElementById('stSave');
    var barText = bar ? bar.querySelector('b') : null;
    // Other scripts on this page fill/normalise fields after DOMContentLoaded (sliders, colour pickers…);
    // take the baseline once they have settled so that does not count as an edit.
    window.addEventListener('load', function () { setTimeout(function () { baseline = serialize(); refresh(); offerDraft(); }, 500); });

    function isDirty() { return forced || serialize() !== baseline; }
    function refresh() {
        var dirty = isDirty();
        bar.classList.toggle('show', dirty);
        if (dirty && barText) {
            var tabs = $$('.nav-link.st-dirty').map(function (b) { return b.textContent.trim(); });
            barText.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> Unsaved' + (tabs.length ? ' · ' + esc(tabs.join(', ')) : '');
        }
        if (dirty) scheduleDraft();
    }
    var t;
    function schedule() { clearTimeout(t); t = setTimeout(refresh, 120); }

    function markTab(el) {
        var pane = el.closest && el.closest('.tab-pane'); if (!pane) return;
        var btn = document.querySelector('[data-bs-target="#' + pane.id + '"]');
        if (btn) btn.classList.add('st-dirty');
    }
    form.addEventListener('input', function (e) { markTab(e.target); schedule(); });
    form.addEventListener('change', function (e) { markTab(e.target); schedule(); });
    // The hero-slide and menu builders keep their data in the DOM (synced into hidden inputs on submit).
    ['tab-hero', 'tab-menu'].forEach(function (id) {
        var pane = document.getElementById(id);
        if (pane) pane.addEventListener('click', function (e) { if (e.target.closest('button, [role="button"], .btn')) { forced = true; markTab(pane); schedule(); } });
    });
    window.addEventListener('beforeunload', function (e) { if (!saving && isDirty()) { e.preventDefault(); e.returnValue = ''; } });

    function clean() {
        baseline = serialize(); forced = false;
        $$('.nav-link.st-dirty').forEach(function (b) { b.classList.remove('st-dirty'); });
        clearDraft(); refresh();
    }

    /* ---------- twin fields: one fact, one value ---------- */
    form.addEventListener('input', function (e) {
        var g = e.target.getAttribute && e.target.getAttribute('data-sync'); if (!g) return;
        $$('[data-sync="' + g + '"]', form).forEach(function (o) {
            if (o !== e.target && o.value !== e.target.value) { o.value = e.target.value; markTab(o); }
        });
    });

    /* ---------- draft recovery (survives a crash or closed tab) ---------- */
    var DRAFT_KEY = 'st-draft:' + location.pathname;
    var draftTimer;
    function draftFields() {
        var out = {};
        $$('input, select, textarea', form).forEach(function (el) {
            if (!el.name || el.name === '_token' || el.name === '_version' || el.disabled) return;
            var type = (el.type || '').toLowerCase();
            if (['file', 'hidden', 'password', 'submit', 'button'].indexOf(type) > -1) return;
            if (type === 'checkbox' || type === 'radio') { if (type === 'radio' && !el.checked) return; out[el.name + (type === 'checkbox' ? '#' + el.value : '')] = el.checked ? (type === 'radio' ? el.value : '1') : '0'; return; }
            out[el.name] = el.value;
        });
        return out;
    }
    function scheduleDraft() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(function () {
            try { localStorage.setItem(DRAFT_KEY, JSON.stringify({ v: (document.getElementById('stVersion') || {}).value || '', at: Date.now(), f: draftFields() })); } catch (e) { }
        }, 1500);
    }
    function clearDraft() { clearTimeout(draftTimer); try { localStorage.removeItem(DRAFT_KEY); } catch (e) { } }
    function applyDraft(d) {
        $$('input, select, textarea', form).forEach(function (el) {
            if (!el.name) return;
            var type = (el.type || '').toLowerCase();
            if (type === 'checkbox') { var k = el.name + '#' + el.value; if (k in d.f) el.checked = d.f[k] === '1'; }
            else if (type === 'radio') { if (el.name in d.f) el.checked = el.value === d.f[el.name]; }
            else if (el.name in d.f && ['file', 'hidden', 'password'].indexOf(type) === -1) el.value = d.f[el.name];
        });
        $$('input[type="range"]', form).forEach(function (r) { r.dispatchEvent(new Event('input', { bubbles: true })); });
        forced = true; schedule();
    }
    function offerDraft() {
        var raw; try { raw = localStorage.getItem(DRAFT_KEY); } catch (e) { return; }
        if (!raw) return;
        var d; try { d = JSON.parse(raw); } catch (e) { clearDraft(); return; }
        var ver = (document.getElementById('stVersion') || {}).value || '';
        if (!d || !d.f || d.v !== ver) { clearDraft(); return; }          // settings changed since: the draft is stale
        var cur = draftFields(), same = Object.keys(d.f).every(function (k) { return cur[k] === d.f[k]; });
        if (same) { clearDraft(); return; }
        var mins = Math.max(1, Math.round((Date.now() - d.at) / 60000));
        toast('Unsaved edits from ' + mins + ' min ago were found.', false, [
            { label: 'Restore', run: function () { applyDraft(d); toast('Draft restored — review and save.'); } },
            { label: 'Discard', run: clearDraft }
        ]);
    }

    /* ---------- save (AJAX) with conflict guard ---------- */
    form.addEventListener('invalid', function (e) {
        var pane = e.target.closest('.tab-pane');
        if (pane && !pane.classList.contains('active') && window.bootstrap) {
            var btn = document.querySelector('[data-bs-target="#' + pane.id + '"]');
            if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
        }
    }, true);

    function setBusy(on) {
        var buttons = [document.getElementById('stSaveBtn'), document.getElementById('stSaveTop')].filter(Boolean);
        buttons.forEach(function (b) {
            if (on) { b.dataset.html = b.innerHTML; b.disabled = true; b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving'; }
            else { b.disabled = false; if (b.dataset.html) b.innerHTML = b.dataset.html; }
        });
    }

    function send(force) {
        saving = true; setBusy(true);
        var fd = new FormData(form);
        if (force) fd.append('force', '1');
        return fetch(form.action, { method: 'POST', headers: JSON_HEADERS, body: fd })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, status: r.status, d: d }; }); })
            .then(function (res) {
                if (res.status === 409 && res.d.conflict) {
                    var v = document.getElementById('stVersion'); if (v && res.d.version) v.dataset.latest = res.d.version;
                    saving = false; setBusy(false);
                    if (confirm(res.d.message + '\n\nPress OK to overwrite their changes with yours, or Cancel to keep editing (reload the page to see theirs).')) { return send(true); }
                    toast('Not saved — someone else changed the settings.', true);
                    return;
                }
                if (res.status === 422 && res.d.errors) {
                    var first = Object.keys(res.d.errors)[0], field = form.elements[first];
                    if (field && field.closest) {
                        var pane = field.closest('.tab-pane'), btn = pane && document.querySelector('[data-bs-target="#' + pane.id + '"]');
                        if (btn && window.bootstrap) bootstrap.Tab.getOrCreateInstance(btn).show();
                        if (field.focus) field.focus();
                    }
                    throw new Error(res.d.errors[first][0]);
                }
                if (!res.ok || !res.d.success) throw new Error(res.d.message || 'Could not save (HTTP ' + res.status + ')');
                var ver = document.getElementById('stVersion'); if (ver && res.d.version) ver.value = res.d.version;
                toast(res.d.message || 'Settings saved.');
                clean(); historyStale = true; if ($('#tab-system.active')) loadHistory();
                if (res.d.reload) { saving = true; setTimeout(function () { location.reload(); }, 700); }
            })
            .catch(function (err) { toast(err.message || 'Could not save', true); })
            .finally(function () { if (saving) { saving = false; setBusy(false); } });
    }

    form.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        e.preventDefault();
        if (!saving) send(false);
    });
    var saveBtn = document.getElementById('stSaveBtn');
    if (saveBtn) saveBtn.addEventListener('click', function () { form.requestSubmit(); });
    var discard = document.getElementById('stDiscard');
    if (discard) discard.addEventListener('click', function () { forced = false; baseline = serialize(); clearDraft(); location.reload(); });

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); form.requestSubmit(); }
        if (e.key === '/' && !/input|textarea|select/i.test((document.activeElement || {}).tagName || '') && !(document.activeElement || {}).isContentEditable) {
            var s = document.getElementById('stSearch'); if (s) { e.preventDefault(); s.focus(); s.select(); }
        }
    });

    /* ---------- maintenance switch needs a second look ---------- */
    var maint = document.getElementById('maintSwitch');
    if (maint) maint.addEventListener('change', function () {
        if (maint.checked && !confirm('Turn maintenance mode on? Visitors will see the maintenance page after you save (admins keep access).')) {
            maint.checked = false; schedule();
        }
    });

    /* ---------- search ---------- */
    var search = document.getElementById('stSearch'), count = document.getElementById('stSearchCount');
    var NODES = 'label, .form-label, h4, h5, h6, small, th, .form-text, legend, .nav-link span';
    function hitText(el) { return (el.textContent + ' ' + (el.getAttribute('placeholder') || '')).toLowerCase(); }

    function runSearch() {
        var q = search.value.trim().toLowerCase(), total = 0, firstPane = null, activePane = $('.tab-pane.active', form);
        $$('.st-hit').forEach(function (n) { n.classList.remove('st-hit'); });
        $$('[data-bs-toggle="pill"]').forEach(function (b) { b.classList.remove('st-tab-hit', 'st-tab-none'); b.removeAttribute('data-hits'); });
        if (!q) { count.hidden = true; return; }

        $$('.tab-pane', form).forEach(function (pane) {
            var hits = 0;
            $$(NODES, pane).forEach(function (n) {
                if (n.closest('.nav')) return;
                if (n.children.length > 4) return;
                if (hitText(n).indexOf(q) > -1) { n.classList.add('st-hit'); hits++; }
            });
            $$('input[placeholder], textarea[placeholder]', pane).forEach(function (n) {
                if ((n.getAttribute('placeholder') || '').toLowerCase().indexOf(q) > -1) { n.classList.add('st-hit'); hits++; }
            });
            var btn = document.querySelector('[data-bs-target="#' + pane.id + '"]');
            if (btn) {
                if (hits) { btn.classList.add('st-tab-hit'); btn.setAttribute('data-hits', hits); } else { btn.classList.add('st-tab-none'); }
            }
            total += hits;
            if (hits && !firstPane) firstPane = pane;
        });

        count.hidden = false; count.textContent = total ? total + ' found' : 'none';
        var activeHasHits = activePane && $('.st-hit', activePane);
        if (firstPane && !activeHasHits && window.bootstrap) {
            var b = document.querySelector('[data-bs-target="#' + firstPane.id + '"]');
            if (b) bootstrap.Tab.getOrCreateInstance(b).show();
        }
    }
    var st;
    if (search) {
        search.addEventListener('input', function () { clearTimeout(st); st = setTimeout(runSearch, 160); });
        search.addEventListener('keydown', function (e) { if (e.key === 'Escape') { search.value = ''; runSearch(); search.blur(); } });
    }

    /* ---------- health ---------- */
    var healthBtn = document.getElementById('stHealthRefresh');
    var loadedAt = 0;
    function renderHealth(d) {
        var icons = { ok: 'fa-check', warn: 'fa-exclamation', bad: 'fa-xmark' };
        document.getElementById('stHealth').innerHTML = d.checks.map(function (c) {
            return '<div class="st-check ' + esc(c.state) + '"><span class="st-dot"><i class="fa-solid ' + icons[c.state] + '"></i></span><div><b>' + esc(c.label) + '</b><span class="d">' + esc(c.detail) + '</span></div></div>';
        }).join('');
        var sc = document.getElementById('stScore');
        sc.textContent = d.score + '/100'; sc.className = 'st-score ' + (d.score >= 85 ? 'ok' : (d.score >= 60 ? 'warn' : 'bad'));
        document.getElementById('stMeta').innerHTML = Object.keys(d.meta).map(function (k) { return '<span><b>' + esc(k) + '</b> ' + esc(d.meta[k]) + '</span>'; }).join('');
        loadedAt = Date.now();
    }
    function loadHealth() {
        if (!healthBtn) return;
        var icon = healthBtn.querySelector('i'); if (icon) icon.classList.add('fa-spin');
        fetch(healthBtn.dataset.healthUrl, { headers: JSON_HEADERS })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); }).then(renderHealth)
            .catch(function () { document.getElementById('stHealth').innerHTML = '<div class="text-danger small">Could not load health checks.</div>'; })
            .finally(function () { if (icon) icon.classList.remove('fa-spin'); });
    }
    if (healthBtn) {
        healthBtn.addEventListener('click', loadHealth);
        var sysBtn = document.getElementById('tab-system-btn');
        if (sysBtn) sysBtn.addEventListener('shown.bs.tab', function () { if (Date.now() - loadedAt > 20000) loadHealth(); if (historyStale) loadHistory(); });
        if ($('#tab-system.active')) loadHealth();
        setInterval(function () { if (!document.hidden && $('#tab-system.active') && Date.now() - loadedAt > 60000) loadHealth(); }, 30000);
    }

    /* ---------- history (restorable changes) ---------- */
    var histBtn = document.getElementById('stHistoryRefresh'), histBox = document.getElementById('stHistory');
    var historyStale = true;
    function renderHistory(d) {
        if (!d.items.length) { histBox.innerHTML = '<div class="text-muted small py-3">None yet</div>'; return; }
        histBox.innerHTML = d.items.map(function (it) {
            return '<div class="st-hist"><div class="st-hist-main"><b>' + esc(it.label) + '</b> <span class="text-muted">by ' + esc(it.by) + '</span>'
                + '<div class="st-hist-areas">' + it.areas.map(function (a) { return '<span class="st-chip">' + esc(a) + '</span>'; }).join('') + '<span class="text-muted small">' + it.count + ' setting' + (it.count === 1 ? '' : 's') + '</span></div></div>'
                + '<div class="st-hist-when" title="' + esc(it.when) + '">' + esc(it.ago) + '</div>'
                + '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-restore="' + esc(it.id) + '"' + (it.restorable ? '' : ' disabled title="Too large to restore"') + '><i class="fa-solid fa-rotate-left me-1"></i> Restore</button></div>';
        }).join('');
    }
    function loadHistory() {
        if (!histBtn) return;
        fetch(histBtn.dataset.url, { headers: JSON_HEADERS })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) { renderHistory(d); historyStale = false; })
            .catch(function () { histBox.innerHTML = '<div class="text-danger small">Could not load the history.</div>'; });
    }
    if (histBtn) {
        histBtn.addEventListener('click', loadHistory);
        if ($('#tab-system.active')) loadHistory();
        histBox.addEventListener('click', function (e) {
            var b = e.target.closest('[data-restore]'); if (!b) return;
            if (isDirty() && !confirm('You have unsaved edits that will be lost when the page reloads. Restore anyway?')) return;
            if (!confirm('Restore the settings as they were before this change?')) return;
            b.disabled = true;
            fetch(histBtn.dataset.restoreUrl + '/' + b.dataset.restore + '/restore', { method: 'POST', headers: JSON_HEADERS })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.d.success) throw new Error(res.d.message || 'Restore failed');
                    toast(res.d.message); forced = false; baseline = serialize(); clearDraft(); saving = true; setTimeout(function () { location.reload(); }, 700);
                })
                .catch(function (err) { toast(err.message, true); b.disabled = false; });
        });
    }

    /* ---------- cache ---------- */
    function clearCache(type, btn) {
        var wrap = document.getElementById('stCache');
        if (type === 'all' && !confirm('Clear every cache (views, config, routes and data)? The next page loads may be slower.')) return;
        var fd = new FormData(); fd.append('type', type);
        if (btn) btn.disabled = true;
        fetch(wrap.dataset.url, { method: 'POST', headers: JSON_HEADERS, body: fd })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) { toast(res.d.message || (res.ok ? 'Cache cleared.' : 'Failed'), !res.ok); if (res.ok) loadedAt = 0; })
            .catch(function () { toast('Could not clear the cache.', true); })
            .finally(function () { if (btn) btn.disabled = false; });
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-cache], [data-cache-quick]');
        if (b) clearCache(b.dataset.cache || b.dataset.cacheQuick, b);
    });

    /* ---------- backup import ---------- */
    var imp = document.getElementById('stImport'), impBtn = document.getElementById('stImportBtn');
    if (imp && impBtn) impBtn.addEventListener('click', function () {
        if (!imp.files.length) { toast('Choose a settings file first.', true); return; }
        if (!confirm('Replace the stored settings with the contents of this file?')) return;
        var fd = new FormData(); fd.append('file', imp.files[0]);
        impBtn.disabled = true;
        fetch(imp.dataset.url, { method: 'POST', headers: JSON_HEADERS, body: fd })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.d.success) throw new Error(res.d.message || 'Import failed');
                toast(res.d.message); saving = true; setTimeout(function () { location.reload(); }, 900);
            })
            .catch(function (err) { toast(err.message, true); })
            .finally(function () { impBtn.disabled = false; });
    });
})();
