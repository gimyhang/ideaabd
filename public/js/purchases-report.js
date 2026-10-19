/* Purchases › monthly report: remember the last open ledger tab. */
(function () {
    'use strict';
    var KEY = 'pr-report-tab';

    function read() { try { return localStorage.getItem(KEY); } catch (e) { return null; } }
    function write(v) { try { localStorage.setItem(KEY, v); } catch (e) { /* storage unavailable */ } }

    document.addEventListener('DOMContentLoaded', function () {
        var tabs = document.getElementById('reportTabs');
        if (!tabs || !window.bootstrap) return;

        var saved = read();
        var btn = saved && tabs.querySelector('[data-bs-target="' + saved + '"]');
        if (btn && !btn.classList.contains('active')) window.bootstrap.Tab.getOrCreateInstance(btn).show();

        tabs.addEventListener('shown.bs.tab', function (e) {
            write(e.target.getAttribute('data-bs-target'));
        });
    });
})();
