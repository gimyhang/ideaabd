/*
 * Storefront category sliders: gentle auto-advance, looping, pauses on hover / touch / focus,
 * only runs while the row is on screen, and never when the visitor prefers reduced motion.
 * (Arrows, swipe and mouse-drag are handled by scrollIdeaSlider() and the page script.)
 */
(function () {
    'use strict';
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var INTERVAL = 4500;

    function init(track) {
        if (track._dzAuto) return;
        track._dzAuto = true;

        var timer = null, paused = false, visible = false;

        function step() {
            if (paused || !visible || document.hidden) return;
            var max = track.scrollWidth - track.clientWidth;
            if (max <= 4) return;                       // nothing to slide
            if (track.scrollLeft >= max - 4) {
                track.scrollTo({ left: 0, behavior: 'smooth' });   // loop back
            } else {
                track.scrollBy({ left: track.clientWidth * 0.75, behavior: 'smooth' });
            }
        }
        function start() { stop(); timer = setInterval(step, INTERVAL); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }

        ['mouseenter', 'focusin', 'touchstart', 'pointerdown'].forEach(function (ev) {
            track.addEventListener(ev, function () { paused = true; }, { passive: true });
        });
        ['mouseleave', 'focusout'].forEach(function (ev) {
            track.addEventListener(ev, function () { paused = false; }, { passive: true });
        });
        track.addEventListener('touchend', function () { setTimeout(function () { paused = false; }, 6000); }, { passive: true });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                visible = entries[0].isIntersecting;
            }, { threshold: 0.25 }).observe(track);
        } else { visible = true; }

        start();
    }

    function boot() {
        document.querySelectorAll('.dz-track[data-autoplay="1"]').forEach(init);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();

/*
 * Shared storefront helpers (guarded: pages that define their own versions keep them).
 * Used by the clean shop page: slider arrows, drag-to-scroll, add-to-cart feedback, quick view.
 */
(function () {
    'use strict';

    window.scrollIdeaSlider = window.scrollIdeaSlider || function (id, dir) {
        var el = document.getElementById(id);
        if (el) el.scrollBy({ left: el.clientWidth * 0.75 * dir, behavior: 'smooth' });
    };

    window.handleCardAddToCart = window.handleCardAddToCart || function (btn) {
        if (!btn) return;
        var icon = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.disabled = true;
        setTimeout(function () { btn.innerHTML = icon; btn.disabled = false; }, 1200);
        if (typeof window.addToCartLive === 'function') {
            window.addToCartLive(btn, btn.dataset.productId, btn.dataset.productTitle || '',
                parseFloat(btn.dataset.productPrice || 0), btn.dataset.productImage || '', btn.dataset.productType || '');
        }
    };

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function money(n) { return '৳' + Math.round(n).toLocaleString('bn-BD'); }

    window.openProductQuickView = window.openProductQuickView || function (id) {
        var modalEl = document.getElementById('productQuickViewModal');
        if (!modalEl || !window.bootstrap) return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var body = document.getElementById('qvContentBody');
        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
        modal.show();
        fetch((modalEl.dataset.url || '').replace(':id', id), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (p) {
                var sale = p.discount_price && p.discount_price < p.price;
                var inStock = p.is_in_stock !== false && p.stock > 0;
                var specs = '';
                if (p.specifications && typeof p.specifications === 'object') {
                    Object.keys(p.specifications).slice(0, 8).forEach(function (k) {
                        specs += '<li><span>' + esc(k) + '</span><b>' + esc(p.specifications[k]) + '</b></li>';
                    });
                }
                body.innerHTML =
                    '<div class="qv"><div class="qv__img"><img src="' + esc(p.image_url) + '" alt=""></div>' +
                    '<div class="qv__info"><h3>' + esc(p.title) + '</h3>' +
                    '<div class="dz-card__price"><span class="dz-card__now">' + money(p.final_price) + '</span>' +
                    (sale ? '<s class="dz-card__old">' + money(p.price) + '</s><span class="dz-card__off">-' + esc(p.discount_percent) + '%</span>' : '') + '</div>' +
                    '<div class="dz-card__stars">' + [1, 2, 3, 4, 5].map(function (i) { return '<i class="fa-solid fa-star ' + (i <= Math.round(p.rating || 5) ? 'on' : '') + '"></i>'; }).join('') + '</div>' +
                    (specs ? '<ul class="pr-specs">' + specs + '</ul>' : '') +
                    '<div class="qv__act">' +
                    (inStock ? '<button type="button" class="btn btn-primary" id="qvAdd">Add</button>' : '<button type="button" class="btn btn-secondary" disabled>Sold out</button>') +
                    '<a class="btn btn-outline-dark" href="' + esc(p.url) + '">Details</a></div></div></div>';
                var add = body.querySelector('#qvAdd');
                if (add) add.addEventListener('click', function () {
                    if (typeof window.addToCartLive === 'function') window.addToCartLive(this, p.id, p.title, p.final_price, p.image_url, p.type);
                    modal.hide();
                });
            })
            .catch(function () { body.innerHTML = '<div class="alert alert-danger mb-0">Failed to load</div>'; });
    };

    // Mouse drag-to-scroll on sliders
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dz-track').forEach(function (el) {
            var down = false, sx = 0, sl = 0, moved = false;
            el.addEventListener('mousedown', function (e) { if (e.target.closest('button')) return; down = true; moved = false; sx = e.pageX; sl = el.scrollLeft; el.style.scrollBehavior = 'auto'; });
            window.addEventListener('mouseup', function () { down = false; el.style.scrollBehavior = ''; });
            el.addEventListener('mousemove', function (e) { if (!down) return; var d = e.pageX - sx; if (Math.abs(d) > 4) moved = true; el.scrollLeft = sl - d * 1.4; });
            el.addEventListener('click', function (e) { if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; } }, true);
        });
    });
})();

/* ── Toolbar dropdowns (Category / Sort): toggle, close on outside click / Esc, bottom-sheet on phones, live find ── */
(function () {
    'use strict';
    function init() {
        var dds = document.querySelectorAll('[data-sf-dd]');
        if (!dds.length) return;
        var scrim = document.querySelector('[data-sf-scrim]');

        function closeAll(except) {
            dds.forEach(function (d) {
                if (d === except) return;
                d.classList.remove('is-open');
                var b = d.querySelector('.sf-dd__btn'); if (b) b.setAttribute('aria-expanded', 'false');
            });
            if (!except) { if (scrim) scrim.classList.remove('is-on'); document.documentElement.classList.remove('sf-dd-lock'); }
        }

        dds.forEach(function (d) {
            var btn = d.querySelector('.sf-dd__btn');
            var menu = d.querySelector('.sf-dd__menu');
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = !d.classList.contains('is-open');
                closeAll(open ? d : null);
                d.classList.toggle('is-open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                var sheet = window.matchMedia('(max-width: 767.98px)').matches;
                if (scrim) scrim.classList.toggle('is-on', open && sheet);
                document.documentElement.classList.toggle('sf-dd-lock', open && sheet);
                if (open) {
                    var f = d.querySelector('[data-sf-find]'); if (f && !sheet) f.focus();
                    var act = d.querySelector('.sf-dd__item.is-active');
                    if (act && act.scrollIntoView) act.scrollIntoView({ block: 'nearest' });
                }
            });
            menu.addEventListener('click', function (e) { e.stopPropagation(); });
            var x = d.querySelector('.sf-dd__close'); if (x) x.addEventListener('click', function () { closeAll(); });

            // live find inside long category lists
            var find = d.querySelector('[data-sf-find]');
            if (find) find.addEventListener('input', function () {
                var q = find.value.trim().toLowerCase(), shown = 0;
                d.querySelectorAll('.sf-dd__item[data-name]').forEach(function (a) {
                    var ok = !q || a.dataset.name.indexOf(q) !== -1; a.style.display = ok ? '' : 'none'; if (ok) shown++;
                });
                var none = d.querySelector('.sf-dd__none'); if (none) none.classList.toggle('d-none', shown > 0);
            });
        });

        document.addEventListener('click', function () { closeAll(); });
        if (scrim) scrim.addEventListener('click', function () { closeAll(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
        window.addEventListener('resize', function () { closeAll(); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
