@php
    $me      = auth()->user();
    $pending = $adminPendingRegistrations ?? 0;
    $initial = mb_substr(trim($me->name ?? 'A'), 0, 1);
@endphp

<header class="adm-top">
    <button class="adm-iconbtn" data-side-toggle type="button" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Global catalog search --}}
    <form class="adm-search d-none d-md-block" action="{{ Route::has('admin.books') ? route('admin.books') : url('/admin') }}" method="GET" role="search">
        <div class="input-group">
            <span class="input-group-text border-end-0"><i class="fas fa-magnifying-glass text-muted"></i></span>
            <input type="search" name="search" class="form-control border-start-0 ps-0"
                   placeholder="Search books, authors, or users..." value="{{ request('search') }}" aria-label="Search">
        </div>
    </form>

    <div class="d-flex align-items-center gap-2 ms-auto">
        {{-- Quick Create Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 dropdown-toggle fw-semibold py-1.5 px-2.5" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus-circle"></i>
                <span class="d-none d-sm-inline">Quick Add</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header text-uppercase small text-muted">Catalog & Content</h6></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'books') }}"><i class="fas fa-book me-2 text-primary"></i>New Book</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'ebooks') }}"><i class="fas fa-tablet-screen-button me-2 text-info"></i>New E-Book</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'blog') }}"><i class="fas fa-blog me-2 text-success"></i>New Blog Post</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'webzines') }}"><i class="fas fa-newspaper me-2 text-warning"></i>New Webzine</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'authors') }}"><i class="fas fa-pen-fancy me-2 text-secondary"></i>New Author</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.content.create', 'publishers') }}"><i class="fas fa-building me-2 text-dark"></i>New Publisher</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header text-uppercase small text-muted">Orders & Purchases</h6></li>
                <li><a class="dropdown-item" href="{{ route('admin.purchases.create') }}"><i class="fas fa-cart-plus me-2 text-primary"></i>New Purchase Order</a></li>
                <li><a class="dropdown-item text-danger fw-bold" href="{{ route('subadmin.bills.create') }}"><i class="fas fa-receipt me-2"></i>New Seller Bill</a></li>
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
                    title="{{ $totalAlertCount > 0 ? $totalAlertCount . ' pending items require action' : 'No new notifications' }}">
                <i class="fas fa-bell {{ $totalAlertCount > 0 ? 'text-primary' : '' }}"></i>
                @if ($totalAlertCount > 0)
                    <span class="badge bg-danger rounded-pill position-absolute top-0 end-0 translate-middle-y" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                        {{ $totalAlertCount }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-0 border-0 overflow-hidden" style="width: 330px;">
                <div class="p-3 bg-primary text-white d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0 text-white"><i class="fas fa-bell me-1.5"></i>Notification Center</h6>
                        <small class="text-white-50" style="font-size: 0.72rem;">Pending Approvals & Orders</small>
                    </div>
                    @if($totalAlertCount > 0)
                        <span class="badge bg-white text-primary fw-bold rounded-pill px-2 py-0.5">{{ $totalAlertCount }} Pending</span>
                    @endif
                </div>

                <div class="p-2">
                    {{-- 1. Pending Ecommerce Orders --}}
                    @if(($alerts['orders'] ?? 0) > 0)
                        <a href="{{ route('admin.ecommerce-orders', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark p-2 rounded-circle"><i class="fas fa-cart-shopping"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">New Book Orders</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Awaiting processing & delivery</div>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold rounded-pill px-2">{{ $alerts['orders'] }}</span>
                        </a>
                    @endif

                    {{-- 2. Pending Registrations --}}
                    @if(($alerts['registrations'] ?? 0) > 0)
                        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger text-white p-2 rounded-circle"><i class="fas fa-user-clock"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">Registration Requests</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Publisher/Author/Seller review</div>
                                </div>
                            </div>
                            <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-2">{{ $alerts['registrations'] }}</span>
                        </a>
                    @endif

                    {{-- 3. Pending Blog Posts --}}
                    @if(($alerts['blogs'] ?? 0) > 0)
                        <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success text-white p-2 rounded-circle"><i class="fas fa-feather-pointed"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">Blog & Article Posts</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Awaiting publication approval</div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-2">{{ $alerts['blogs'] }}</span>
                        </a>
                    @endif

                    {{-- 4. Pending Book Requests --}}
                    @if(($alerts['book_requests'] ?? 0) > 0)
                        <a href="{{ route('admin.book-requests.index', ['status' => 'pending']) }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info text-white p-2 rounded-circle"><i class="fas fa-book-bookmark"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">Customer Book Requests</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Sourcing in progress</div>
                                </div>
                            </div>
                            <span class="badge bg-info-subtle text-info fw-bold rounded-pill px-2">{{ $alerts['book_requests'] }}</span>
                        </a>
                    @endif

                    {{-- 5. Pending Author Submissions --}}
                    @if(($alerts['submissions'] ?? 0) > 0)
                        <a href="{{ route('admin.authors') }}" class="dropdown-item d-flex align-items-center justify-content-between p-2.5 rounded-3 mb-1 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary text-white p-2 rounded-circle"><i class="fas fa-file-lines"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small">Author Submissions</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Manuscript review needed</div>
                                </div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary fw-bold rounded-pill px-2">{{ $alerts['submissions'] }}</span>
                        </a>
                    @endif

                    @if($totalAlertCount === 0)
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-circle-check text-success fs-3 mb-2 d-block"></i>
                            <div class="fw-bold small text-dark">Everything is up-to-date!</div>
                            <small class="text-muted">No pending approvals or unread alerts.</small>
                        </div>
                    @endif
                </div>

                <div class="p-2 border-top bg-light text-center">
                    <a href="{{ route('admin.dashboard') }}" class="small fw-semibold text-primary text-decoration-none">
                        <i class="fas fa-chart-pie me-1"></i> View System Dashboard
                    </a>
                </div>
            </div>
        </div>

        {{-- Dark Mode Toggle --}}
        <button class="adm-iconbtn text-decoration-none" data-theme-toggle type="button" title="Toggle Theme (Light / Dark)">
            <i class="fas fa-moon"></i>
        </button>

        {{-- View site --}}
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="adm-iconbtn text-decoration-none d-none d-sm-grid" title="View Public Website">
            <i class="fas fa-globe"></i>
        </a>

        {{-- Profile --}}
        <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="adm-avatar">{{ $initial }}</span>
                <span class="text-start d-none d-lg-block lh-sm">
                    <span class="d-block fw-semibold small">{{ $me->name ?? 'Admin' }}</span>
                    <span class="d-block text-muted" style="font-size:.72rem">
                        {{ ['admin' => 'Administrator', 'sub_admin' => 'Sub-Admin', 'seller' => 'Seller'][$me->role ?? 'admin'] ?? ($me->role ?? 'Admin') }}
                    </span>
                </span>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ $me->name ?? 'Admin' }}</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $me->email ?? 'admin@ideaabd.com' }}</div>
                </li>
                @if (Route::has('admin.roles.index'))
                    <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}"><i class="fas fa-user-shield me-2 text-primary"></i>Roles & Permissions</a></li>
                @endif
                @if (Route::has('admin.users'))
                    <li><a class="dropdown-item" href="{{ route('admin.users') }}"><i class="fas fa-users me-2 text-muted"></i>Users</a></li>
                @endif
                <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fas fa-globe me-2 text-muted"></i>View Website</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-arrow-right-from-bracket me-2"></i>Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
