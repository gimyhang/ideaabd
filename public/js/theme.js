/**
 * ══════════════════════════════════════════════════════════════════════════
 * IDEA PROKASHON — UNIVERSAL THEME MANAGER & CONTROLLER (LIGHT / DARK)
 * Persists in localStorage and Cookie, smooth instant switching, zero conflict.
 * ══════════════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    const THEME_STORAGE_KEY = 'idea_theme_preference';

    // Get current effective theme ('light' or 'dark')
    window.getSavedTheme = function() {
        const stored = localStorage.getItem(THEME_STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
        // If not set, check cookie
        const match = document.cookie.match(new RegExp('(^| )idea_theme=([^;]+)'));
        if (match && (match[2] === 'dark' || match[2] === 'light')) {
            return match[2];
        }
        // Fallback to system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    };

    // Apply theme to document element
    window.applySiteTheme = function(theme, updateUI = true) {
        const root = document.documentElement;
        const validTheme = (theme === 'dark') ? 'dark' : 'light';
        
        root.setAttribute('data-bs-theme', validTheme);
        root.setAttribute('data-theme', validTheme);

        if (validTheme === 'dark') {
            root.classList.add('dark-mode');
        } else {
            root.classList.remove('dark-mode');
        }

        // Persist in localStorage and Cookie
        try {
            localStorage.setItem(THEME_STORAGE_KEY, validTheme);
            document.cookie = `idea_theme=${validTheme};path=/;max-age=31536000;SameSite=Lax`;
        } catch (e) {}

        if (updateUI) {
            updateThemeToggleButtons(validTheme);
        }

        // Dispatch custom event for dynamic components
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: validTheme } }));
    };

    // Toggle between light and dark
    window.toggleSiteTheme = function() {
        const current = document.documentElement.getAttribute('data-bs-theme') || window.getSavedTheme();
        const nextTheme = (current === 'dark') ? 'light' : 'dark';
        window.applySiteTheme(nextTheme, true);
    };

    // Update icons and labels in all theme switcher buttons on the page
    function updateThemeToggleButtons(theme) {
        const isDark = (theme === 'dark');
        
        // 1. Update Topbar Theme Switcher Icon & Label
        const topIcon = document.getElementById('siteThemeIcon');
        const topLabel = document.getElementById('siteThemeLabel');
        if (topIcon) {
            topIcon.className = isDark ? 'fas fa-sun text-warning' : 'fas fa-moon text-warning';
        }
        if (topLabel) {
            topLabel.textContent = isDark ? 'Light' : 'Dark';
        }

        // 2. Update any other buttons with [data-theme-toggle]
        document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
            const icon = btn.querySelector('i') || btn.querySelector('.theme-toggle-icon');
            if (icon) {
                if (isDark) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
            btn.setAttribute('aria-label', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
            btn.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        });
    }

    // Apply theme immediately on initial script execution
    const initialTheme = window.getSavedTheme();
    window.applySiteTheme(initialTheme, false);

    // When DOM is fully loaded, sync toggle button UI
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            updateThemeToggleButtons(window.getSavedTheme());
        });
    } else {
        updateThemeToggleButtons(initialTheme);
    }

    // Listen for OS system theme changes if user has not explicitly chosen
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem(THEME_STORAGE_KEY)) {
                window.applySiteTheme(e.matches ? 'dark' : 'light', true);
            }
        });
    }
})();
