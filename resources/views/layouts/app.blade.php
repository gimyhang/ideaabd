<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'আইডিয়া প্রকাশন')</title>
    <!-- Fonts & Vite-built assets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Inter:wght@300;400;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Theme Script (preserve dark-mode preference) -->
    <script>
        if (localStorage.getItem('admin_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @if(app()->environment('local'))
        @php /* When running with Vite/Laravel plugin */ @endphp
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-classic text-gray-800">
    @include('components.header')

    <main class="min-h-screen">
        @yield('content')
    </main>

    @include('components.cart-drawer')
    @include('components.footer')
</body>
</html>