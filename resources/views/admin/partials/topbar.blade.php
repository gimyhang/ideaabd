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

        {{-- Unified Real-Time Notification Center Dropdown --}}
        @php
            $alerts = $adminPendingAlerts ?? [
                'total_count'   => $pending,
                'has_alerts'    => $pending > 0,
                'orders'        => 0,
                'registrations' => $pending,
                'blogs'         => 0,
                'book_requests' => 0,
                'submissions'   => 0,
            ];
            $totalAlertCount = $alerts['total_count'] ?? 0;
        @endphp

        <div class="dropdown">
            <button class="adm-iconbtn text-decoration-none border-0 bg-transparent position-relative" 
                    type="button" data-bs-toggle="dropdown" aria-expanded="false" 
                    title="{{ $totalAlertCount > 0 ? \App\Support\Bn::num($totalAlertCount) . 'টি নতুন আপডেট অপেক্ষমান' : 'কোনো নতুন নোটিফিকেশন নেই' }}">
                <i class="fas fa-bell {{ $totalAlertCount > 0 ? 'text-primary' : '' }}"></i>
                @if ($totalAlertCount > 0)
                    <span class="badge bg-danger rounded-pill position-absolute top-0 end-0 translate-middle-y" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                        @bn($totalAlertCount)
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-0 border-0 overflow-hidden" style="width: 320px;">
                <div class="p-3 bg-primary text-white d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0 text-white"><i class="fas fa-bell me-1.5"></i>নোটিফিকেশন সেন্টার</h6>
                        <small class="text-white-50" style="font-size: 0.72rem;">অনুমোদন ও অপেক্ষমান তথ্যাবলী</small>
                    </div>
                    @if($totalAlertCount > 0)
                        <span class="badge bg-white text-primary fw-bold rounded-pill px-2 py-0.5">@bn($totalAlertCount)টি নতুন</span>
                    @endif
                </div>

                <div class="p-2">
                    {{-- 1. Pending Ecommerce Orders --}}
                    @if(($alerts['orders'] ?? 0) > 0)
                        <a href="{{ route('admin.ecommerce-orders', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark p-2 rounded-circle"><i class="fas fa-cart-shopping"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">নতুন বই অর্ডার</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">ডেলিভারির জন্য অপেক্ষমান</div>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold rounded-pill px-2">@bn($alerts['orders'])টি</span>
                        </a>
                    @endif

                    {{-- 2. Pending Registrations --}}
                    @if(($alerts['registrations'] ?? 0) > 0)
                        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger text-white p-2 rounded-circle"><i class="fas fa-user-clock"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">রেজিস্ট্রেশন আবেদন</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">লেখক/সেলার অনুমোদন প্রয়োজন</div>
                                </div>
                            </div>
                            <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-2">@bn($alerts['registrations'])টি</span>
                        </a>
                    @endif

                    {{-- 3. Pending Blog Posts --}}
                    @if(($alerts['blogs'] ?? 0) > 0)
                        <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success text-white p-2 rounded-circle"><i class="fas fa-feather-pointed"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">আইডিয়াপত্র / ব্লগ পোস্ট</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">প্রকাশনার জন্য অনুমোদন প্রয়োজন</div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-2">@bn($alerts['blogs'])টি</span>
                        </a>
                    @endif

                    {{-- 4. Pending Book Requests --}}
                    @if(($alerts['book_requests'] ?? 0) > 0)
                        <a href="{{ route('admin.book-requests.index', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info text-white p-2 rounded-circle"><i class="fas fa-book-bookmark"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">গ্রাহকের বই রিকোয়েস্ট</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">স্টক সংগ্রহের অপেক্ষা</div>
                                </div>
                            </div>
                            <span class="badge bg-info-subtle text-info fw-bold rounded-pill px-2">@bn($alerts['book_requests'])টি</span>
                        </a>
                    @endif

                    {{-- 5. Pending Author Submissions --}}
                    @if(($alerts['submissions'] ?? 0) > 0)
                        <a href="{{ route('admin.authors') }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary text-white p-2 rounded-circle"><i class="fas fa-file-lines"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">লেখক পাণ্ডুলিপি সাবমিশন</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">রিভিউ ও অনুমোদন প্রয়োজন</div>
                                </div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary fw-bold rounded-pill px-2">@bn($alerts['submissions'])টি</span>
                        </a>
                    @endif

                    @if($totalAlertCount === 0)
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-circle-check text-success fs-3 mb-2 d-block"></i>
                            <div class="fw-bold small text-dark">সবকিছু আপ-টু-ডেট আছে!</div>
                            <small class="text-muted">কোনো নতুন অপেক্ষমান অনুমোদন বা আবেদন নেই।</small>
                        </div>
                    @endif
                </div>

                <div class="p-2 border-top bg-light text-center">
                    <a href="{{ route('admin.dashboard') }}" class="small fw-semibold text-primary text-decoration-none">
                        <i class="fas fa-chart-pie me-1"></i> ড্যাশবোর্ডে সার্বিক চিত্র দেখুন
                    </a>
                </div>
            </div>
        </div>

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
                    <span class="d-block fw-semibold small">{{ $me->name ?? 'অ্যাডমিন' }}</span>
                    <span class="d-block text-muted" style="font-size:.72rem">
                        {{ ['admin' => 'সাইট অ্যাডমিন', 'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার'][$me->role ?? 'admin'] ?? ($me->role ?? 'অ্যাডমিন') }}
                    </span>
                </span>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ $me->name ?? 'অ্যাডমিন' }}</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $me->email ?? 'admin@ideaabd.com' }}</div>
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
