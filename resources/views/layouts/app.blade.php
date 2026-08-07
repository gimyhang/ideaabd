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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    @if(app()->environment('local'))
        @php /* When running with Vite/Laravel plugin */ @endphp
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-classic text-gray-800">
    <nav class="bg-white shadow mb-6 p-4">
        <div class="container mx-auto flex gap-4">
            <a href="/" class="text-blue-600 font-bold">হোম</a>
            <a href="/about" class="text-gray-700">আমাদের সম্পর্কে</a>
            <a href="/contact" class="text-gray-700">যোগাযোগ</a>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>