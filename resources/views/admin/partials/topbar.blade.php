@php
    $me      = auth()->user();
    $pending = $adminPendingRegistrations ?? 0;
    $initial = mb_substr(trim($me->name ?? 'A'), 0, 1);
@endphp

<header class="adm-top">
    <button class="adm-iconbtn" data-side-toggle type="button" aria-label="সাইডবার দেখান/লুকান">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Global catalog search --}}
    <form class="adm-search d-none d-md-block" action="{{ Route::has('admin.books') ? route('admin.books') : url('/admin') }}" method="GET" role="search">
        <div class="input-group">
            <span class="input-group-text border-end-0"><i class="fas fa-magnifying-glass text-muted"></i></span>
            <input type="search" name="search" class="form-control border-start-0 ps-0"
                   placeholder="বই, লেখক বা ব্যবহারকারী খুঁজুন..." value="{{ request('search') }}" aria-label="খুঁজুন">
        </div>
    </form>

    <div class="d-flex align-items-center gap-2 ms-auto">
        {{-- Quick Create Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 dropdown-toggle fw-semibold py-1.5 px-2.5" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus-circle"></i>
                <span class="d-none d-sm-inline">নতুন যোগ করুন</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header text-uppercase small text-muted">ক্যাটালগ ও কন্টেন্ট</h6></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'books') }}"><i class="fas fa-book me-2 text-primary"></i>নতুন বই যোগ</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'ebooks') }}"><i class="fas fa-tablet-screen-button me-2 text-info"></i>নতুন ই-বুক যোগ</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'blog') }}"><i class="fas fa-blog me-2 text-success"></i>নতুন ব্লগ পোস্ট</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'webzines') }}"><i class="fas fa-newspaper me-2 text-warning"></i>নতুন ওয়েবজিন</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'authors') }}"><i class="fas fa-pen-fancy me-2 text-secondary"></i>নতুন লেখক</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'publishers') }}"><i class="fas fa-building me-2 text-dark"></i>নতুন প্রকাশক</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header text-uppercase small text-muted">বিক্রি ও অর্ডার</h6></li>
                <li><a class="dropdown-item text-danger fw-bold" href="{{ route('subadmin.bills.create') }}"><i class="fas fa-receipt me-2"></i>নতুন অর্ডার / বিল পোস্ট</a></li>
            </ul>
        </div>

        {{-- Pending registrations --}}
        @if (Route::has('admin.registrations.index'))
            <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}"
               class="adm-iconbtn text-decoration-none"
               title="{{ $pending > 0 ? \App\Support\Bn::num($pending) . 'টি রেজিস্ট্রেশন অপেক্ষমান' : 'কোনো অপেক্ষমান রেজিস্ট্রেশন নেই' }}">
                <i class="fas fa-bell"></i>
                @if ($pending > 0)
                    <span class="badge bg-danger adm-iconbtn__dot">@bn($pending)</span>
                @endif
            </a>
        @endif

        {{-- Dark Mode Toggle --}}
        <button class="adm-iconbtn text-decoration-none" data-theme-toggle type="button" title="ডার্ক / লাইট মোড সুইচার">
            <i class="fas fa-moon"></i>
        </button>

        {{-- View site --}}
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="adm-iconbtn text-decoration-none d-none d-sm-grid" title="ওয়েবসাইট দেখুন">
            <i class="fas fa-globe"></i>
        </a>

        {{-- Profile --}}
        <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="adm-avatar">{{ $initial }}</span>
                <span class="text-start d-none d-lg-block lh-sm">
                    <span class="d-block fw-semibold small">{{ $me->name }}</span>
                    <span class="d-block text-muted" style="font-size:.72rem">
                        {{ ['admin' => 'সাইট অ্যাডমিন', 'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার'][$me->role] ?? $me->role }}
                    </span>
                </span>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ $me->name }}</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $me->email }}</div>
                </li>
                @if (Route::has('admin.roles.index'))
                    <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}"><i class="fas fa-user-shield me-2 text-primary"></i>পারমিশন ও এক্সেস</a></li>
                @endif
                @if (Route::has('admin.users'))
                    <li><a class="dropdown-item" href="{{ route('admin.users') }}"><i class="fas fa-users me-2 text-muted"></i>ব্যবহারকারী</a></li>
                @endif
                <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fas fa-globe me-2 text-muted"></i>ওয়েবসাইট</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-arrow-right-from-bracket me-2"></i>লগ আউট
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
