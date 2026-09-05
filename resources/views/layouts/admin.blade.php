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
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 d-print-none">
            <div>
                <h1 class="h4 fw-bold mb-1">@yield('heading', 'Dashboard')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Admin</a></li>
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
        var isDark = document.body.classList.contains('dark-mode');
        return Swal.fire({
            title: options.title || 'Are you sure?',
            text: options.text || '',
            html: options.html || undefined,
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.confirmButtonColor || '#0284c7',
            cancelButtonColor: options.cancelButtonColor || '#64748b',
            confirmButtonText: options.confirmButtonText || '<i class="fas fa-check me-1"></i> Yes, Confirm',
            cancelButtonText: options.cancelButtonText || '<i class="fas fa-times me-1"></i> Cancel',
            reverseButtons: true,
            focusCancel: options.focusCancel || false,
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#1e293b',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-slate-200',
                confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-semibold mx-1 shadow-sm',
                cancelButton: 'btn btn-secondary rounded-pill px-4 py-2 fw-semibold mx-1 shadow-sm'
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

    // Automatic SweetAlert2 Form Confirmation Interceptor
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form && form.dataset && form.dataset.confirm && !form.dataset.confirmed) {
            e.preventDefault();
            SwalConfirm({
                title: form.dataset.confirmTitle || 'আপনি কি নিশ্চিত?',
                text: form.dataset.confirm,
                icon: form.dataset.confirmIcon || 'warning',
                confirmButtonText: form.dataset.confirmBtn || '<i class="fas fa-check me-1"></i> হ্যাঁ, নিশ্চিত',
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    });

    // Sidebar & Dark Mode toggle — stored in localStorage
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

        document.querySelectorAll('[data-side-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    body.classList.toggle('side-open');
                } else {
                    body.classList.toggle('side-mini');
                    localStorage.setItem(MINI_KEY, body.classList.contains('side-mini') ? '1' : '0');
                }
            });
        });

        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                body.classList.toggle('dark-mode');
                var isDark = body.classList.contains('dark-mode');
                localStorage.setItem(DARK_KEY, isDark ? '1' : '0');
            });
        });

        document.querySelectorAll('[data-side-close]').forEach(function (el) {
            el.addEventListener('click', function () { body.classList.remove('side-open'); });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
