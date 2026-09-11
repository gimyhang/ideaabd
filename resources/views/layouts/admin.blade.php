<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#1e293b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin Panel') — {{ \App\Support\SiteSetting::name() }}</title>

    {{-- Dynamic Site Favicon --}}
    @php $adminFaviconUrl = \App\Support\SiteSetting::faviconUrl(); @endphp
    @if ($adminFaviconUrl)
        <link rel="icon" href="{{ $adminFaviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $adminFaviconUrl }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;600;700;800&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Admin stylesheet lives in /public so deploys need no vite build step --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: 1 }}">

    @stack('styles')
</head>
<body>

@include('admin.partials.sidebar')
<div class="adm-backdrop" data-side-close></div>

<div class="adm-main">
    @include('admin.partials.topbar')

    <div class="adm-content">
        {{-- Page heading + breadcrumb --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 d-print-none">
            <div>
                <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">@yield('heading', 'Dashboard')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary"><i class="fas fa-home-alt me-1"></i>Admin</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2">@yield('actions')</div>
        </div>

        {{-- Flash messages --}}
        @foreach (['success' => 'circle-check', 'error' => 'circle-exclamation', 'warning' => 'triangle-exclamation', 'info' => 'circle-info'] as $key => $icon)
            @if (session($key))
                <div class="alert alert-{{ $key === 'error' ? 'danger' : $key }} alert-dismissible d-flex align-items-center d-print-none" role="alert">
                    <i class="fas fa-{{ $icon }} me-2"></i>
                    <div>{{ session($key) }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        @endforeach

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible">
                <strong><i class="fas fa-circle-exclamation me-1"></i> Please fix the following errors:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Global SweetAlert2 World-Class Confirmation Helper
    window.SwalConfirm = function(options) {
        if (typeof options === 'string') {
            options = { text: options };
        }
        var isDark = document.body.classList.contains('dark-mode');
        var isDanger = options.icon === 'error' || options.icon === 'danger' || options.isDanger || (options.confirmButtonColor && (options.confirmButtonColor.includes('#dc') || options.confirmButtonColor.includes('#ef'))) || false;
        
        return Swal.fire({
            title: options.title || 'আপনি কি নিশ্চিত?',
            text: options.text || '',
            html: options.html || undefined,
            icon: (options.icon === 'danger' ? 'error' : options.icon) || 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || '<i class="fas fa-check-circle me-1.5"></i> হ্যাঁ, নিশ্চিত করুন',
            cancelButtonText: options.cancelButtonText || '<i class="fas fa-times me-1.5"></i> বাতিল',
            reverseButtons: true,
            focusCancel: options.focusCancel !== undefined ? options.focusCancel : true,
            background: isDark ? '#0f172a' : '#ffffff',
            color: isDark ? '#f8fafc' : '#0f172a',
            backdrop: 'rgba(15, 23, 42, 0.65)',
            customClass: {
                popup: 'swal2-modern-popup rounded-4 shadow-2xl border p-4',
                title: 'fw-bold fs-5 mb-2',
                htmlContainer: 'text-muted small mb-4 lh-base',
                confirmButton: 'btn ' + (isDanger ? 'btn-danger' : 'btn-primary') + ' rounded-pill px-4 py-2 fw-bold mx-1.5 shadow-sm d-inline-flex align-items-center gap-1',
                cancelButton: 'btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold mx-1.5 shadow-2xs d-inline-flex align-items-center gap-1'
            },
            buttonsStyling: false
        });
    };

    // Global SweetAlert2 Toast Helper
    window.SwalToast = function(type, message) {
        var isDark = document.body.classList.contains('dark-mode');
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#1e293b',
            didOpen: function(toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({
            icon: type || 'success',
            title: message
        });
    };

    // Automatic SweetAlert2 Form & Button Confirmation Interceptor
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form) return;

        // 1. If form has data-confirm attribute
        if (form.dataset && form.dataset.confirm && !form.dataset.confirmed) {
            e.preventDefault();
            e.stopImmediatePropagation();
            SwalConfirm({
                title: form.dataset.confirmTitle || 'নিশ্চিতকরণ প্রয়োজন',
                text: form.dataset.confirm,
                icon: form.dataset.confirmIcon || 'warning',
                confirmButtonText: form.dataset.confirmBtn || '<i class="fas fa-check-circle me-1"></i> হ্যাঁ, নিশ্চিত করুন',
                cancelButtonText: form.dataset.cancelBtn || '<i class="fas fa-times me-1"></i> বাতিল'
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
            return;
        }

        // 2. Intercept legacy inline onsubmit="return confirm(...)"
        var onsubmitAttr = form.getAttribute('onsubmit');
        if (onsubmitAttr && onsubmitAttr.includes('confirm(') && !form.dataset.confirmed) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            // Extract the confirm message from inside confirm('...') or confirm("...")
            var match = onsubmitAttr.match(/confirm\(\s*['"`](.*?)['"`]\s*\)/);
            var confirmMsg = match ? match[1].replace(/\\'/g, "'").replace(/\\"/g, '"') : 'আপনি কি নিশ্চিত?';
            
            SwalConfirm({
                title: form.dataset.confirmTitle || 'নিশ্চিতকরণ প্রয়োজন',
                text: confirmMsg,
                icon: form.dataset.confirmIcon || 'warning',
                confirmButtonText: form.dataset.confirmBtn || '<i class="fas fa-check-circle me-1"></i> হ্যাঁ, নিশ্চিত করুন',
                cancelButtonText: '<i class="fas fa-times me-1"></i> বাতিল'
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    }, true);

    // Intercept legacy inline onclick="return confirm(...)" on buttons/links
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
                icon: trigger.dataset.confirmIcon || 'warning',
                confirmButtonText: trigger.dataset.confirmBtn || '<i class="fas fa-check-circle me-1"></i> হ্যাঁ, নিশ্চিত করুন',
                cancelButtonText: '<i class="fas fa-times me-1"></i> বাতিল'
            }).then(function(result) {
                if (result.isConfirmed) {
                    trigger.dataset.confirmed = 'true';
                    if (form && trigger.type === 'submit') {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    } else if (isLink && trigger.href) {
                        window.location.href = trigger.href;
                    }
                }
            });
        }
    }, true);

    // World-Class Sidebar, Touch Gestures & Dark Mode Controller
    (function () {
        var body = document.body;
        var MINI_KEY = 'adm-side-mini';
        var DARK_KEY = 'adm-dark-mode';

        if (localStorage.getItem(MINI_KEY) === '1') body.classList.add('side-mini');
        if (localStorage.getItem(DARK_KEY) === '1') body.classList.add('dark-mode');

        var dynBrand = localStorage.getItem('adm-dynamic-brand');
        var dynBrand2 = localStorage.getItem('adm-dynamic-brand2');
        if (dynBrand) document.documentElement.style.setProperty('--brand', dynBrand);
        if (dynBrand2) document.documentElement.style.setProperty('--brand-2', dynBrand2);

        function isMobile() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        function closeMobileSidebar() {
            if (body.classList.contains('side-open')) {
                body.classList.remove('side-open');
            }
        }

        function openMobileSidebar() {
            if (!body.classList.contains('side-open')) {
                body.classList.add('side-open');
                window.dispatchEvent(new Event('side-open'));
            }
        }

        function toggleSidebar() {
            if (isMobile()) {
                body.classList.toggle('side-open');
                if (body.classList.contains('side-open')) {
                    window.dispatchEvent(new Event('side-open'));
                }
            } else {
                body.classList.toggle('side-mini');
                localStorage.setItem(MINI_KEY, body.classList.contains('side-mini') ? '1' : '0');
            }
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
            }, 300);
        }

        // Toggle buttons click event
        document.querySelectorAll('[data-side-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                toggleSidebar();
            });
        });

        // Close sidebar triggers (backdrop, close buttons)
        document.querySelectorAll('[data-side-close]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                closeMobileSidebar();
            });
        });

        // Auto-close mobile drawer when any link in sidebar is tapped
        document.addEventListener('click', function(e) {
            if (isMobile()) {
                var navLink = e.target.closest('.adm-nav__link, .adm-side__fav-chip');
                if (navLink && !navLink.getAttribute('target')) {
                    closeMobileSidebar();
                }
            }
        });

        // Escape key closes mobile sidebar or spotlight search
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
            }
        });

        // ── Mobile Touch Gestures (Swipe to Open / Swipe to Close) ──
        var touchStartX = 0;
        var touchStartY = 0;
        var touchEndX = 0;
        var touchEndY = 0;
        var isEdgeSwipe = false;

        document.addEventListener('touchstart', function(e) {
            if (!isMobile()) return;
            var touch = e.touches[0];
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
            touchEndX = touch.clientX;
            touchEndY = touch.clientY;
            // Detect if swipe starts from the left edge (within 35px)
            isEdgeSwipe = (touchStartX <= 35 && !body.classList.contains('side-open'));
        }, { passive: true });

        document.addEventListener('touchmove', function(e) {
            if (!isMobile()) return;
            var touch = e.touches[0];
            touchEndX = touch.clientX;
            touchEndY = touch.clientY;
        }, { passive: true });

        document.addEventListener('touchend', function(e) {
            if (!isMobile()) return;
            var deltaX = touchEndX - touchStartX;
            var deltaY = touchEndY - touchStartY;

            // Ensure horizontal swipe is dominant (not vertical scrolling)
            if (Math.abs(deltaX) > Math.abs(deltaY) * 1.5) {
                // Swipe Right from edge -> Open Sidebar
                if (isEdgeSwipe && deltaX > 50) {
                    openMobileSidebar();
                }
                // Swipe Left anywhere when open -> Close Sidebar
                else if (body.classList.contains('side-open') && deltaX < -50) {
                    closeMobileSidebar();
                }
            }
            isEdgeSwipe = false;
        }, { passive: true });

        // Clean up classes on desktop/mobile viewport resizing
        window.addEventListener('resize', function() {
            if (!isMobile() && body.classList.contains('side-open')) {
                body.classList.remove('side-open');
            }
        });

        // Theme toggle
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                body.classList.toggle('dark-mode');
                var isDark = body.classList.contains('dark-mode');
                localStorage.setItem(DARK_KEY, isDark ? '1' : '0');
            });
        });

        // Smart Backspace / Alt+ArrowLeft Navigation for Admin Dashboards
        document.addEventListener('keydown', function(e) {
            var activeEl = document.activeElement;
            if (!activeEl) return;
            var tag = activeEl.tagName ? activeEl.tagName.toLowerCase() : '';
            var isInput = tag === 'input' || tag === 'textarea' || tag === 'select' || 
                          activeEl.isContentEditable || 
                          activeEl.classList.contains('ql-editor') || 
                          activeEl.classList.contains('ck-editor__editable') || 
                          activeEl.getAttribute('contenteditable') === 'true' ||
                          activeEl.closest('.ql-editor, .ck-editor, .note-editor, [contenteditable="true"]');
            
            // If user is not typing in a form field or editor
            if (!isInput) {
                // Check if user pressed Backspace (without modifier) or Alt + ArrowLeft
                if (e.key === 'Backspace' || (e.altKey && e.key === 'ArrowLeft')) {
                    if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                        e.preventDefault();
                        window.history.back();
                    } else if (window.location.pathname !== '/admin' && window.location.pathname !== '/admin/dashboard') {
                        e.preventDefault();
                        window.location.href = "{{ route('admin.dashboard') }}";
                    }
                }
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
