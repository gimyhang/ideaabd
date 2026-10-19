/*
 * Event registration forms: progressive enhancement only — the forms work without this file.
 *  - saves a draft in this browser so a refresh / dropped connection does not lose typing
 *  - Bengali digits become English digits in phone / number fields
 *  - double-submit guard with a spinner, offline warning, jump to the first invalid field
 */
(function () {
    'use strict';
    var doc = document;
    var FORM_SELECTOR = '#writerRegisterForm, #dynamicScholarshipForm, #dynamicLibraryForm, #eventRegisterForm';
    var SKIP_NAMES = /^(_token|password|optimized_photo_data|student_photo|photo|declaration_agreed|transaction_id)$/;
    var BN = '০১২৩৪৫৬৭৮৯';

    function store(fn) { try { return fn(window.localStorage); } catch (e) { return null; } }

    function toast(text, bad) {
        var el = doc.getElementById('evToast');
        if (!el) {
            el = doc.createElement('div');
            el.id = 'evToast';
            el.className = 'ev-toast';
            el.setAttribute('role', 'status');
            doc.body.appendChild(el);
        }
        el.textContent = text;
        el.classList.toggle('is-bad', !!bad);
        el.classList.add('show');
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.classList.remove('show'); }, 3200);
    }

    function init(form) {
        var key = 'evdraft:' + location.pathname;

        // Text-like fields only: dependent selects (division → district → upazila) and files are not restored.
        function draftFields() {
            return Array.prototype.filter.call(form.elements, function (el) {
                if (!el.name || SKIP_NAMES.test(el.name) || el.readOnly || el.disabled) return false;
                if (el.tagName === 'TEXTAREA') return true;
                return el.tagName === 'INPUT' && /^(text|tel|email|number|date)$/.test(el.type);
            });
        }

        // ── Restore ─────────────────────────────────────────
        var hasServerValues = draftFields().some(function (el) { return el.value && el.defaultValue; });
        var saved = store(function (s) { return s.getItem(key); });
        if (saved && !hasServerValues) {
            try {
                var data = JSON.parse(saved);
                var restored = 0;
                draftFields().forEach(function (el) {
                    if (data[el.name] && !el.value) { el.value = data[el.name]; restored++; }
                });
                if (restored) {
                    var note = doc.createElement('div');
                    note.className = 'ev-draft no-print';
                    note.innerHTML = '<span>Draft restored</span><button type="button">Clear</button>';
                    form.insertBefore(note, form.firstChild);
                    note.querySelector('button').addEventListener('click', function () {
                        store(function (s) { s.removeItem(key); });
                        draftFields().forEach(function (el) { el.value = el.defaultValue || ''; });
                        note.remove();
                    });
                }
            } catch (e) { /* corrupt draft: ignore */ }
        }

        // ── Save (debounced) ────────────────────────────────
        var timer;
        function save() {
            var data = {};
            draftFields().forEach(function (el) { if (el.value) data[el.name] = el.value; });
            store(function (s) {
                if (Object.keys(data).length) s.setItem(key, JSON.stringify(data)); else s.removeItem(key);
            });
        }
        form.addEventListener('input', function (e) {
            var el = e.target;
            // Bengali → English digits for phone / numeric fields
            if (el.matches && el.matches('input[type="tel"], input[type="number"], input[name$="phone"], input[name$="roll"], input[name$="year"]')) {
                var fixed = el.value.replace(/[০-৯]/g, function (c) { return String(BN.indexOf(c)); });
                if (fixed !== el.value) el.value = fixed;
            }
            clearTimeout(timer);
            timer = setTimeout(save, 400);
        });

        // ── Submit ──────────────────────────────────────────
        var submitting = false;
        form.addEventListener('submit', function (e) {
            if (submitting) { e.preventDefault(); return; }
            if (!navigator.onLine) {
                e.preventDefault();
                toast('No connection', true);
                return;
            }
            if (e.defaultPrevented) return;
            submitting = true;
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn._label = btn.innerHTML;
                btn.innerHTML = '<span class="ev-spin" aria-hidden="true"></span>';
            }
            // The draft is cleared only once the server has accepted the page load that follows.
            store(function (s) { s.setItem(key + ':sent', '1'); });
            setTimeout(function () { release(btn); }, 20000);
        });

        function release(btn) {
            submitting = false;
            if (btn && btn._label) { btn.disabled = false; btn.innerHTML = btn._label; }
        }
        window.addEventListener('pageshow', function () { release(form.querySelector('button[type="submit"]')); });

        // Native validation: bring the first invalid field into view and tell the user once.
        form.addEventListener('invalid', function (e) {
            if (!form._shownInvalid) {
                form._shownInvalid = true;
                setTimeout(function () { form._shownInvalid = false; }, 600);
                e.target.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
        }, true);
    }

    // After a successful registration the success page loads; drop any draft saved for this form.
    function sweepSent() {
        store(function (s) {
            Object.keys(s).forEach(function (k) {
                if (/^evdraft:.*:sent$/.test(k) && !doc.querySelector(FORM_SELECTOR)) {
                    s.removeItem(k);
                    s.removeItem(k.replace(/:sent$/, ''));
                }
            });
        });
    }

    window.addEventListener('online', function () { toast('Back online'); });
    window.addEventListener('offline', function () { toast('No connection', true); });

    doc.addEventListener('DOMContentLoaded', function () {
        var form = doc.querySelector(FORM_SELECTOR);
        if (form) init(form); else sweepSent();
    });
})();
