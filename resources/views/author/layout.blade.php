<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'লেখক ড্যাশবোর্ড — আইডিয়া প্রকাশন')</title>

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@400;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('{{ asset('fonts/kalpurush/kalpurush.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --brand-primary: #4338ca;
            --brand-accent: #6366f1;
            --brand-bg: #f8fafc;
            --brand-sidebar: #0f172a;
            --brand-text: #1e293b;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Hind Siliguri', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--brand-bg);
            color: var(--brand-text);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Desktop Sidebar */
        .author-sidebar {
            width: 260px;
            background: var(--brand-sidebar);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
        }
        
        /* Main Layout */
        .author-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 260px);
            background: var(--brand-bg);
        }

        /* Mobile Viewport Adjustments */
        @media (max-width: 991.98px) {
            .author-sidebar {
                display: none !important;
            }
            .author-main {
                margin-left: 0 !important;
                width: 100% !important;
                padding-bottom: 95px !important; /* Proper clearance for fixed bottom mobile nav & FAB */
            }
        }

        .author-nav-link {
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.90rem;
            padding: 0.65rem 1.15rem;
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
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .author-card:hover {
            box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.08);
        }

        /* Mobile Bottom App Navigation Bar */
        .author-mobile-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #0f172a;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-top: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 -4px 16px rgba(0,0,0,0.22);
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
        .bottom-nav-item {
            color: #94a3b8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            min-width: 48px;
            font-size: 0.72rem;
            font-weight: 600;
            text-decoration: none;
            gap: 2px;
            flex: 1;
            padding: 6px 2px;
            transition: color 0.15s, transform 0.15s;
            position: relative;
        }
        .bottom-nav-item i {
            font-size: 1.2rem;
        }
        .bottom-nav-item.active {
            color: #38bdf8;
        }
        .bottom-nav-item.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 3px;
            background: #38bdf8;
            border-radius: 0 0 3px 3px;
        }
        .bottom-nav-item:hover {
            color: #e2e8f0;
        }
        .bottom-nav-item:active {
            transform: scale(0.94);
        }

        /* Offcanvas Mobile Drawer Styling */
        .offcanvas-author {
            background: #0f172a !important;
            color: #f8fafc;
            width: 290px !important;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Desktop Fixed Sidebar -->
    <aside class="author-sidebar p-3 d-none d-lg-flex" id="authorDesktopSidebar">
        {{-- Brand Header --}}
        <div class="d-flex align-items-center justify-content-between px-2 py-3 mb-3 border-bottom border-secondary border-opacity-25">
            <a href="{{ route('author.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-white">
                <span class="p-2 bg-primary rounded-3"><i class="fas fa-feather-pointed"></i></span>
                <div>
                    <h6 class="mb-0 fw-bold text-white font-monospace">IDEA KDP</h6>
                    <small class="text-white-50" style="font-size: 10.5px;">Author Studio</small>
                </div>
            </a>
        </div>

        {{-- Author Wallet Card in Sidebar --}}
        @php
            $authRecord = auth()->user()->getAuthorRecord();
        @endphp
        <div class="p-3 mb-3 rounded-3 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border: 1px solid rgba(255,255,255,0.1);">
            <div class="small text-white-50 mb-1 d-flex align-items-center justify-content-between">
                <span>Wallet (50%)</span>
                <i class="fas fa-wallet text-warning"></i>
            </div>
            <h4 class="fw-bold mb-1 text-warning font-monospace">৳{{ number_format($authRecord?->wallet_balance ?? 0, 2) }}</h4>
            <a href="{{ route('author.payouts.index') }}" class="btn btn-xs btn-outline-light rounded-pill w-100 py-1 text-decoration-none fw-semibold" style="font-size: 11px;">
                <i class="fas fa-money-bill-transfer me-1"></i> Payout
            </a>
        </div>

        {{-- Navigation Menu --}}
        <nav class="flex-grow-1 overflow-y-auto pe-1">
            <a href="{{ route('author.dashboard') }}" class="author-nav-link {{ request()->routeIs('author.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line text-info"></i>
                <span>Dashboard</span>
            </a>

            {{-- SECTION 1: E-BOOKS & ROYALTIES --}}
            <div class="text-white-50 text-uppercase fw-bold px-3 pt-3 pb-1" style="font-size: 10px; letter-spacing: 0.8px;">
                E-Books & Royalty
            </div>
            <a href="{{ route('author.ebooks.index') }}" class="author-nav-link {{ request()->routeIs('author.ebooks.index') ? 'active' : '' }}">
                <i class="fas fa-book-open text-primary"></i>
                <span>My E-Books</span>
            </a>
            <a href="{{ route('author.ebooks.create') }}" class="author-nav-link text-white bg-success bg-opacity-25 border border-success border-opacity-25 my-1">
                <i class="fas fa-plus-circle text-success"></i>
                <span class="fw-bold">Upload E-Book</span>
            </a>
            <a href="{{ route('author.royalties') }}" class="author-nav-link {{ request()->routeIs('author.royalties') ? 'active' : '' }}">
                <i class="fas fa-receipt text-warning"></i>
                <span>Royalties (50%)</span>
            </a>
            <a href="{{ route('author.payouts.index') }}" class="author-nav-link {{ request()->routeIs('author.payouts.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-dollar text-success"></i>
                <span>Payouts</span>
            </a>

            {{-- SECTION 2: POSTS & ARTICLES --}}
            <div class="text-white-50 text-uppercase fw-bold px-3 pt-3 pb-1" style="font-size: 10px; letter-spacing: 0.8px;">
                Posts & Tips
            </div>
            <a href="{{ route('author.posts.index') }}" class="author-nav-link {{ request()->routeIs('author.posts.index') || request()->routeIs('author.posts.edit') ? 'active' : '' }}">
                <i class="fas fa-feather-pointed text-primary"></i>
                <span>My Posts</span>
            </a>
            <a href="{{ route('author.honorariums') }}" class="author-nav-link {{ request()->routeIs('author.honorariums') ? 'active' : '' }}">
                <i class="fas fa-heart text-danger"></i>
                <span>Reader Tips</span>
            </a>
            <a href="{{ route('author.posts.create') }}" class="author-nav-link {{ request()->routeIs('author.posts.create') ? 'active' : '' }}">
                <i class="fas fa-pen-nib text-warning"></i>
                <span>Write Post</span>
            </a>
            <a href="{{ route('blog.index') }}" target="_blank" class="author-nav-link">
                <i class="fas fa-newspaper text-info"></i>
                <span>Live Feed</span>
            </a>
        </nav>

        {{-- Footer User Profile --}}
        @php
            $authorAvatar = auth()->user()->avatar ?: (auth()->user()->reg_data['avatar'] ?? ($authRecord?->avatar ?? null));
            $authorAvatarUrl = $authorAvatar ? (str_starts_with($authorAvatar, 'http') ? $authorAvatar : asset('storage/' . ltrim($authorAvatar, '/'))) : null;
        @endphp
        <div class="pt-3 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 text-white text-truncate">
                <div class="rounded-circle overflow-hidden bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 border border-white border-opacity-25" style="width: 38px; height: 38px; min-width: 38px; aspect-ratio: 1/1;">
                    @if($authorAvatarUrl)
                        <img src="{{ $authorAvatarUrl }}" alt="{{ auth()->user()->name }}" class="w-100 h-100 object-fit-cover header-author-avatar-img">
                    @else
                        <span>{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="overflow-hidden">
                    <div class="small fw-bold text-truncate text-white">{{ auth()->user()->name }}</div>
                    <small class="text-white-50 d-block text-truncate" style="font-size: 11px;">Author</small>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-white-50 p-1" title="Logout">
                    <i class="fas fa-power-off"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Offcanvas Drawer -->
    <div class="offcanvas offcanvas-start offcanvas-author d-lg-none" tabindex="-1" id="mobileAuthorDrawer" aria-labelledby="mobileAuthorDrawerLabel">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25 px-3 py-3">
            <div class="d-flex align-items-center gap-2" id="mobileAuthorDrawerLabel">
                <span class="p-2 bg-primary rounded-3 text-white"><i class="fas fa-feather-pointed"></i></span>
                <div>
                    <h6 class="mb-0 fw-bold text-white font-monospace">IDEA KDP</h6>
                    <small class="text-white-50" style="font-size: 10px;">Author Studio</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3 d-flex flex-column">
            <div class="p-3 mb-3 rounded-3 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border: 1px solid rgba(255,255,255,0.1);">
                <div class="small text-white-50 mb-1 d-flex align-items-center justify-content-between">
                    <span>Wallet (50%)</span>
                    <i class="fas fa-wallet text-warning"></i>
                </div>
                <h4 class="fw-bold mb-1 text-warning font-monospace">৳{{ number_format($authRecord?->wallet_balance ?? 0, 2) }}</h4>
                <a href="{{ route('author.payouts.index') }}" class="btn btn-xs btn-outline-light rounded-pill w-100 py-1 text-decoration-none fw-semibold" style="font-size: 11px;">
                    <i class="fas fa-money-bill-transfer me-1"></i> Payout
                </a>
            </div>

            <nav class="flex-grow-1 overflow-y-auto pe-1">
                <a href="{{ route('author.dashboard') }}" class="author-nav-link {{ request()->routeIs('author.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line text-info"></i>
                    <span>Dashboard</span>
                </a>

                <div class="text-white-50 text-uppercase fw-bold px-3 pt-3 pb-1" style="font-size: 10px; letter-spacing: 0.8px;">
                    E-Books & Royalty
                </div>
                <a href="{{ route('author.ebooks.index') }}" class="author-nav-link {{ request()->routeIs('author.ebooks.index') ? 'active' : '' }}">
                    <i class="fas fa-book-open text-primary"></i>
                    <span>My E-Books</span>
                </a>
                <a href="{{ route('author.ebooks.create') }}" class="author-nav-link text-white bg-success bg-opacity-25 border border-success border-opacity-25 my-1">
                    <i class="fas fa-plus-circle text-success"></i>
                    <span class="fw-bold">Upload E-Book</span>
                </a>
                <a href="{{ route('author.royalties') }}" class="author-nav-link {{ request()->routeIs('author.royalties') ? 'active' : '' }}">
                    <i class="fas fa-receipt text-warning"></i>
                    <span>Royalties</span>
                </a>
                <a href="{{ route('author.payouts.index') }}" class="author-nav-link {{ request()->routeIs('author.payouts.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-dollar text-success"></i>
                    <span>Payouts</span>
                </a>

                <div class="text-white-50 text-uppercase fw-bold px-3 pt-3 pb-1" style="font-size: 10px; letter-spacing: 0.8px;">
                    Posts & Tips
                </div>
                <a href="{{ route('author.posts.index') }}" class="author-nav-link {{ request()->routeIs('author.posts.index') || request()->routeIs('author.posts.edit') ? 'active' : '' }}">
                    <i class="fas fa-feather-pointed text-primary"></i>
                    <span>My Posts</span>
                </a>
                <a href="{{ route('author.honorariums') }}" class="author-nav-link {{ request()->routeIs('author.honorariums') ? 'active' : '' }}">
                    <i class="fas fa-heart text-danger"></i>
                    <span>Reader Tips</span>
                </a>
                <a href="{{ route('author.posts.create') }}" class="author-nav-link {{ request()->routeIs('author.posts.create') ? 'active' : '' }}">
                    <i class="fas fa-pen-nib text-warning"></i>
                    <span>Write Post</span>
                </a>
            </nav>

            <div class="pt-3 border-top border-secondary border-opacity-25">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-semibold">
                        <i class="fas fa-power-off me-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="author-main">
        {{-- Top Navbar --}}
        <header class="bg-white border-bottom py-2.5 px-3 px-md-4 d-flex align-items-center justify-content-between sticky-top shadow-xs">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-pill px-2.5 py-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAuthorDrawer" aria-controls="mobileAuthorDrawer">
                    <i class="fas fa-bars"></i>
                </button>
                @if(!request()->routeIs('author.dashboard'))
                    <button type="button" 
                            onclick="if(window.history.length > 1 && document.referrer){ window.history.back(); } else { window.location.href='{{ route('author.dashboard') }}'; }" 
                            class="btn btn-sm btn-light border bg-white shadow-2xs rounded-3 text-secondary px-2.5 py-1 d-inline-flex align-items-center gap-1.5 author-nav-back-btn" 
                            title="পূর্ববর্তী পেজে ফিরে যান (Backspace বা Alt+Left)">
                        <i class="fas fa-arrow-left-long text-primary"></i>
                        <span class="fw-semibold small d-none d-sm-inline">ফিরে যান</span>
                        <kbd class="bg-light text-muted border px-1 py-0 ms-0.5 d-none d-md-inline small font-monospace" style="font-size: 0.65rem;">⌫</kbd>
                    </button>
                @endif
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">@yield('heading', 'Author Studio')</h5>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="fas fa-store me-1"></i> <span class="d-none d-sm-inline">Store</span>
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
                    <i class="fas fa-circle-error fs-5 me-2 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
            @yield('author_content')
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small d-none d-lg-block">
            © {{ date('Y') }} IDEA Publication • Author Self-Publishing & E-Book Royalty Engine
        </footer>
    </div>

    <!-- Mobile Bottom Navigation Bar (< 992px) -->
    <nav class="author-mobile-bottom-bar d-lg-none">
        <a href="{{ route('author.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('author.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('author.posts.create') }}" class="bottom-nav-item {{ request()->routeIs('author.posts.create') ? 'active' : '' }}">
            <i class="fas fa-pen-nib"></i>
            <span>Write</span>
        </a>
        <a href="{{ route('author.ebooks.create') }}" class="bottom-nav-item text-warning {{ request()->routeIs('author.ebooks.create') ? 'active' : '' }}">
            <i class="fas fa-circle-plus fs-4"></i>
            <span>Upload</span>
        </a>
        <a href="{{ route('author.ebooks.index') }}" class="bottom-nav-item {{ request()->routeIs('author.ebooks.index') ? 'active' : '' }}">
            <i class="fas fa-book-bookmark"></i>
            <span>E-Books</span>
        </a>
        <a href="{{ route('author.royalties') }}" class="bottom-nav-item {{ request()->routeIs('author.royalties') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i>
            <span>Royalty</span>
        </a>
    </nav>

    <!-- Bootstrap 5 Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
                        } else if (isLink && trigger.href) {
                            window.location.href = trigger.href;
                        }
                    }
                });
            }
        }, true);

        // Author Studio Smart Backspace / Alt+Left Navigation
        document.addEventListener('keydown', function(e) {
            var activeEl = document.activeElement;
            var tag = (activeEl && activeEl.tagName) ? activeEl.tagName.toLowerCase() : '';
            var isInput = tag === 'input' || tag === 'textarea' || tag === 'select' || (activeEl && activeEl.isContentEditable);
            
            if (!isInput) {
                if (e.key === 'Backspace' || (e.altKey && e.key === 'ArrowLeft')) {
                    if (window.history.length > 1 && document.referrer) {
                        e.preventDefault();
                        window.history.back();
                    }
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
