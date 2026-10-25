/**
 * Keeps Bootstrap modals from leaving the screen dark and locked.
 *
 * The usual cause: code that does `new bootstrap.Modal(el).show()` every time a button is pressed. Each call makes
 * another instance for the same element, each instance adds its own dark backdrop, but closing removes only one.
 * The leftovers cover the page and `modal-open` keeps the page from scrolling or clicking.
 *
 * 1. `new bootstrap.Modal(el)` now returns the instance that already exists for that element.
 * 2. When the last modal has closed, any backdrop or body lock left behind is cleared.
 * Load it right after bootstrap.bundle.js.
 */
(function () {
    'use strict';

    if (!window.bootstrap || !bootstrap.Modal || bootstrap.Modal.__guarded) {
        return;
    }

    var Original = bootstrap.Modal;

    function Guarded(element, config) {
        var el = typeof element === 'string' ? document.querySelector(element) : element;
        var existing = el ? Original.getInstance(el) : null;

        // a constructor that returns an object hands that object back from `new`
        return existing || new Original(el, config);
    }
    Guarded.prototype = Original.prototype;
    Object.setPrototypeOf(Guarded, Original);
    Guarded.__guarded = true;
    bootstrap.Modal = Guarded;

    function anyModalOpen() {
        return !!document.querySelector('.modal.show, .modal.showing');
    }

    function clearLeftovers() {
        document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    // Right after a modal closes (Bootstrap removes its own backdrop first, so wait one tick).
    document.addEventListener('hidden.bs.modal', function () {
        setTimeout(function () {
            if (!anyModalOpen()) {
                clearLeftovers();
            }
        }, 50);
    });

    // Safety net for backdrops that appear without any modal (a modal removed from the page while open, a script error).
    // A backdrop must be seen orphaned on two checks in a row, so one that is just fading in is never touched.
    var orphanSeen = false;
    setInterval(function () {
        var stuck = !anyModalOpen() && (document.querySelector('.modal-backdrop') || document.body.classList.contains('modal-open'));
        if (stuck && orphanSeen) {
            clearLeftovers();
            orphanSeen = false;
        } else {
            orphanSeen = !!stuck;
        }
    }, 1200);

    // Escape closes the top modal in Bootstrap; also let a click on a leftover dark layer free the page.
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('modal-backdrop') && !anyModalOpen()) {
            clearLeftovers();
        }
    });
})();
