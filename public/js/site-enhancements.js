/**
 * ══════════════════════════════════════════════════════════════════════════
 * IDEA PROKASHON — MODERN SITE ENHANCEMENTS ENGINE
 * Vanilla JS utilities: Quick Search Shortcut, Modern Toast Alerts, Auto-Save Drafts,
 * Image Fallback Handler, Back-to-Top Progress, and Multi-Tab Sync.
 * ══════════════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    // ── 1. Global Toast Notification System ──────────────────────────────────
    window.IdeaToast = {
        container: null,
        initContainer: function() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'idea-toast-container';
                this.container.setAttribute('aria-live', 'polite');
                this.container.setAttribute('aria-atomic', 'true');
                document.body.appendChild(this.container);
            }
        },
        show: function(options) {
            this.initContainer();
            const { title = '', message = '', type = 'info', duration = 4000 } = options;
            
            const icons = {
                success: '<i class="fa-solid fa-circle-check text-success fs-5 me-2"></i>',
                error: '<i class="fa-solid fa-circle-exclamation text-danger fs-5 me-2"></i>',
                warning: '<i class="fa-solid fa-triangle-exclamation text-warning fs-5 me-2"></i>',
                info: '<i class="fa-solid fa-circle-info text-info fs-5 me-2"></i>'
            };

            const toast = document.createElement('div');
            toast.className = `idea-toast idea-toast-${type} shadow-lg rounded-3 p-3 mb-2 d-flex align-items-center justify-content-between animate__animated animate__fadeInUp`;
            toast.style.minWidth = '280px';
            toast.style.maxWidth = '400px';

            toast.innerHTML = `
                <div class="d-flex align-items-center flex-grow-1 me-2">
                    ${icons[type] || icons.info}
                    <div>
                        ${title ? `<strong class="d-block mb-0.5 font-semibold text-dark fs-6">${title}</strong>` : ''}
                        <span class="text-secondary small line-clamp-2">${message}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-sm ms-auto flex-shrink-0" aria-label="Close"></button>
            `;

            const closeBtn = toast.querySelector('.btn-close');
            const dismiss = () => {
                toast.classList.remove('animate__fadeInUp');
                toast.classList.add('animate__fadeOutDown');
                setTimeout(() => toast.remove(), 300);
            };

            if (closeBtn) closeBtn.addEventListener('click', dismiss);

            this.container.appendChild(toast);

            if (duration > 0) {
                setTimeout(dismiss, duration);
            }
        },
        success: function(msg, title = 'সফল!') { this.show({ message: msg, title: title, type: 'success' }); },
        error: function(msg, title = 'ত্রুটি!') { this.show({ message: msg, title: title, type: 'error' }); },
        info: function(msg, title = 'তথ্য') { this.show({ message: msg, title: title, type: 'info' }); },
        warning: function(msg, title = 'সতর্কতা') { this.show({ message: msg, title: title, type: 'warning' }); }
    };

    // ── 2. Global Keyboard Shortcuts (Ctrl+K or / for Search) ────────────────
    document.addEventListener('keydown', function(e) {
        // Check if user is already typing in an input/textarea
        const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const isEditing = tag === 'input' || tag === 'textarea' || document.activeElement.isContentEditable;

        // Ctrl + K or Cmd + K or '/' when not typing
        if ((e.key === 'k' && (e.ctrlKey || e.metaKey)) || (e.key === '/' && !isEditing)) {
            const searchInput = document.querySelector('input[type="search"], input[name="q"], input[name="search"], #globalSearchInput');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                if (searchInput.select) searchInput.select();
                
                // Show temporary helper tooltip if available
                searchInput.classList.add('ring-2', 'ring-primary');
                setTimeout(() => searchInput.classList.remove('ring-2', 'ring-primary'), 1000);
            }
        }
    });

    // ── 3. One-Click Copy-to-Clipboard Action ─────────────────────────────────
    document.addEventListener('click', function(e) {
        const copyBtn = e.target.closest('[data-copy-text]');
        if (!copyBtn) return;

        const textToCopy = copyBtn.getAttribute('data-copy-text');
        if (!textToCopy) return;

        navigator.clipboard.writeText(textToCopy).then(() => {
            const originalHtml = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i> কপি হয়েছে!';
            if (window.IdeaToast) {
                window.IdeaToast.success('টেক্সট সফলভাবে ক্লিপবোর্ডে কপি হয়েছে।', 'কপি সম্পন্ন');
            }
            setTimeout(() => {
                copyBtn.innerHTML = originalHtml;
            }, 2000);
        }).catch(() => {
            if (window.IdeaToast) {
                window.IdeaToast.error('কপি করতে ব্যর্থ হয়েছে।', 'ত্রুটি');
            }
        });
    });

    // ── 4. Smart Local Form Draft Auto-Save & Recovery ───────────────────────
    function initDraftAutoSave() {
        const formsWithDraft = document.querySelectorAll('form[data-autosave-key]');
        formsWithDraft.forEach(form => {
            const draftKey = 'idea_draft_' + form.getAttribute('data-autosave-key');
            
            // Restore draft if present and inputs are empty
            try {
                const saved = localStorage.getItem(draftKey);
                if (saved) {
                    const data = JSON.parse(saved);
                    let hasRestored = false;
                    Object.keys(data).forEach(name => {
                        const field = form.querySelector(`[name="${name}"]`);
                        if (field && !field.value && data[name]) {
                            field.value = data[name];
                            hasRestored = true;
                        }
                    });
                    if (hasRestored && window.IdeaToast) {
                        window.IdeaToast.info('আপনার পূর্বে ড্রাফট করা টেক্সট স্বয়ংক্রিয়ভাবে লোড করা হয়েছে।', 'ড্রাফট রিকভারি');
                    }
                }
            } catch (e) {}

            // Auto-save on input
            form.addEventListener('input', function(e) {
                try {
                    const data = {};
                    form.querySelectorAll('input:not([type="password"]):not([type="hidden"]), textarea').forEach(el => {
                        if (el.name && el.value.trim()) {
                            data[el.name] = el.value;
                        }
                    });
                    localStorage.setItem(draftKey, JSON.stringify(data));
                } catch (e) {}
            });

            // Clear draft on successful submit
            form.addEventListener('submit', function() {
                try {
                    localStorage.removeItem(draftKey);
                } catch (e) {}
            });
        });
    }

    // ── 5. Image Error Fallback Handler ──────────────────────────────────────
    function initImageFallbacks() {
        document.querySelectorAll('img[data-fallback]').forEach(img => {
            img.addEventListener('error', function() {
                const fallback = img.getAttribute('data-fallback') || '/images/book-placeholder.png';
                if (img.src !== fallback) {
                    img.src = fallback;
                }
            }, { once: true });
        });
    }

    // ── 6. Multi-Tab Cart Synchronizer ───────────────────────────────────────
    window.addEventListener('storage', function(e) {
        if (e.key === 'idea_cart_count') {
            const badges = document.querySelectorAll('.cart-count-badge, #headerCartCount, #mobileCartBadge');
            badges.forEach(badge => {
                if (badge) badge.textContent = e.newValue || '0';
            });
        }
    });

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initDraftAutoSave();
            initImageFallbacks();
        });
    } else {
        initDraftAutoSave();
        initImageFallbacks();
    }
})();
