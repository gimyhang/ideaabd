<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ideaabd - অনলাইন বই এবং পণ্যের মার্কেটপ্লেস')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Site chrome (header/nav/footer) — served from /public, no build step needed -->
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ @filemtime(public_path('css/site.css')) ?: 1 }}">
    
    <!-- Custom Styles -->
    <style>
        * {
            font-family: 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;
        }
        
        body {
            background-color: #f8fafb;
            color: #333;
        }
        
        /* Light Sky Blue Theme */
        :root {
            --primary-light: #E8F4F8;
            --primary-dark: #0066cc;
            --primary-accent: #0099ff;
            --sky-blue: #87CEEB;
            --dark-blue: #1a3a52;
            --text-light: #666;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0099ff 0%, #0066cc 100%) !important;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3) !important;
        }
        
        .card {
            border: 1px solid rgba(0, 102, 204, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.15);
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Hover utilities */
        .hover-lift { transition: transform .2s, box-shadow .2s; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,102,204,.15) !important; }
        .cat-card { transition: transform .2s, box-shadow .2s; }
        .cat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,.1); }
        .author-card { transition: background .2s; }
        .author-card:hover { background: #ede8f5 !important; }
    </style>
    
    {{-- public/build is gitignored, so a git-only deploy may not have a manifest.
         Guarding this keeps the whole site from 500-ing when the assets are absent. --}}
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">
    <!-- Header Navigation -->
    @include('layouts.header')

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Close alert after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    new bootstrap.Alert(alert).close();
                }, 3000);
            });
        });
    </script>
    
    {{-- Both mechanisms are supported: @section('scripts') and @push('scripts') --}}
    @yield('scripts')
    @stack('scripts')
</body>
</html>
