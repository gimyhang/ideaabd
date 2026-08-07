<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'আইডিয়া প্রকাশন')</title>
    <!-- Tailwind CSS CDN (দ্রুত ডিজাইনের জন্য) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
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