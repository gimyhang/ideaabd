/* Site core: the scripts every public page used to carry inline at the end of layouts/app.blade.php.
 * Moved here unchanged so browsers keep them cached instead of downloading them with every page.
 * Runs inside the "core" bundle, in this order: bootstrap, modal-guard, sweetalert2, this file, theme, site-enhancements. */

/* -- Flash banners and dropdown fallback -- */
// Auto-dismiss top flash notification banners gently after 8 seconds
document.addEventListener('DOMContentLoaded', function() {
    const flashAlerts = document.querySelectorAll('main > .container > .alert.alert-dismissible');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            } catch(e) {}
        }, 8000);
    });
});

// Safe Dropdown Closer Fallback (ensures outside clicks close open dropdowns without breaking Bootstrap's native handler)
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown') && !e.target.closest('[data-bs-toggle="dropdown"]')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
            m.classList.remove('show');
            var pToggle = m.parentElement ? m.parentElement.querySelector('[data-bs-toggle="dropdown"]') : null;
            if (pToggle) pToggle.setAttribute('aria-expanded', 'false');
        });
    }
});

/* -- Google Translate and language switcher -- */
// Google Translate Element Initialization
function googleTranslateElementInit() {
    try {
        new google.translate.TranslateElement({
            pageLanguage: 'bn',
            includedLanguages: 'bn,en,ar,hi,ur,es,fr,de,zh-CN,ja,tr,ru,pt,it,ko,ms,fa',
            autoDisplay: false,
            multilanguagePage: true
        }, 'google_translate_element');
    } catch(e) {}
}

// Prevent Google Translate top frame from pushing down or duplicating topbar
(function() {
    var resetBodyTop = function() {
        if (document.body && document.body.style.top && document.body.style.top !== '0px') {
            document.body.style.top = '0px';
        }
        var banner = document.querySelector('.goog-te-banner-frame');
        if (banner && banner.style.display !== 'none') banner.style.display = 'none';
    };
    window.addEventListener('load', resetBodyTop);
    if (window.MutationObserver && document.body) {
        var gtObserver = new MutationObserver(resetBodyTop);
        gtObserver.observe(document.body, { attributes: true, attributeFilter: ['style', 'class'] });
    }
})();

// Global Site Language Switcher
window.switchSiteLanguage = function(langCode, langName) {
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        var domain = window.location.hostname;
        document.cookie = name + "=" + (value || "") + expires + "; path=/;";
        document.cookie = name + "=" + (value || "") + expires + "; path=/; domain=" + domain;
        if (domain.indexOf('.') !== -1) {
            document.cookie = name + "=" + (value || "") + expires + "; path=/; domain=." + domain;
        }
    }

    function deleteCookie(name) {
        var domain = window.location.hostname;
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + domain;
        if (domain.indexOf('.') !== -1) {
            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=." + domain;
        }
    }

    if (langCode === 'bn') {
        deleteCookie('googtrans');
        setCookie('googtrans', '/bn/bn', 1);
        localStorage.setItem('site_selected_lang', 'bn');
        localStorage.setItem('site_selected_lang_name', 'বাংলা');
        fetch('/lang/bn').catch(function(){});
        window.location.reload();
    } else {
        setCookie('googtrans', '/bn/' + langCode, 30);
        localStorage.setItem('site_selected_lang', langCode);
        localStorage.setItem('site_selected_lang_name', langName || langCode);
        if (langCode === 'en') {
            fetch('/lang/en').catch(function(){});
        }

        var select = document.querySelector('.goog-te-combo');
        if (select) {
            select.value = langCode;
            select.dispatchEvent(new Event('change'));
            if (window.updateLanguageUI) {
                window.updateLanguageUI(langCode, langName);
            }
        } else {
            window.location.reload();
        }
    }
};

window.updateLanguageUI = function(langCode, langName) {
    var displayEls = document.querySelectorAll('.current-lang-display');
    displayEls.forEach(function(el) {
        if (langName) el.textContent = langName;
    });

    document.querySelectorAll('.lang-check-icon').forEach(function(icon) {
        if (icon.getAttribute('data-lang') === langCode) {
            icon.classList.remove('d-none');
            var item = icon.closest('.lang-item-btn');
            if (item) item.classList.add('active');
        } else {
            icon.classList.add('d-none');
            var item = icon.closest('.lang-item-btn');
            if (item) item.classList.remove('active');
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    var cookies = document.cookie.split(';');
    var currentLang = 'bn';
    var currentName = 'বাংলা';

    var langMap = {
        'bn': 'বাংলা',
        'en': 'English',
        'ar': 'العربية',
        'hi': 'हिन्दी',
        'ur': 'اردو',
        'es': 'Español',
        'fr': 'Français',
        'de': 'Deutsch',
        'zh-CN': '中文',
        'ja': '日本語',
        'tr': 'Türkçe',
        'ru': 'Русский'
    };

    for (var i = 0; i < cookies.length; i++) {
        var c = cookies[i].trim();
        if (c.indexOf('googtrans=') === 0) {
            var val = c.substring('googtrans='.length);
            var parts = val.split('/');
            if (parts.length >= 3 && parts[2]) {
                currentLang = parts[2];
                currentName = langMap[currentLang] || currentLang.toUpperCase();
            }
        }
    }

    if (currentLang === 'bn') {
        var localLang = localStorage.getItem('site_selected_lang');
        if (localLang && localLang !== 'bn') {
            currentLang = localLang;
            currentName = localStorage.getItem('site_selected_lang_name') || langMap[currentLang] || currentLang;
        }
    }

    window.updateLanguageUI(currentLang, currentName);
});

/* -- Copy attribution, shortcuts guard, Alt+Left -- */
(function() {

    // 2. Disable Keyboard Shortcuts (F12, View Source Ctrl+U, Inspect Ctrl+Shift+I/J/C, Save Ctrl+S)
    document.addEventListener('keydown', function(e) {
        // F12
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        var isCtrlOrCmd = e.ctrlKey || e.metaKey;

        // View Source (Ctrl + U / Cmd + Option + U)
        if (isCtrlOrCmd && (e.key === 'u' || e.key === 'U' || e.keyCode === 85)) {
            e.preventDefault();
            e.stopPropagation();
            showProtectionToast('পেজ সোর্স দেখা সুরক্ষিত ও নিষিদ্ধ।');
            return false;
        }

        // Save Page (Ctrl + S)
        if (isCtrlOrCmd && (e.key === 's' || e.key === 'S' || e.keyCode === 83)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Inspect / Developer Tools (Ctrl + Shift + I/J/C or Cmd + Option + I/J/C)
        if (isCtrlOrCmd && e.shiftKey && (
            e.key === 'I' || e.key === 'i' || e.keyCode === 73 ||
            e.key === 'J' || e.key === 'j' || e.keyCode === 74 ||
            e.key === 'C' || e.key === 'c' || e.keyCode === 67
        )) {
            e.preventDefault();
            e.stopPropagation();
            showProtectionToast('ইন্সপেক্ট এলিমেন্ট বন্ধ রাখা হয়েছে।');
            return false;
        }
    }, true);

    // 3. Smart Protected Literary Content Copy Protection (Applies ONLY to eBooks, Webzine, Author Bio & Blog Bodies)
    document.addEventListener('copy', function(e) {
        var selObj = window.getSelection();
        var selection = selObj ? selObj.toString().trim() : '';
        if (!selection || selection.length < 5) return;

        // Check if the copied text originates from a PROTECTED literary content container:
        var targetNode = selObj.anchorNode ? (selObj.anchorNode.nodeType === 1 ? selObj.anchorNode : selObj.anchorNode.parentElement) : e.target;
        
        var isProtectedArea = targetNode && targetNode.closest(
            '.ebook-content-protected, .ebook-reader-body, .webzine-article-content, .webzine-reader-body, ' +
            '.author-bio-protected, .author-bio-body, #bioTabPane, ' +
            '.blog-article-content, .ideapatra-post-body, .blog-post-content, #blogContentWrap, #mainArticleContent'
        );

        // If NOT from a protected literary body (e.g. book title, author name, category, prices, header/footer, links)
        // Allow 100% clean, normal copy without interception or attached notices!
        if (!isProtectedArea) {
            return; // Normal unrestricted copy
        }

        // If copied from protected literary writing/ebook/bio, attach attribution notice
        var pageTitle = document.title || 'আইডিয়া প্রকাশন';
        var pageUrl = window.location.href;
        var claimNotice = '\n\n' +
            '----------------------------------------------------------------------\n' +
            '© আইডিয়া প্রকাশন (Idea Prokashon) | সর্বস্বত্ব সংরক্ষিত\n' +
            'শিরোনাম: ' + pageTitle + '\n' +
            'মূল উৎস লিংক: ' + pageUrl + '\n' +
            'আইডিয়া প্রকাশনের অনুমতি ব্যতীত এই লেখার অননুমোদিত বাণিজ্যিক বা অবাণিজ্যিক পুনঃপ্রকাশ কপিরাইট আইনে শাস্তিযোগ্য অপরাধ।\n' +
            'ওয়েবসাইট: https://www.ideaabd.com\n' +
            '----------------------------------------------------------------------';

        var copyWithClaim = selection + claimNotice;

        if (e.clipboardData) {
            e.clipboardData.setData('text/plain', copyWithClaim);
            e.preventDefault();
        } else if (window.clipboardData) {
            window.clipboardData.setData('Text', copyWithClaim);
            e.preventDefault();
        }

        showCopyToast();
    });

    // 4. Developer Console Warning & Obfuscated Security Stream
    try {
        console.log(
            "%c🛑 সাবধান! STOP! | 0x7FA3 0x9B12 0x3F88 0x0119 0xEE43 0x889A",
            "color: #ef4444; font-size: 26px; font-weight: 900; -webkit-text-stroke: 1px black;"
        );
        console.log(
            "%c[SECURITY-HASH: 9f8a88c3d10e55b729a4c8e71b2390ff5b0981aa7c3d42e1]\n[DATA-STREAM: 0x22C9 0xFA4B 0x8801 0x5D3E 0x992B 0x71AF 0x44BC 0x110E]\nএটি আইডিয়া প্রকাশনের কপিরাইট ও মেধাস্বত্ব দ্বারা সুরক্ষিত ডিজিটাল প্ল্যাটফর্ম।\nঅনুমতি ব্যতীত সোর্স কোড দেখা, স্ক্র্যাপিং বা অননুমোদিত ব্যবহারের অপচেষ্টা আইনত দণ্ডনীয়।\n© Idea Prokashon | https://www.ideaabd.com",
            "color: #0284c7; font-size: 13px; font-weight: bold;"
        );
    } catch(err){}

    var toastTimer = null;
    function showCopyToast() {
        var toast = document.getElementById('copyClaimToast');
        if (!toast) return;
        toast.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function() {
            toast.style.display = 'none';
        }, 4000);
    }

    function showProtectionToast(msg) {
        var toast = document.getElementById('copyClaimToast');
        if (!toast) return;
        var textSpan = toast.querySelector('span');
        if (textSpan && msg) {
            textSpan.textContent = msg;
        }
        toast.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function() {
            toast.style.display = 'none';
            if (textSpan) {
                textSpan.textContent = 'লেখাটি কপি করা হয়েছে! মেধা-স্বত্ব আইন অনুসারে মূল উৎস ও কপিরাইট তথ্য যুক্ত করা হয়েছে।';
            }
        }, 3000);
    }
})();

// Global Alt+Left Navigation (Never hijack Backspace)
document.addEventListener('keydown', function(e) {
    var activeEl = document.activeElement;
    var tag = (activeEl && activeEl.tagName) ? activeEl.tagName.toLowerCase() : '';
    var isInput = tag === 'input' || tag === 'textarea' || tag === 'select' || (activeEl && activeEl.isContentEditable);
    
    if (!isInput && e.altKey && e.key === 'ArrowLeft') {
        if (window.history.length > 1 && document.referrer) {
            e.preventDefault();
            window.history.back();
        }
    }
});

/* -- SweetAlert2 confirmation helper -- */
// Global SweetAlert2 World-Class Confirmation Helper
window.SwalConfirm = function(options) {
    if (typeof options === 'string') options = { text: options };
    return Swal.fire({
        title: options.title || 'আপনি কি নিশ্চিত?',
        text: options.text || '',
        html: options.html || undefined,
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: options.confirmButtonText || '<i class="fas fa-check-circle me-1.5"></i> হ্যাঁ, নিশ্চিত করুন',
        cancelButtonText: options.cancelButtonText || '<i class="fas fa-times me-1.5"></i> বাতিল',
        reverseButtons: true,
        focusCancel: true,
        background: '#ffffff',
        color: '#0f172a',
        backdrop: 'rgba(15, 23, 42, 0.65)',
        customClass: {
            popup: 'swal2-modern-popup rounded-4 shadow-2xl border p-4',
            title: 'fw-bold fs-5 mb-2',
            htmlContainer: 'text-muted small mb-4 lh-base',
            confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-bold mx-1.5 shadow-sm d-inline-flex align-items-center gap-1',
            cancelButton: 'btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold mx-1.5 shadow-2xs d-inline-flex align-items-center gap-1'
        },
        buttonsStyling: false
    });
};

// Automatic SweetAlert2 Form & Button Confirmation Interceptor
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form) return;
    if (form.dataset && form.dataset.confirm && !form.dataset.confirmed) {
        e.preventDefault();
        e.stopImmediatePropagation();
        SwalConfirm({
            title: form.dataset.confirmTitle || 'নিশ্চিতকরণ প্রয়োজন',
            text: form.dataset.confirm,
            icon: form.dataset.confirmIcon || 'warning',
            confirmButtonText: form.dataset.confirmBtn || '<i class="fas fa-check-circle me-1"></i> হ্যাঁ, নিশ্চিত করুন'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
        return;
    }
    var onsubmitAttr = form.getAttribute('onsubmit');
    if (onsubmitAttr && onsubmitAttr.includes('confirm(') && !form.dataset.confirmed) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var match = onsubmitAttr.match(/confirm\(\s*['"`](.*?)['"`]\s*\)/);
        var confirmMsg = match ? match[1].replace(/\\'/g, "'").replace(/\\"/g, '"') : 'আপনি কি নিশ্চিত?';
        SwalConfirm({
            title: form.dataset.confirmTitle || 'নিশ্চিতকরণ প্রয়োজন',
            text: confirmMsg,
            icon: form.dataset.confirmIcon || 'warning'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    }
}, true);

document.addEventListener('click', function(e) {
    var trigger = e.target.closest('[onclick*="confirm("], [data-confirm]');
    if (!trigger || trigger.dataset.confirmed) return;
    var onclickAttr = trigger.getAttribute('onclick');
    var confirmMsg = trigger.dataset.confirm;
    if (!confirmMsg && onclickAttr && onclickAttr.includes('confirm(')) {
        var match = onclickAttr.match(/confirm\(\s*['"`](.*?)['"`]\s*\)/);
        confirmMsg = match ? match[1].replace(/\\'/g, "'").replace(/\\"/g, '"') : 'আপনি কি নিশ্চিত?';
    }
    if (confirmMsg) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var form = trigger.closest('form');
        var isLink = trigger.tagName.toLowerCase() === 'a';
        SwalConfirm({
            title: trigger.dataset.confirmTitle || 'নিশ্চিতকরণ প্রয়োজন',
            text: confirmMsg,
            icon: trigger.dataset.confirmIcon || 'warning'
        }).then(function(result) {
            if (result.isConfirmed) {
                trigger.dataset.confirmed = 'true';
                if (form && trigger.type === 'submit') {
                    form.dataset.confirmed = 'true';
                    form.submit();
                } else if (isLink && trigger.href && !onclickAttr) {
                    window.location.href = trigger.href;
                } else {
                    var origConfirm = window.confirm;
                    window.confirm = function() { return true; };
                    try {
                        trigger.click();
                    } finally {
                        window.confirm = origConfirm;
                        setTimeout(function() { delete trigger.dataset.confirmed; }, 100);
                    }
                }
            }
        });
    }
}, true);

/* -- Google Translate loads only for a visitor who chose another language --
 * The translate widget is ~4 extra requests and a busy script; a Bengali reader never needs it.
 * switchSiteLanguage() sets the googtrans cookie and reloads when the widget is absent, so the next page loads it. */
(function () {
    function wanted() {
        var m = document.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/);
        if (!m) return false;
        var lang = decodeURIComponent(m[1]).split('/').pop();
        return !!lang && lang !== 'bn';
    }
    window.ideaLoadTranslate = function () {
        if (window.__ideaTranslateLoading) return;
        window.__ideaTranslateLoading = true;
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        document.body.appendChild(s);
    };
    if (wanted()) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', window.ideaLoadTranslate);
        else window.ideaLoadTranslate();
    }
})();
