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
