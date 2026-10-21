/* Admin › Roles & Permissions
 * One source of truth: `state` (role slug -> Set of permission ids).
 * Both the editor and the compare table read/write it, so a change can never be
 * overwritten by a second copy of the same checkbox. */
(function () {
    'use strict';

    var root = document.getElementById('iamRoot');
    var dataEl = document.getElementById('iamData');
    if (!root || !dataEl) return;

    var D = JSON.parse(dataEl.textContent);
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var $ = function (s, c) { return (c || root).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || root).querySelectorAll(s)); };
    var esc = function (v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    function toast(msg, bad) {
        var el = document.createElement('div');
        el.textContent = msg;
        el.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2000;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 12px 30px -10px rgba(0,0,0,.5);background:' + (bad ? '#dc2626' : '#0f172a');
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 2600);
    }

    /* ---------- state ---------- */
    var allIds = [];
    D.modules.forEach(function (m) { m.perms.forEach(function (p) { allIds.push(p.id); }); });
    var roles = D.roles;
    var saved = {}, state = {};
    roles.forEach(function (r) {
        var ids = r.slug === 'admin' ? allIds : (D.matrix[r.slug] || []);
        saved[r.slug] = new Set(ids);
        state[r.slug] = new Set(ids);
    });
    var cur = (roles.find(function (r) { return r.slug !== 'admin'; }) || roles[0] || {}).slug;
    var view = 'editor', filterText = '', filterMode = 'all';

    function isLocked(slug) { return slug === 'admin'; }
    function roleBy(slug) { return roles.find(function (r) { return r.slug === slug; }); }

    function diffCount() {
        var n = 0;
        roles.forEach(function (r) {
            if (isLocked(r.slug)) return;
            var a = state[r.slug], b = saved[r.slug];
            a.forEach(function (id) { if (!b.has(id)) n++; });
            b.forEach(function (id) { if (!a.has(id)) n++; });
        });
        return n;
    }

    /* ---------- rail ---------- */
    function renderRail() {
        var q = ($('#iamRoleSearch') || {}).value || '';
        q = q.trim().toLowerCase();
        $('#iamRail').innerHTML = roles.filter(function (r) {
            return !q || (r.name + ' ' + r.department + ' ' + r.slug).toLowerCase().indexOf(q) > -1;
        }).map(function (r) {
            var on = state[r.slug].size, pct = allIds.length ? Math.round(on / allIds.length * 100) : 0;
            return '<button type="button" class="iam-role ' + (r.slug === cur ? 'active' : '') + '" data-role="' + esc(r.slug) + '">'
                + '<span class="iam-role-ico" style="background:' + esc(r.color) + '"><i class="' + esc(r.icon) + '"></i></span>'
                + '<span class="iam-role-txt"><b>' + esc(r.name) + '</b><small>' + esc(r.department) + '</small>'
                + '<span class="iam-bar"><i style="width:' + pct + '%;background:' + esc(r.color) + '"></i></span></span>'
                + '<span class="iam-role-n">' + on + '</span></button>';
        }).join('') || '<div class="iam-empty">No role matches.</div>';
    }

    /* ---------- editor ---------- */
    function permVisible(p, on) {
        if (filterMode === 'on' && !on) return false;
        if (filterMode === 'off' && on) return false;
        return !filterText || (p.name + ' ' + p.key + ' ' + (p.description || '')).toLowerCase().indexOf(filterText) > -1;
    }

    function renderEditor() {
        var r = roleBy(cur);
        if (!r) { $('#iamEditor').innerHTML = '<div class="iam-empty">No roles yet.</div>'; return; }
        var set = state[cur], locked = isLocked(cur);

        var head = '<div class="iam-editor-head"><span class="iam-role-ico lg" style="background:' + esc(r.color) + '"><i class="' + esc(r.icon) + '"></i></span>'
            + '<div class="flex-grow-1"><h5 class="mb-0 fw-bold">' + esc(r.name) + '</h5><small class="iam-muted">' + esc(r.department) + (r.description ? ' · ' + esc(r.description) : '') + '</small></div>'
            + '<div class="iam-count"><b id="iamCurCount">' + set.size + '</b><span>/ ' + allIds.length + '</span></div></div>';

        if (locked) {
            $('#iamEditor').innerHTML = head + '<div class="iam-lock"><i class="fa-solid fa-crown"></i><div><b>Full access</b><br><small>The admin role always has every permission and cannot be changed.</small></div></div>';
            return;
        }

        var tools = '<div class="iam-tools"><button type="button" class="iam-btn sm" data-act="role-all"><i class="fa-solid fa-check-double"></i> All</button>'
            + '<button type="button" class="iam-btn sm" data-act="role-none"><i class="fa-solid fa-xmark"></i> None</button>'
            + '<select class="iam-select sm" id="iamCopyFrom" title="Copy permissions from another role"><option value="">Copy from…</option>'
            + roles.filter(function (x) { return x.slug !== cur; }).map(function (x) { return '<option value="' + esc(x.slug) + '">' + esc(x.name) + '</option>'; }).join('')
            + '</select></div>';

        var mods = D.modules.map(function (m) {
            var perms = m.perms.filter(function (p) { return permVisible(p, set.has(p.id)); });
            if (!perms.length) return '';
            var on = m.perms.filter(function (p) { return set.has(p.id); }).length;
            return '<section class="iam-mod" data-mod="' + esc(m.key) + '">'
                + '<header><span class="iam-mod-ico" style="background:' + esc(m.color) + '"><i class="' + esc(m.icon) + '"></i></span>'
                + '<b>' + esc(m.title) + '</b><span class="iam-pill">' + on + '/' + m.perms.length + '</span>'
                + '<span class="ms-auto"><button type="button" class="iam-link" data-act="mod-all" data-mod="' + esc(m.key) + '">All</button>'
                + '<button type="button" class="iam-link" data-act="mod-none" data-mod="' + esc(m.key) + '">None</button></span></header>'
                + perms.map(function (p) {
                    return '<label class="iam-perm"><span><b>' + esc(p.name) + '</b><code>' + esc(p.key) + '</code>'
                        + (p.description ? '<small>' + esc(p.description) + '</small>' : '') + '</span>'
                        + '<span class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" data-id="' + p.id + '"' + (set.has(p.id) ? ' checked' : '') + '></span></label>';
                }).join('') + '</section>';
        }).join('');

        $('#iamEditor').innerHTML = head + tools + '<div class="iam-mods">' + (mods || '<div class="iam-empty">No permission matches your filter.</div>') + '</div>';
    }

    /* ---------- compare table ---------- */
    function renderMatrix() {
        var th = roles.map(function (r) {
            return '<th class="text-center"><span class="iam-chip" style="background:' + esc(r.color) + '"><i class="' + esc(r.icon) + '"></i> ' + esc(r.name) + '</span>'
                + (isLocked(r.slug) ? '<div class="iam-muted small">Full</div>' : '<div><button type="button" class="iam-link" data-act="col-toggle" data-role="' + esc(r.slug) + '">Toggle all</button></div>') + '</th>';
        }).join('');

        var body = D.modules.map(function (m) {
            var perms = m.perms.filter(function (p) { return !filterText || (p.name + ' ' + p.key + ' ' + (p.description || '')).toLowerCase().indexOf(filterText) > -1; });
            if (!perms.length) return '';
            return '<tr class="iam-modrow"><td colspan="' + (roles.length + 1) + '"><span class="iam-mod-ico" style="background:' + esc(m.color) + '"><i class="' + esc(m.icon) + '"></i></span> <b>' + esc(m.title) + '</b></td></tr>'
                + perms.map(function (p) {
                    return '<tr><td><b>' + esc(p.name) + '</b> <code>' + esc(p.key) + '</code></td>' + roles.map(function (r) {
                        return '<td class="text-center"><span class="form-check form-switch d-inline-block m-0"><input class="form-check-input" type="checkbox" data-id="' + p.id + '" data-role="' + esc(r.slug) + '"'
                            + (state[r.slug].has(p.id) ? ' checked' : '') + (isLocked(r.slug) ? ' disabled' : '') + '></span></td>';
                    }).join('') + '</tr>';
                }).join('');
        }).join('');

        $('#iamMatrix').innerHTML = '<div class="table-responsive"><table class="iam-table"><thead><tr><th>Permission</th>' + th + '</tr></thead><tbody>' + body + '</tbody></table></div>';
    }

    function renderAll() {
        renderRail();
        if (view === 'editor') renderEditor(); else renderMatrix();
        refreshDirty();
    }

    /* ---------- dirty bar ---------- */
    function refreshDirty() {
        var n = diffCount(), bar = $('#iamSave');
        bar.classList.toggle('show', n > 0);
        $('#iamDirtyN').textContent = n;
        $('#iamDirtyS').textContent = n === 1 ? 'change' : 'changes';
    }
    window.addEventListener('beforeunload', function (e) { if (diffCount() > 0) { e.preventDefault(); e.returnValue = ''; } });

    /* ---------- events ---------- */
    function setRole(slug, ids) { state[slug] = new Set(ids); }
    function modIds(key) { var m = D.modules.find(function (x) { return x.key === key; }); return m ? m.perms.map(function (p) { return p.id; }) : []; }

    root.addEventListener('click', function (e) {
        var rb = e.target.closest('.iam-role');
        if (rb) { cur = rb.dataset.role; renderAll(); return; }

        var a = e.target.closest('[data-act]'); if (!a) return;
        var act = a.dataset.act;
        if (act === 'role-all') setRole(cur, allIds);
        else if (act === 'role-none') setRole(cur, []);
        else if (act === 'mod-all') modIds(a.dataset.mod).forEach(function (id) { state[cur].add(id); });
        else if (act === 'mod-none') modIds(a.dataset.mod).forEach(function (id) { state[cur].delete(id); });
        else if (act === 'col-toggle') {
            var s = a.dataset.role, all = allIds.every(function (id) { return state[s].has(id); });
            setRole(s, all ? [] : allIds);
        } else if (act === 'view') { view = a.dataset.view; $$('[data-act="view"]').forEach(function (b) { b.classList.toggle('active', b === a); }); $('#iamEditorWrap').hidden = view !== 'editor'; $('#iamMatrix').hidden = view !== 'matrix'; }
        else if (act === 'discard') { roles.forEach(function (r) { state[r.slug] = new Set(saved[r.slug]); }); }
        else if (act === 'save') { save(a); return; }
        else if (act === 'mode') { filterMode = a.dataset.mode; $$('[data-act="mode"]').forEach(function (b) { b.classList.toggle('active', b === a); }); }
        renderAll();
    });

    root.addEventListener('change', function (e) {
        var t = e.target;
        if (t.id === 'iamCopyFrom' && t.value) {
            var from = roleBy(t.value);
            if (confirm('Replace ' + roleBy(cur).name + ' permissions with those of ' + from.name + '?')) { setRole(cur, Array.from(state[t.value])); }
            renderAll(); return;
        }
        if (t.matches('input[type="checkbox"][data-id]')) {
            var slug = t.dataset.role || cur, id = Number(t.dataset.id);
            if (isLocked(slug)) return;
            t.checked ? state[slug].add(id) : state[slug].delete(id);
            // light update: counters only (keeps scroll position and focus)
            var m = t.closest('.iam-mod');
            if (m) {
                var key = m.dataset.mod, mod = D.modules.find(function (x) { return x.key === key; });
                m.querySelector('.iam-pill').textContent = mod.perms.filter(function (p) { return state[cur].has(p.id); }).length + '/' + mod.perms.length;
                var c = $('#iamCurCount'); if (c) c.textContent = state[cur].size;
            }
            renderRail(); refreshDirty();
        }
    });

    var search = $('#iamSearch');
    if (search) search.addEventListener('input', function () { filterText = search.value.trim().toLowerCase(); if (view === 'editor') renderEditor(); else renderMatrix(); });
    var rs = $('#iamRoleSearch');
    if (rs) rs.addEventListener('input', renderRail);

    /* ---------- save ---------- */
    function save(btn) {
        var payload = {};
        roles.forEach(function (r) { if (!isLocked(r.slug)) payload[r.slug] = Array.from(state[r.slug]); });
        btn.disabled = true; var old = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving';
        fetch(D.urls.save, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ permissions: payload })
        }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
          .then(function (res) {
              if (!res.ok || !res.d.success) throw new Error(res.d.message || 'Save failed');
              roles.forEach(function (r) { saved[r.slug] = new Set(state[r.slug]); });
              toast(res.d.message || 'Saved'); renderAll();
          })
          .catch(function (err) { toast(err.message || 'Save failed', true); })
          .finally(function () { btn.disabled = false; btn.innerHTML = old; });
    }

    /* ---------- tabs: remember the open one ---------- */
    var hash = location.hash;
    if (hash && window.bootstrap) {
        var tab = document.querySelector('[data-bs-target="' + hash + '"]');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
    $$('[data-bs-toggle="tab"]').forEach(function (t) {
        t.addEventListener('shown.bs.tab', function () { history.replaceState(null, '', t.dataset.bsTarget); });
    });

    /* ---------- role modal (create / edit) ---------- */
    var roleModal = document.getElementById('iamRoleModal');
    if (roleModal) {
        var rf = roleModal.querySelector('form'), method = rf.querySelector('input[name="_method"]');
        window.iamRoleForm = function (r) {
            var edit = !!(r && r.id);
            rf.action = edit ? D.urls.roleBase + '/' + r.id : D.urls.roleStore;
            method.disabled = !edit; method.value = 'PUT';
            roleModal.querySelector('.modal-title span').textContent = edit ? 'Edit role' : 'New role';
            roleModal.querySelector('[data-when="create"]').hidden = edit;
            rf.elements.slug.closest('.col-md-6, .col-12').hidden = edit;
            rf.elements.slug.disabled = edit;
            rf.elements.name.value = r ? r.name : '';
            rf.elements.department.value = r ? r.department : 'Operations & Support';
            rf.elements.badge_color.value = r ? r.color : '#2563eb';
            rf.elements.icon.value = r ? r.icon : 'fa-solid fa-user-shield';
            rf.elements.description.value = r ? (r.description || '') : '';
            if (!edit) rf.elements.slug.value = '';
            bootstrap.Modal.getOrCreateInstance(roleModal).show();
        };
        $$('[data-tpl]').forEach(function (b) {
            b.addEventListener('click', function () {
                var t = JSON.parse(b.dataset.tpl);
                rf.elements.name.value = t.name; rf.elements.department.value = t.dept;
                rf.elements.badge_color.value = t.color; rf.elements.icon.value = t.icon; rf.elements.description.value = t.desc;
            });
        });
    }

    root.addEventListener('click', function (e) {
        var b = e.target.closest('[data-role-edit]');
        if (b) { iamRoleForm(JSON.parse(b.dataset.roleEdit)); return; }
        var c = e.target.closest('[data-role-clone]');
        if (c) {
            var d = JSON.parse(c.dataset.roleClone), cm = document.getElementById('iamCloneModal');
            cm.querySelector('form').action = D.urls.roleBase + '/' + d.id + '/clone';
            cm.querySelector('[name="new_name"]').value = d.name + ' (Copy)';
            cm.querySelector('.iam-clone-from').textContent = d.name;
            bootstrap.Modal.getOrCreateInstance(cm).show();
        }
        var s = e.target.closest('[data-staff-edit]');
        if (s) {
            var u = JSON.parse(s.dataset.staffEdit), sm = document.getElementById('iamStaffModal'), f = sm.querySelector('form');
            f.action = D.urls.staffBase + '/' + u.id + '/update-role';
            f.elements.custom_role_id.value = u.role || '';
            f.elements.ip_whitelist.value = u.ip || '';
            sm.querySelector('.iam-staff-name').textContent = u.name;
            bootstrap.Modal.getOrCreateInstance(sm).show();
        }
        var i = e.target.closest('[data-staff-inspect]');
        if (i) { inspect(JSON.parse(i.dataset.staffInspect)); }
    });

    /* ---------- staff filters: apply while typing ---------- */
    var sf = document.getElementById('iamStaffFilters');
    if (sf) {
        var t;
        sf.elements.search.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { sf.action = sf.action.split('#')[0] + '#staffTabPane'; sf.submit(); }, 550); });
        ['role', 'status'].forEach(function (n) { sf.elements[n].addEventListener('change', function () { sf.action = sf.action.split('#')[0] + '#staffTabPane'; sf.submit(); }); });
        if (sf.elements.search.value && document.referrer.indexOf(location.pathname) > -1) {
            var el = sf.elements.search; el.focus(); el.setSelectionRange(el.value.length, el.value.length);
        }
    }

    /* ---------- staff inspector ---------- */
    function inspect(u) {
        var m = document.getElementById('iamInspector'), loader = $('#iamInspLoader', m), body = $('#iamInspBody', m);
        $('.iam-insp-name', m).textContent = u.name;
        $('.iam-insp-sub', m).textContent = 'Loading…';
        loader.hidden = false; body.hidden = true;
        m.querySelector('form').action = D.urls.staffBase + '/' + u.id + '/direct-permissions';
        bootstrap.Modal.getOrCreateInstance(m).show();

        fetch(D.urls.staffBase + '/' + u.id + '/inspector', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.success) throw new Error();
                var d = j.data, groups = {};
                $('.iam-insp-sub', m).textContent = 'Role: ' + d.role_name + ' · ' + d.total_effective + ' effective permissions';
                Object.keys(d.matrix).forEach(function (pid) { var it = d.matrix[pid], k = it.permission.module || 'general'; (groups[k] = groups[k] || []).push(it); });
                body.querySelector('.iam-insp-list').innerHTML = Object.keys(groups).map(function (k) {
                    return '<section class="iam-mod"><header><b>' + esc(k.toUpperCase()) + '</b><span class="iam-pill">' + groups[k].length + '</span></header>'
                        + groups[k].map(function (it) {
                            var p = it.permission, g = it.override === 'grant', n = it.override === 'deny';
                            return '<div class="iam-perm insp-row" data-s="' + esc((p.name + ' ' + p.key).toLowerCase()) + '"><span><b>' + esc(p.name) + '</b><code>' + esc(p.key) + '</code>'
                                + '<small>' + (it.inherited ? 'In role' : 'Not in role') + '</small></span>'
                                + '<span class="iam-seg" data-pid="' + p.id + '">'
                                + '<label><input type="radio" name="o' + p.id + '" value="default"' + (!g && !n ? ' checked' : '') + '><span>Default</span></label>'
                                + '<label class="g"><input type="radio" name="o' + p.id + '" value="grant"' + (g ? ' checked' : '') + '><span>Grant</span></label>'
                                + '<label class="d"><input type="radio" name="o' + p.id + '" value="deny"' + (n ? ' checked' : '') + '><span>Deny</span></label></span>'
                                + '<span class="hid"></span></div>';
                        }).join('') + '</section>';
                }).join('');
                body.querySelectorAll('.iam-seg').forEach(function (seg) { syncOverride(seg); });
                loader.hidden = true; body.hidden = false;
            })
            .catch(function () { loader.innerHTML = '<div class="text-danger py-4 text-center">Could not load permissions.</div>'; });
    }
    function syncOverride(seg) {
        var pid = seg.dataset.pid, val = seg.querySelector('input:checked').value, hid = seg.parentNode.querySelector('.hid');
        hid.innerHTML = val === 'grant' ? '<input type="hidden" name="grants[]" value="' + pid + '">' : (val === 'deny' ? '<input type="hidden" name="denies[]" value="' + pid + '">' : '');
    }
    document.addEventListener('change', function (e) { var seg = e.target.closest('.iam-seg'); if (seg) syncOverride(seg); });
    var is = document.getElementById('iamInspSearch');
    if (is) is.addEventListener('input', function () {
        var q = is.value.trim().toLowerCase();
        document.querySelectorAll('#iamInspector .insp-row').forEach(function (r) { r.style.display = !q || r.dataset.s.indexOf(q) > -1 ? '' : 'none'; });
    });

    renderAll();
})();
