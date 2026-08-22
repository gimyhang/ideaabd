<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'লেখক ড্যাশবোর্ড — আইডিয়া প্রকাশন')</title>

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        :root {
            --brand-primary: #4338ca;
            --brand-accent: #6366f1;
            --brand-bg: #f8fafc;
            --brand-sidebar: #0f172a;
            --brand-text: #1e293b;
        }
        body {
            font-family: 'Hind Siliguri', 'Inter', sans-serif;
            background-color: var(--brand-bg);
            color: var(--brand-text);
            min-height: 100vh;
        }
        .author-sidebar {
            width: 260px;
            background: var(--brand-sidebar);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.3s ease;
        }
        .author-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 991.98px) {
            .author-sidebar { margin-left: -260px; }
            .author-sidebar.show { margin-left: 0; }
            .author-main { margin-left: 0; }
        }
        .author-nav-link {
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            border-radius: 0.75rem;
            margin-bottom: 0.25rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .author-nav-link:hover {
            color: #f8fafc;
            background: rgba(255,255,255,0.08);
        }
        .author-nav-link.active {
            color: #ffffff;
            background: var(--brand-primary);
            box-shadow: 0 4px 12px rgba(67, 56, 202, 0.35);
        }
        .author-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .author-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06);
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside class="author-sidebar p-3 d-flex flex-column" id="authorSidebar">
        {{-- Brand Header --}}
        <div class="d-flex align-items-center justify-content-between px-2 py-3 mb-3 border-bottom border-secondary border-opacity-25">
            <a href="{{ route('author.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-white">
                <span class="p-2 bg-primary rounded-3"><i class="fas fa-feather-pointed"></i></span>
                <div>
                    <h6 class="mb-0 fw-bold text-white font-monospace">IDEA KDP</h6>
                    <small class="text-white-50" style="font-size: 10.5px;">লেখক সেলফ-পাবলিশিং</small>
                </div>
            </a>
            <button class="btn btn-sm btn-link text-white-50 d-lg-none" onclick="document.getElementById('authorSidebar').classList.toggle('show')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Author Wallet Card in Sidebar --}}
        @php
            $authRecord = auth()->user()->getAuthorRecord();
        @endphp
        <div class="p-3 mb-3 rounded-3 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border: 1px solid rgba(255,255,255,0.1);">
            <div class="small text-white-50 mb-1 d-flex align-items-center justify-content-between">
                <span>রয়্যালটি ওয়ালেট (৫০%)</span>
                <i class="fas fa-wallet text-warning"></i>
            </div>
            <h4 class="fw-bold mb-1 text-warning font-monospace">৳{{ number_format($authRecord?->wallet_balance ?? 0, 2) }}</h4>
            <a href="{{ route('author.payouts.index') }}" class="btn btn-xs btn-outline-light rounded-pill w-100 py-1 text-decoration-none fw-semibold" style="font-size: 11px;">
                <i class="fas fa-money-bill-transfer me-1"></i> উত্তোলন (Payout)
            </a>
        </div>

        {{-- Navigation Menu --}}
        <nav class="flex-grow-1 overflow-y-auto pe-1">
            <a href="{{ route('author.dashboard') }}" class="author-nav-link {{ request()->routeIs('author.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line text-info"></i>
                <span>ড্যাশবোর্ড (Dashboard)</span>
            </a>

            {{-- SECTION 1: E-BOOKS & ROYALTIES --}}
            <div class="text-white-50 text-uppercase fw-bold px-3 pt-3 pb-1" style="font-size: 10px; letter-spacing: 0.8px;">
                ই-বুক ও রয়্যালটি (KDP)
            </div>
            <a href="{{ route('author.ebooks.index') }}" class="author-nav-link {{ request()->routeIs('author.ebooks.index') ? 'active' : '' }}">
                <i class="fas fa-book-open text-primary"></i>
                <span>আমার ই-বুকসমূহ</span>
            </a>
            <a href="{{ route('author.ebooks.create') }}" class="author-nav-link text-white bg-success bg-opacity-25 border border-success border-opacity-25 my-1">
                <i class="fas fa-plus-circle text-success"></i>
                <span class="fw-bold">নতুন ই-বুক আপলোড</span>
            </a>
            <a href="{{ route('author.royalties') }}" class="author-nav-link {{ request()->routeIs('author.royalties') ? 'active' : '' }}">
                <i class="fas fa-receipt text-warning"></i>
                <span>রয়্যালটি লেজার (৫০%)</span>
            </a>
            <a href="{{ route('author.payouts.index') }}" class="author-nav-link {{ request()->routeIs('author.payouts.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-dollar text-success"></i>
                <span>উত্তোলন ও পে-আউট</span>
            </a>

            {{-- SECTION 2: IDEAPATRA (BLOG & ARTICLES) --}}
            <div class="text-white-50 text-uppercase fw-bold px-3 pt-3 pb-1" style="font-size: 10px; letter-spacing: 0.8px;">
                আইডিয়াপত্র (IdeaPatra)
            </div>
            <a href="{{ route('author.posts.index') }}" class="author-nav-link {{ request()->routeIs('author.posts.index') || request()->routeIs('author.posts.edit') ? 'active' : '' }}">
                <i class="fas fa-feather-pointed text-primary"></i>
                <span>আমার আইডিয়াপত্র</span>
            </a>
            <a href="{{ route('author.posts.create') }}" class="author-nav-link {{ request()->routeIs('author.posts.create') ? 'active' : '' }}">
                <i class="fas fa-pen-nib text-warning"></i>
                <span>নতুন আইডিয়াপত্র লিখুন</span>
            </a>
            <a href="{{ route('blog.index') }}" target="_blank" class="author-nav-link">
                <i class="fas fa-newspaper text-info"></i>
                <span>আইডিয়াপত্র লাইভ ফিড</span>
            </a>
        </nav>

        {{-- Footer User Profile --}}
        <div class="pt-3 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 text-white text-truncate">
                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <div class="small fw-bold text-truncate text-white">{{ auth()->user()->name }}</div>
                    <small class="text-white-50 d-block text-truncate" style="font-size: 11px;">লেখক (Author)</small>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-white-50 p-1" title="লগআউট">
                    <i class="fas fa-power-off"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="author-main">
        {{-- Top Navbar --}}
        <header class="bg-white border-bottom py-2.5 px-4 d-flex align-items-center justify-content-between sticky-top shadow-xs">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-pill" onclick="document.getElementById('authorSidebar').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="fw-bold mb-0 text-dark">@yield('heading', 'লেখক সেলফ-পাবলিশিং পোর্টাল')</h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="fas fa-store me-1"></i> লাইভ স্টোর দেখুন
                </a>
            </div>
        </header>

        {{-- Page Body --}}
        <main class="flex-grow-1 p-3 p-md-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-4 mb-4 shadow-xs" role="alert">
                    <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center rounded-4 mb-4 shadow-xs" role="alert">
                    <i class="fas fa-circle-exclamation fs-5 me-2 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
            © {{ date('Y') }} আইডিয়া প্রকাশন (IDEA Publication) • Author Self-Publishing & E-Book Royalty Engine
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
