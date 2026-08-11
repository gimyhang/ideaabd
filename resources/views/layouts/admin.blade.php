<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'অ্যাডমিন প্যানেল') — {{ config('brand.name') }}</title>

    @if (config('brand.favicon') && is_file(public_path(config('brand.favicon'))))
        <link rel="icon" href="{{ asset(config('brand.favicon')) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">@yield('heading', 'ড্যাশবোর্ড')</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">অ্যাডমিন</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2">@yield('actions')</div>
        </div>

        {{-- Flash messages --}}
        @foreach (['success' => 'circle-check', 'error' => 'circle-exclamation', 'warning' => 'triangle-exclamation', 'info' => 'circle-info'] as $key => $icon)
            @if (session($key))
                <div class="alert alert-{{ $key === 'error' ? 'danger' : $key }} alert-dismissible d-flex align-items-center" role="alert">
                    <i class="fas fa-{{ $icon }} me-2"></i>
                    <div>{{ session($key) }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <strong><i class="fas fa-circle-exclamation me-1"></i> কিছু তথ্য ঠিক করতে হবে:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle — "mini" on desktop, slide-over on mobile. Choice is remembered.
    (function () {
        var body = document.body;
        var KEY  = 'adm-side-mini';

        if (localStorage.getItem(KEY) === '1') body.classList.add('side-mini');

        document.querySelectorAll('[data-side-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    body.classList.toggle('side-open');
                } else {
                    body.classList.toggle('side-mini');
                    localStorage.setItem(KEY, body.classList.contains('side-mini') ? '1' : '0');
                }
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
