/**
 * IdeaABD — Admin Dynamic Theme Engine & Controller
 * Supports:
 * - Real-time Theme Mode Switching (Light / Dark / Auto)
 * - Dynamic Brand & Accent Colors via CSS Variables
 * - Sidebar Preset Themes
 * - Typography / Bengali Font Switching
 * - Chart.js & ApexCharts Dynamic Palette Sync
 * - Instant Live Preview & AJAX Persistent Backend Sync
 */
(function (window, document) {
    'use strict';

    var STORAGE_PREFIX = 'adm-theme-';
    var KEYS = {
        MODE: STORAGE_PREFIX + 'mode',
        PRIMARY: STORAGE_PREFIX + 'primary',
        SECONDARY: STORAGE_PREFIX + 'secondary',
        ACCENT: STORAGE_PREFIX + 'accent',
        SIDEBAR: STORAGE_PREFIX + 'sidebar',
        FONT: STORAGE_PREFIX + 'font',
        RADIUS: STORAGE_PREFIX + 'radius'
    };

    // Helper: HEX to RGB
    function hexToRgb(hex) {
        if (!hex) return '0, 102, 204';
        hex = hex.replace('#', '').trim();
        if (hex.length === 3) {
            hex = hex.split('').map(function (c) { return c + c; }).join('');
        }
        if (hex.length !== 6) return '0, 102, 204';
        var num = parseInt(hex, 16);
        return ((num >> 16) & 255) + ', ' + ((num >> 8) & 255) + ', ' + (num & 255);
    }

    // Helper: Darken Color
    function darkenColor(hex, percent) {
        if (!hex || hex.indexOf('#') === -1) return hex;
        var num = parseInt(hex.slice(1), 16),
            amt = Math.round(2.55 * percent),
            R = (num >> 16) - amt,
            G = (num >> 8 & 0x00FF) - amt,
            B = (num & 0x0000FF) - amt;
        return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }

    var AdminTheme = {
        state: {
            mode: localStorage.getItem(KEYS.MODE) || document.documentElement.dataset.themeMode || 'light',
            primary: localStorage.getItem(KEYS.PRIMARY) || document.documentElement.dataset.primaryColor || '#0066cc',
            secondary: localStorage.getItem(KEYS.SECONDARY) || document.documentElement.dataset.secondaryColor || '#0099ff',
            accent: localStorage.getItem(KEYS.ACCENT) || '#ff6b35',
            sidebar: localStorage.getItem(KEYS.SIDEBAR) || document.documentElement.dataset.sidebarTheme || 'theme-deep-navy',
            font: localStorage.getItem(KEYS.FONT) || document.documentElement.dataset.fontFamily || 'Hind Siliguri',
            radius: localStorage.getItem(KEYS.RADIUS) || 'rounded-modern'
        },

        init: function () {
            this.applyAll();
            this.bindEvents();
            this.bindSystemScheme();
        },

        applyAll: function () {
            this.setMode(this.state.mode, false);
            this.setColors(this.state.primary, this.state.secondary, this.state.accent, false);
            this.setSidebarTheme(this.state.sidebar, false);
            this.setFont(this.state.font, false);
        },

        setMode: function (mode, save) {
            this.state.mode = mode;
            var isDark = false;
            if (mode === 'dark') {
                isDark = true;
            } else if (mode === 'auto') {
                isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            if (isDark) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('adm-dark-mode', '1');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('adm-dark-mode', '0');
            }

            if (save !== false) {
                localStorage.setItem(KEYS.MODE, mode);
            }

            this.updateToggleButtons(isDark, mode);
            this.syncChartsTheme(isDark);
            this.dispatchThemeEvent();
        },

        toggleMode: function () {
            var current = this.state.mode;
            var next = current === 'dark' ? 'light' : 'dark';
            this.setMode(next, true);
        },

        setColors: function (primary, secondary, accent, save) {
            if (primary) this.state.primary = primary;
            if (secondary) this.state.secondary = secondary;
            if (accent) this.state.accent = accent;

            var root = document.documentElement;
            var rgb = hexToRgb(this.state.primary);
            var hover = darkenColor(this.state.primary, 15);

            root.style.setProperty('--brand', this.state.primary);
            root.style.setProperty('--brand-2', this.state.secondary || this.state.primary);
            root.style.setProperty('--brand-rgb', rgb);
            root.style.setProperty('--brand-hover', hover);
            root.style.setProperty('--brand-glow', 'rgba(' + rgb + ', 0.28)');
            if (this.state.accent) {
                root.style.setProperty('--accent', this.state.accent);
            }

            if (save !== false) {
                localStorage.setItem(KEYS.PRIMARY, this.state.primary);
                localStorage.setItem(KEYS.SECONDARY, this.state.secondary);
                localStorage.setItem(KEYS.ACCENT, this.state.accent);
                localStorage.setItem('adm-dynamic-brand', this.state.primary);
                localStorage.setItem('adm-dynamic-brand2', this.state.secondary);
            }

            this.dispatchThemeEvent();
        },

        setSidebarTheme: function (themeClass, save) {
            this.state.sidebar = themeClass || 'theme-deep-navy';
            var body = document.body;
            var themes = [
                'sidebar-deep-navy',
                'sidebar-midnight-slate',
                'sidebar-emerald-forest',
                'sidebar-royal-purple',
                'sidebar-crimson-night',
                'sidebar-minimal-light'
            ];

            themes.forEach(function (cls) { body.classList.remove(cls); });

            var classToAdd = themeClass.replace('theme-', 'sidebar-');
            body.classList.add(classToAdd);

            if (save !== false) {
                localStorage.setItem(KEYS.SIDEBAR, themeClass);
            }
            this.dispatchThemeEvent();
        },

        setFont: function (fontName, save) {
            this.state.font = fontName || 'Hind Siliguri';
            var body = document.body;
            var fonts = ['font-hind-siliguri', 'font-kalpurush', 'font-nikosh', 'font-inter'];
            fonts.forEach(function (f) { body.classList.remove(f); });

            var fontSlug = fontName.toLowerCase().replace(/\s+/g, '-');
            body.classList.add('font-' + fontSlug);

            if (save !== false) {
                localStorage.setItem(KEYS.FONT, fontName);
            }
            this.dispatchThemeEvent();
        },

        applyPreset: function (preset) {
            if (!preset) return;
            this.setColors(preset.primary, preset.secondary, preset.accent || '#ff6b35', true);
            if (preset.sidebar) {
                this.setSidebarTheme(preset.sidebar, true);
            }
            if (preset.mode) {
                this.setMode(preset.mode, true);
            }
        },

        saveToServer: function (saveGlobal, callback) {
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            var endpoint = '/admin/theme/update';

            var payload = {
                primary_color: this.state.primary,
                secondary_color: this.state.secondary,
                accent_color: this.state.accent,
                default_mode: this.state.mode,
                sidebar_theme: this.state.sidebar,
                font_family: this.state.font,
                save_global: saveGlobal ? 1 : 0
            };

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (typeof callback === 'function') {
                    callback(data);
                } else if (window.SwalToast) {
                    window.SwalToast('success', data.message || 'থিম সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে!');
                }
            })
            .catch(function (err) {
                console.error('Theme save error:', err);
                if (window.SwalToast) {
                    window.SwalToast('error', 'থিম সংরক্ষণ করতে সমস্যা হয়েছে।');
                }
            });
        },

        resetDefaults: function (resetGlobal, callback) {
            var self = this;
            var defaultPreset = {
                primary: '#0066cc',
                secondary: '#0099ff',
                accent: '#ff6b35',
                mode: 'light',
                sidebar: 'theme-deep-navy',
                font: 'Hind Siliguri'
            };

            this.applyPreset(defaultPreset);
            this.setFont(defaultPreset.font, true);

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/admin/theme/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ reset_global: resetGlobal ? 1 : 0 })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (typeof callback === 'function') {
                    callback(data);
                } else if (window.SwalToast) {
                    window.SwalToast('success', data.message || 'থিম ডিফল্ট অবস্থায় ফিরিয়ে আনা হয়েছে!');
                }
            });
        },

        updateToggleButtons: function (isDark, mode) {
            document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isDark ? 'fas fa-sun text-warning' : 'fas fa-moon';
                }
                btn.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
            });

            document.querySelectorAll('[data-theme-mode-select]').forEach(function (el) {
                if (el.value !== undefined) el.value = mode;
            });
        },

        syncChartsTheme: function (isDark) {
            // Synchronize Chart.js instances
            if (window.Chart && window.Chart.instances) {
                var gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
                var textColor = isDark ? '#9ca3af' : '#64748b';

                Object.values(window.Chart.instances).forEach(function (chart) {
                    if (chart.options && chart.options.scales) {
                        if (chart.options.scales.x) {
                            if (chart.options.scales.x.grid) chart.options.scales.x.grid.color = gridColor;
                            if (chart.options.scales.x.ticks) chart.options.scales.x.ticks.color = textColor;
                        }
                        if (chart.options.scales.y) {
                            if (chart.options.scales.y.grid) chart.options.scales.y.grid.color = gridColor;
                            if (chart.options.scales.y.ticks) chart.options.scales.y.ticks.color = textColor;
                        }
                    }
                    chart.update();
                });
            }

            // Synchronize ApexCharts instances
            if (window.ApexCharts) {
                window.dispatchEvent(new CustomEvent('apex-theme-update', { detail: { isDark: isDark } }));
            }
        },

        dispatchThemeEvent: function () {
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: {
                    mode: this.state.mode,
                    isDark: document.body.classList.contains('dark-mode'),
                    primary: this.state.primary,
                    secondary: this.state.secondary,
                    sidebar: this.state.sidebar,
                    font: this.state.font
                }
            }));
        },

        bindEvents: function () {
            var self = this;

            // Global Dark Mode Toggle Click
            document.addEventListener('click', function (e) {
                var toggleBtn = e.target.closest('[data-theme-toggle]');
                if (toggleBtn) {
                    e.preventDefault();
                    self.toggleMode();
                }
            });

            // Theme Mode Selector inputs
            document.addEventListener('change', function (e) {
                var modeInput = e.target.closest('[data-theme-mode-select], input[name="admin_theme_mode"]');
                if (modeInput) {
                    self.setMode(modeInput.value, true);
                }
            });
        },

        bindSystemScheme: function () {
            var self = this;
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                    if (self.state.mode === 'auto') {
                        self.setMode('auto', false);
                    }
                });
            }
        }
    };

    // Run immediate setup
    AdminTheme.init();
    window.AdminTheme = AdminTheme;

})(window, document);
