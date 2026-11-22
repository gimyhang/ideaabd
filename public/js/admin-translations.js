/* /admin/translations — live filters, inline save, suggest, sync and fill */
(function () {
    'use strict';

    var app = document.getElementById('trApp');
    if (!app) return;

    var results = document.getElementById('trResults');
    var search = document.getElementById('trSearch');
    var canEdit = app.dataset.canEdit === '1';
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var params = new URLSearchParams(location.search);
    var state = {
        group: params.get('group') || '',
        search: params.get('search') || '',
        status: params.get('status') || 'all',
        page: parseInt(params.get('page') || '1', 10) || 1
    };
    var loadSeq = 0;

    // ---------- helpers ----------
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function request(method, url, body) {
        var opts = {
            method: method,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin'
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                if (!res.ok) {
                    var err = new Error(firstError(data) || (res.status === 403 ? 'You do not have permission' : res.status === 419 ? 'Session expired — reload the page' : 'Something went wrong'));
                    err.status = res.status; err.data = data;
                    throw err;
                }
                return data;
            });
        });
    }

    function firstError(data) {
        if (data && data.errors) {
            for (var k in data.errors) if (data.errors[k] && data.errors[k][0]) return data.errors[k][0];
        }
        return data && data.message;
    }

    var toastTimer;
    function toast(msg, isError) {
        var el = document.getElementById('trToast');
        if (!el) return;
        el.innerHTML = '<i class="fa-solid ' + (isError ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i><span>' + esc(msg) + '</span>';
        el.classList.toggle('is-error', !!isError);
        el.hidden = false;
        el.style.animation = 'none'; void el.offsetWidth; el.style.animation = '';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.hidden = true; }, 3200);
    }

    function ask(text, danger) {
        if (window.SwalConfirm) {
            return window.SwalConfirm({
                title: danger ? 'Delete key' : 'Reset text', text: text, icon: danger ? 'warning' : 'question',
                confirmButtonText: danger ? 'Delete' : 'Reset', cancelButtonText: 'Cancel', isDanger: !!danger
            }).then(function (r) { return !!(r && r.isConfirmed); });
        }
        if (window.Swal && Swal.fire) {
            return Swal.fire({
                text: text, icon: danger ? 'warning' : 'question', showCancelButton: true,
                confirmButtonText: danger ? 'Delete' : 'Yes', cancelButtonText: 'Cancel',
                confirmButtonColor: danger ? '#e11d48' : '#2563eb'
            }).then(function (r) { return r.isConfirmed; });
        }
        return Promise.resolve(window.confirm(text));
    }

    function autosize(t) {
        t.style.height = 'auto';
        t.style.height = Math.max(t.scrollHeight, 21) + 'px';
    }

    function updateUrl(url) {
        var d = new URLSearchParams();
        if (state.group) d.set('group', state.group);
        if (state.search) d.set('search', state.search);
        if (state.status !== 'all') d.set('status', state.status);
        if (state.page > 1) d.set('page', state.page);
        var q = d.toString();
        history.replaceState(null, '', url + (q ? '?' + q : ''));
        return d;
    }

    // ---------- stats ----------
    function setStats(s) {
        if (!s) return;
        var ready = s.total ? Math.floor((s.total - s.missing) * 100 / s.total) : 100;
        var put = function (k, v) { var el = app.querySelector('[data-stat="' + k + '"]'); if (el) el.textContent = Number(v).toLocaleString('en'); };
        put('total', s.total); put('groups', s.groups); put('custom', s.custom); put('missing', s.missing); put('ready', ready);
        var ring = app.querySelector('[data-ring]');
        if (ring) {
            ring.querySelector('.tr-ring-value').setAttribute('stroke-dasharray', ready + ' 100');
            ring.classList.toggle('is-good', ready >= 90);
            ring.classList.toggle('is-mid', ready >= 50 && ready < 90);
        }
        Object.keys(s.langs || {}).forEach(function (loc) {
            var bar = app.querySelector('[data-lang-bar="' + loc + '"]');
            if (!bar) return;
            bar.querySelector('[data-pct]').textContent = s.langs[loc].percent;
            bar.querySelector('[data-count]').textContent = s.langs[loc].count;
            bar.querySelector('.tr-meter i').style.width = s.langs[loc].percent + '%';
        });
        var all = app.querySelector('.tr-chip[data-group=""] b');
        if (all) all.textContent = s.total;
    }

    function setGroups(groups) {
        var wrap = document.getElementById('trGroups');
        if (!wrap || !groups) return;
        var html = '<button type="button" data-group="" class="tr-chip' + (state.group === '' ? ' is-on' : '') + '"><span>All</span><b>' +
            groups.reduce(function (n, g) { return n + g.total; }, 0) + '</b></button>';
        groups.forEach(function (g) {
            html += '<button type="button" data-group="' + esc(g.name) + '" class="tr-chip' + (state.group === g.name ? ' is-on' : '') +
                '" style="--p: ' + g.percent + '%" title="' + g.done + ' of ' + g.total + ' done"><span>' + esc(g.name) + '</span><b>' + g.total + '</b><i class="tr-chip-meter"></i></button>';
        });
        wrap.innerHTML = html;
    }

    // ---------- list loading ----------
    function load() {
        var seq = ++loadSeq;
        var d = updateUrl(app.dataset.index);
        d.set('partial', '1');
        results.classList.add('is-loading');
        return request('GET', app.dataset.index + '?' + d.toString()).then(function (data) {
            if (seq !== loadSeq) return;
            results.innerHTML = data.rows;
            setStats(data.stats);
            setGroups(data.groups);
            prepare(results);
        }).catch(function (e) {
            if (seq === loadSeq) toast(e.message, true);
        }).then(function () {
            if (seq === loadSeq) results.classList.remove('is-loading');
        });
    }

    function prepare(root) {
        root.querySelectorAll('.tr-cell textarea').forEach(function (t) {
            t.dataset.orig = t.value;
            autosize(t);
        });
    }

    var searchTimer;
    if (search) {
        search.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                state.search = search.value.trim();
                state.page = 1;
                load();
            }, 280);
        });
        document.addEventListener('keydown', function (e) {
            var tag = (document.activeElement || {}).tagName;
            if (e.key === '/' && tag !== 'INPUT' && tag !== 'TEXTAREA' && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                search.focus();
            }
        });
    }

    document.getElementById('trStatus').addEventListener('click', function (e) {
        var b = e.target.closest('[data-status]');
        if (!b) return;
        state.status = b.dataset.status;
        state.page = 1;
        this.querySelectorAll('[data-status]').forEach(function (x) {
            x.classList.toggle('is-on', x === b);
            x.setAttribute('aria-selected', x === b ? 'true' : 'false');
        });
        load();
    });

    document.getElementById('trGroups').addEventListener('click', function (e) {
        var b = e.target.closest('[data-group]');
        if (!b) return;
        state.group = b.dataset.group;
        state.page = 1;
        this.querySelectorAll('.tr-chip').forEach(function (x) { x.classList.toggle('is-on', x === b); });
        load();
    });

    // ---------- row actions ----------
    results.addEventListener('click', function (e) {
        var link = e.target.closest('.tr-pager a[href]');
        if (link) {
            e.preventDefault();
            var p = new URL(link.href, location.href).searchParams.get('page');
            state.page = parseInt(p || '1', 10) || 1;
            load().then(function () { app.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
            return;
        }

        var copy = e.target.closest('[data-copy]');
        if (copy) {
            var text = copy.dataset.copy;
            (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject()).then(function () {
                copy.classList.add('is-done');
                copy.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(function () { copy.classList.remove('is-done'); copy.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1400);
            }).catch(function () { toast(text); });
            return;
        }

        if (!canEdit) return;
        var row = e.target.closest('.tr-row');
        if (!row) return;

        if (e.target.closest('[data-suggest]')) {
            suggestRow(row, e.target.closest('[data-suggest]'));
        } else if (e.target.closest('[data-reset]')) {
            ask('Remove the custom text of ' + row.dataset.full + ' and use the file text again?').then(function (yes) {
                if (!yes) return;
                request('PUT', updateUrl_(row), { text_bn: '', text_en: '', text_ar: '' }).then(function (d) {
                    toast('Reset ' + row.dataset.full);
                    setStats(d.stats);
                    load();
                }).catch(function (err) { toast(err.message, true); });
            });
        } else if (e.target.closest('[data-delete]')) {
            ask('Delete the key ' + row.dataset.full + '?', true).then(function (yes) {
                if (!yes) return;
                request('DELETE', updateUrl_(row)).then(function (d) {
                    row.style.transition = 'opacity .2s, transform .2s';
                    row.style.opacity = '0'; row.style.transform = 'translateX(12px)';
                    setTimeout(function () { load(); }, 200);
                    setStats(d.stats);
                    toast('Deleted ' + row.dataset.full);
                }).catch(function (err) { toast(err.message, true); });
            });
        }
    });

    function updateUrl_(row) {
        return app.dataset.update.replace('__ID__', encodeURIComponent(row.dataset.id));
    }

    function suggestRow(row, btn) {
        var bn = row.querySelector('textarea[data-lang="bn"]');
        var en = row.querySelector('textarea[data-lang="en"]');
        var text = (bn.value.trim() || bn.placeholder || '').trim();
        if (!text || /^Type /.test(text)) { toast('Type the Bangla text first', true); return; }
        btn.classList.add('is-busy');
        request('POST', app.dataset.suggest, { text_bn: text, id: parseInt(row.dataset.id, 10) }).then(function (d) {
            en.value = d.text_en;
            autosize(en);
            save(en);
            toast('Suggested from ' + (d.source === 'memory' ? 'saved text' : 'the glossary'));
        }).catch(function (err) {
            toast(err.status === 404 ? 'No suggestion — type the English text' : err.message, true);
            en.focus();
        }).then(function () { btn.classList.remove('is-busy'); });
    }

    // ---------- inline save ----------
    var timers = new WeakMap();

    function setState(t, cls, text) {
        var s = t.closest('.tr-cell').querySelector('.tr-state');
        if (!s) return;
        s.className = 'tr-state' + (cls ? ' ' + cls : '');
        s.textContent = text || '';
        if (cls === 'is-saved') setTimeout(function () { if (s.textContent === text) { s.textContent = ''; s.className = 'tr-state'; } }, 1800);
    }

    function save(t) {
        clearTimeout(timers.get(t));
        if (t.value === t.dataset.orig || t.readOnly) return;
        var row = t.closest('.tr-row');
        var lang = t.dataset.lang;
        var value = t.value;
        var body = {}; body['text_' + lang] = value;
        setState(t, 'is-saving', 'Saving…');
        request('PUT', updateUrl_(row), body).then(function (d) {
            t.dataset.orig = value;
            setState(t, 'is-saved', 'Saved');
            paintRow(row, d.row);
            setStats(d.stats);
        }).catch(function (err) {
            setState(t, 'is-error', 'Not saved');
            toast(err.message, true);
        });
    }

    function paintRow(row, data) {
        if (!data) return;
        row.classList.toggle('is-missing', !data.done);
        Object.keys(data.langs).forEach(function (loc) {
            var l = data.langs[loc];
            var cell = row.querySelector('.tr-cell[data-lang="' + loc + '"]');
            if (!cell) return;
            cell.classList.toggle('is-saved', l.saved !== '');
            cell.classList.toggle('is-empty', l.value === '' && loc !== 'ar');
            cell.querySelector('.tr-src').textContent = l.saved !== '' ? 'Custom' : (l.default !== '' ? 'Default' : (loc === 'ar' ? 'Optional' : 'Empty'));
        });
        var badges = row.querySelector('.tr-badges');
        if (badges) {
            var h = '';
            if (data.custom) h += '<span class="tr-badge is-custom">Custom</span>';
            if (!data.done) h += '<span class="tr-badge is-missing">Missing</span>';
            if (data.warn) h += '<span class="tr-badge is-warn" title="English text lacks a :placeholder used in Bangla">Check :vars</span>';
            (data.vars || []).forEach(function (v) { h += '<span class="tr-var">:' + esc(v) + '</span>'; });
            badges.innerHTML = h;
        }
        var acts = row.querySelector('.tr-row-acts');
        if (acts && data.in_file) {
            acts.innerHTML = data.custom ? '<button type="button" class="tr-icon-btn" data-reset title="Reset to the file text"><i class="fa-solid fa-rotate-left"></i></button>' : '';
        }
    }

    results.addEventListener('input', function (e) {
        var t = e.target;
        if (!t.matches('.tr-cell textarea')) return;
        autosize(t);
        if (t.value === t.dataset.orig) { setState(t, '', ''); clearTimeout(timers.get(t)); return; }
        setState(t, '', 'Edited');
        clearTimeout(timers.get(t));
        timers.set(t, setTimeout(function () { save(t); }, 1200));
    });

    results.addEventListener('focusout', function (e) {
        if (e.target.matches('.tr-cell textarea')) save(e.target);
    });

    results.addEventListener('keydown', function (e) {
        var t = e.target;
        if (!t.matches('.tr-cell textarea')) return;
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey || !e.shiftKey)) {
            e.preventDefault();
            save(t);
        } else if (e.key === 'Escape') {
            t.value = t.dataset.orig;
            autosize(t);
            clearTimeout(timers.get(t));
            setState(t, '', '');
        }
    });

    window.addEventListener('beforeunload', function (e) {
        var dirty = Array.prototype.some.call(results.querySelectorAll('.tr-cell textarea'), function (t) { return t.value !== t.dataset.orig; });
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    // ---------- toolbar actions ----------
    app.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.dataset.action;
            btn.classList.add('is-busy');
            request('POST', app.dataset[action], {}).then(function (d) {
                toast(d.message);
                return load();
            }).catch(function (err) { toast(err.message, true); }).then(function () {
                btn.classList.remove('is-busy');
            });
        });
    });

    // ---------- add modal ----------
    var form = document.getElementById('trAddForm');
    if (form) {
        var code = document.getElementById('trAddCode');
        var errBox = document.getElementById('trAddError');
        var showCode = function () {
            code.textContent = "__('" + (form.group.value.trim() || 'group') + '.' + (form.key.value.trim() || '…') + "')";
        };
        form.group.addEventListener('input', showCode);
        form.key.addEventListener('input', function () {
            // keys: lower case words joined by _
            var v = form.key.value.replace(/\s+/g, '_');
            if (v !== form.key.value) form.key.value = v;
            showCode();
        });
        showCode();

        form.querySelector('[data-add-suggest]').addEventListener('click', function () {
            var b = this;
            var text = form.text_bn.value.trim();
            if (!text) { form.text_bn.focus(); toast('Type the Bangla text first', true); return; }
            b.classList.add('is-busy');
            request('POST', app.dataset.suggest, { text_bn: text }).then(function (d) {
                form.text_en.value = d.text_en;
            }).catch(function (err) {
                toast(err.status === 404 ? 'No suggestion — type the English text' : err.message, true);
                form.text_en.focus();
            }).then(function () { b.classList.remove('is-busy'); });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submit = form.querySelector('[type="submit"]');
            errBox.hidden = true;
            form.querySelectorAll('.is-invalid').forEach(function (x) { x.classList.remove('is-invalid'); });
            submit.classList.add('is-busy');
            request('POST', app.dataset.store, {
                group: form.group.value.trim(), key: form.key.value.trim(),
                text_bn: form.text_bn.value, text_en: form.text_en.value, text_ar: form.text_ar.value
            }).then(function (d) {
                toast(d.message);
                ['key', 'text_bn', 'text_en', 'text_ar'].forEach(function (n) { form[n].value = ''; });
                showCode();
                var modal = window.bootstrap && bootstrap.Modal.getInstance(document.getElementById('trAddModal'));
                if (modal) modal.hide();
                state.group = form.group.value.trim();
                state.search = ''; if (search) search.value = '';
                state.status = 'all'; state.page = 1;
                document.querySelectorAll('#trStatus [data-status]').forEach(function (x) { x.classList.toggle('is-on', x.dataset.status === 'all'); });
                load();
            }).catch(function (err) {
                errBox.textContent = err.message;
                errBox.hidden = false;
                var fields = (err.data && err.data.errors) || {};
                Object.keys(fields).forEach(function (n) { if (form[n]) form[n].classList.add('is-invalid'); });
            }).then(function () { submit.classList.remove('is-busy'); });
        });

        document.getElementById('trAddModal').addEventListener('shown.bs.modal', function () { form.key.focus(); });
    }

    // first paint
    prepare(results);
    var ring = app.querySelector('[data-ring]');
    if (ring) {
        var v = ring.querySelector('.tr-ring-value');
        var target = v.getAttribute('stroke-dasharray');
        var ready = parseInt(target, 10);
        ring.classList.toggle('is-good', ready >= 90);
        ring.classList.toggle('is-mid', ready >= 50 && ready < 90);
        v.setAttribute('stroke-dasharray', '0 100');
        requestAnimationFrame(function () { requestAnimationFrame(function () { v.setAttribute('stroke-dasharray', target); }); });
    }
})();
