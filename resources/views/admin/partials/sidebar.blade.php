@php
    /**
     * Dynamic & Smart Accordion Nav tree for the admin panel.
     * Includes spotlight search filter, quick favorites strip, and auto-scroll.
     */
    $pending = $adminPendingRegistrations ?? 0;
    $pendingPayouts = \Illuminate\Support\Facades\Schema::hasTable('author_payout_requests')
        ? \App\Models\AuthorPayoutRequest::where('status', 'pending')->count()
        : 0;
    $pendingHonorariums = \Illuminate\Support\Facades\Schema::hasTable('author_honorariums')
        ? \App\Models\AuthorHonorarium::where('payment_status', 'pending')->count()
        : 0;
    $pendingPasswordRequests = \Illuminate\Support\Facades\Schema::hasTable('password_reset_requests')
        ? \App\Models\PasswordResetRequest::where('status', 'pending')->count()
        : 0;

    $menu = [
        null => [
            ['route' => 'admin.dashboard', 'icon' => 'gauge-high', 'label' => 'Dashboard'],
        ],
        'Catalog' => [
            ['route' => 'admin.books',          'icon' => 'book',          'label' => 'Books'],
            ['route' => 'admin.bundles.index',  'icon' => 'boxes-stacked', 'label' => 'Combos & Bundles'],
            ['route' => 'admin.categories',     'icon' => 'folder-tree',   'label' => 'Categories'],
            ['route' => 'admin.authors',        'icon' => 'pen-fancy',     'label' => 'Authors'],
            ['route' => 'admin.publishers',     'icon' => 'building',      'label' => 'Publishers'],
        ],
        'E-Books & Royalties' => [
            ['route' => 'admin.ebooks',                 'icon' => 'tablet-screen-button', 'label' => 'E-Books Inventory'],
            ['route' => 'admin.subscriptions.index',    'icon' => 'crown',                'label' => 'Idea Unlimited Club'],
            ['route' => 'admin.ebook-sales-report',     'icon' => 'chart-pie',             'label' => 'E-Book Sales Report'],
            ['route' => 'admin.author-royalties.index', 'icon' => 'scale-balanced',       'label' => 'Royalty Management'],
            ['route' => 'admin.author-payouts.index',   'icon' => 'hand-holding-dollar',  'label' => 'Royalty Payouts',
             'badge' => $pendingPayouts > 0 ? $pendingPayouts : null, 'badgeClass' => 'bg-warning text-dark'],
            ['route' => 'admin.royalty-payout-logs',    'icon' => 'file-invoice-dollar',  'label' => 'Payout Gateway Logs'],
        ],
        'Purchases & Inventory' => [
            ['route' => 'admin.purchases.index',    'icon' => 'cart-flatbed',        'label' => 'Purchases & Invoices'],
            ['route' => 'admin.purchases.payments', 'icon' => 'hand-holding-dollar', 'label' => 'Payments & Installments'],
            ['route' => 'admin.purchases.ledger',   'icon' => 'book-bookmark',       'label' => 'Vendor & Press Ledgers'],
            ['route' => 'admin.purchases.monthly-report', 'icon' => 'chart-pie',     'label' => 'Monthly Report'],
        ],
        'Idea Accounting' => [
            ['route' => 'admin.accounting.index',                  'icon' => 'scale-balanced',      'label' => 'Income & Expenses'],
            ['route' => 'admin.accounting.invoices.index',         'icon' => 'file-invoice-dollar', 'label' => 'Invoices & Challans'],
            ['route' => 'admin.accounting.customer-ledger.index', 'icon' => 'book-bookmark',       'label' => 'Customer Ledgers'],
            ['route' => 'admin.accounting.salary.index',           'icon' => 'money-check-dollar',  'label' => 'Payroll & Salaries'],
            ['route' => 'admin.accounting.employees.index',        'icon' => 'users-gear',          'label' => 'Employee Profiles'],
            ['route' => 'admin.accounting.reports.index',          'icon' => 'chart-pie',           'label' => 'P&L Reports'],
        ],
        'Content' => [
            ['route' => 'admin.blog',                     'icon' => 'newspaper',          'label' => 'Ideapatra / Blog'],
            ['route' => 'admin.author-honorariums.index', 'icon' => 'hand-holding-heart', 'label' => 'Author Honorariums',
             'badge' => $pendingHonorariums > 0 ? $pendingHonorariums : null, 'badgeClass' => 'bg-danger text-white'],
            ['route' => 'admin.webzines',                 'icon' => 'book-open',          'label' => 'Webzines'],
        ],
        'Sales & Orders' => [
            ['route' => 'admin.pos.index',        'icon' => 'cash-register', 'label' => 'Boi Mela Stall POS'],
            ['route' => 'admin.ecommerce-orders', 'icon' => 'cart-shopping', 'label' => 'Book Orders'],
            ['route' => 'admin.affiliates.index', 'icon' => 'bullhorn',      'label' => 'Affiliates & Influencers'],
            ['route' => 'admin.gateway-reports',  'icon' => 'receipt',       'label' => 'Gateway Reports'],
            ['route' => 'admin.payments.index',   'icon' => 'credit-card',   'label' => 'Payment Gateways'],
            ['route' => 'admin.customers',        'icon' => 'user-tag',      'label' => 'Customers & Broadcast'],
            ['route' => 'admin.orders',           'icon' => 'file-invoice',  'label' => 'Seller Bills'],
            ['route' => 'admin.book-requests.index', 'icon' => 'code-pull-request', 'label' => 'Book Requests'],
        ],
        'User Management' => [
            ['route' => 'admin.users',                 'icon' => 'users',         'label' => 'All Users'],
            ['route' => 'admin.tickets.index',         'icon' => 'ticket',        'label' => 'Support Tickets CRM'],
            ['route' => 'admin.users.security.index',  'icon' => 'shield-halved', 'label' => 'Login Security & OTP',
             'badge' => $pendingPasswordRequests > 0 ? $pendingPasswordRequests : null, 'badgeClass' => 'bg-danger text-white'],
            ['route' => 'admin.registrations.index',   'icon' => 'user-check',    'label' => 'Registration Approvals',
             'badge' => $pending, 'badgeClass' => 'bg-warning text-dark'],
            ['route' => 'admin.sub-admins.index',      'icon' => 'user-shield',   'label' => 'Sub-Admins'],
        ],
        'Administration' => [
            ['route' => 'admin.currencies.index',    'icon' => 'coins',           'label' => 'Multi-Currency & FX'],
            ['route' => 'admin.translations.index',  'icon' => 'language',        'label' => 'Translations & i18n'],
            ['route' => 'admin.communication.index', 'icon' => 'paper-plane',      'label' => 'Communication Hub'],
            ['route' => 'admin.roles.index',         'icon' => 'key',             'label' => 'Roles & Permissions'],
            ['route' => 'admin.visitor-reports',     'icon' => 'chart-line',     'label' => 'Visitor Reports'],
            ['route' => 'admin.system-settings',     'icon' => 'sliders',         'label' => 'System Settings'],
            ['route' => 'admin.cache.manage',        'icon' => 'bolt',            'label' => 'Cache Management'],
            ['route' => 'admin.media.index',         'icon' => 'images',          'label' => 'Media & Library'],
            ['route' => 'admin.backup.index',        'icon' => 'database',        'label' => 'Backup Database'],
            ['route' => 'admin.audit-logs.index',    'icon' => 'shield-halved',   'label' => 'Audit & Logs'],
        ],
        'Seller Panel' => [
            ['route' => 'subadmin.bills.index', 'icon' => 'file-invoice-dollar', 'label' => 'Bills List'],
            ['route' => 'subadmin.accounts',    'icon' => 'wallet',             'label' => 'Seller Accounts'],
        ],
        'Public Site' => [
            ['route' => 'home', 'icon' => 'arrow-up-right-from-square', 'label' => 'View Website', 'target' => '_blank'],
        ],
    ];
@endphp

<aside class="adm-side" data-sidebar>
    <div class="adm-side__header d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="adm-brand text-decoration-none">
            <x-brand-logo :size="38" />
            <span class="adm-brand__text">
                <span class="adm-brand__name d-block">{{ config('brand.name') }}</span>
                <span class="adm-brand__sub">{{ config('brand.tagline') }}</span>
            </span>
        </a>
        <button type="button" class="adm-side__close d-lg-none btn btn-sm text-white" data-side-close aria-label="Close sidebar">
            <i class="fas fa-times fs-5"></i>
        </button>
    </div>

    <!-- 1. Spotlight Search Bar (Instant Filter) -->
    <div class="adm-side__search-wrap">
        <div class="adm-side__search-box">
            <i class="fas fa-search adm-side__search-icon"></i>
            <input type="text" id="admSidebarSearch" class="adm-side__search-input" placeholder="Search menu... (Ctrl+K)" autocomplete="off">
            <span id="admSidebarMatchCounter" class="adm-side__match-badge d-none">0</span>
            <span class="adm-side__search-shortcut" id="admSidebarShortcut">⌘K</span>
            <button type="button" id="admSidebarSearchClear" class="adm-side__search-clear" title="Clear filter">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- 2. Quick Access / Favorites Chip Bar -->
    <div class="adm-side__favorites">
        <a href="{{ route('admin.pos.index') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.pos.*') ? 'is-active' : '' }}" title="Boi Mela Stall POS">
            <i class="fas fa-cash-register text-warning"></i>
            <span>POS</span>
        </a>
        <a href="{{ route('admin.ecommerce-orders') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.ecommerce-orders*') ? 'is-active' : '' }}" title="Book Orders">
            <i class="fas fa-cart-shopping text-info"></i>
            <span>Orders</span>
        </a>
        <a href="{{ route('admin.books') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.books*') ? 'is-active' : '' }}" title="Books Catalog">
            <i class="fas fa-book text-success"></i>
            <span>Books</span>
        </a>
        <a href="{{ route('admin.currencies.index') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.currencies.*') ? 'is-active' : '' }}" title="Multi-Currency FX">
            <i class="fas fa-coins text-warning"></i>
            <span>FX</span>
        </a>
        <a href="{{ route('admin.tickets.index') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.tickets.*') ? 'is-active' : '' }}" title="Support Tickets">
            <i class="fas fa-ticket text-danger"></i>
            <span>Tickets</span>
        </a>
    </div>

    <!-- 3. Dynamic Collapsible Navigation Tree -->
    <nav class="adm-nav" id="admNavTree">
        @foreach ($menu as $group => $items)
            @php
                $items = array_filter($items, fn ($i) => Route::has($i['route']));
                $groupId = $group ? 'nav-grp-' . \Illuminate\Support\Str::slug($group) : 'nav-grp-main';

                // Check if any child item in this group is active
                $groupHasActive = false;
                foreach ($items as $chk) {
                    $chkBase = preg_replace('/\.index$/', '', $chk['route']);
                    if (request()->routeIs($chk['route']) || request()->routeIs($chkBase . '.*')) {
                        $groupHasActive = true;
                        break;
                    }
                }
            @endphp

            @if (! empty($items))
                @if ($group)
                    <div class="adm-nav__group {{ $groupHasActive ? 'is-active-group' : '' }}" data-group-id="{{ $groupId }}">
                        <div class="adm-nav__group-header" onclick="toggleNavGroup('{{ $groupId }}')">
                            <span>{{ $group }}</span>
                            <i class="fas fa-chevron-down adm-nav__group-chevron"></i>
                        </div>
                        <div class="adm-nav__group-items" id="{{ $groupId }}">
                            @foreach ($items as $item)
                                @php
                                    $base = preg_replace('/\.index$/', '', $item['route']);
                                    $isRouteActive = request()->routeIs($item['route']) || request()->routeIs($base . '.*');
                                    $active = isset($item['category']) ? ($isRouteActive && request('category') === $item['category']) : ($isRouteActive && empty(request('category')));
                                    $href = $item['url'] ?? route($item['route']);
                                @endphp
                                <a href="{{ $href }}"
                                   class="adm-nav__link {{ $active ? 'is-active' : '' }}"
                                   data-label="{{ strtolower($item['label'] . ' ' . $group) }}"
                                   @isset($item['target']) target="{{ $item['target'] }}" rel="noopener" @endisset
                                   @if ($active) id="activeNavItem" aria-current="page" @endif>
                                    <i class="fas fa-{{ $item['icon'] }}"></i>
                                    <span class="adm-nav__text">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="badge {{ $item['badgeClass'] ?? 'bg-primary' }}">{{ $item['badge'] }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Top level (Dashboard) --}}
                    @foreach ($items as $item)
                        @php
                            $active = request()->routeIs($item['route']);
                            $href = $item['url'] ?? route($item['route']);
                        @endphp
                        <a href="{{ $href }}"
                           class="adm-nav__link {{ $active ? 'is-active' : '' }}"
                           data-label="{{ strtolower($item['label']) }}"
                           @if ($active) id="activeNavItem" aria-current="page" @endif>
                            <i class="fas fa-{{ $item['icon'] }}"></i>
                            <span class="adm-nav__text">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endif
            @endif
        @endforeach
    </nav>

    <div class="adm-side__foot adm-brand__text d-flex justify-content-between align-items-center">
        <span>Version 2.0</span>
        <button type="button" class="btn btn-xs p-0 text-white-50 text-decoration-none" onclick="toggleAllNavGroups()" title="Expand/Collapse All">
            <i class="fas fa-up-down-left-right" style="font-size: 10px;"></i> Toggle All
        </button>
    </div>
</aside>

@push('scripts')
<script>
(function() {
    // 1. Accordion Group State & LocalStorage Management
    const STORAGE_KEY = 'adm_nav_collapsed_groups';
    let collapsedGroups = [];
    try {
        collapsedGroups = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch(e) {
        collapsedGroups = [];
    }

    // Apply saved collapsed state (except if the group contains the active item)
    document.querySelectorAll('.adm-nav__group').forEach(group => {
        const gid = group.getAttribute('data-group-id');
        const hasActive = group.classList.contains('is-active-group');

        if (!hasActive && collapsedGroups.includes(gid)) {
            group.classList.add('is-collapsed');
        } else if (hasActive) {
            group.classList.remove('is-collapsed');
        }
    });

    window.toggleNavGroup = function(groupId) {
        const group = document.querySelector(`.adm-nav__group[data-group-id="${groupId}"]`);
        if (!group) return;

        group.classList.toggle('is-collapsed');

        let saved = [];
        try { saved = JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch(e){}

        if (group.classList.contains('is-collapsed')) {
            if (!saved.includes(groupId)) saved.push(groupId);
        } else {
            saved = saved.filter(id => id !== groupId);
        }
        localStorage.setItem(STORAGE_KEY, JSON.stringify(saved));
    };

    window.toggleAllNavGroups = function() {
        const groups = document.querySelectorAll('.adm-nav__group');
        const anyExpanded = Array.from(groups).some(g => !g.classList.contains('is-collapsed'));

        groups.forEach(g => {
            if (anyExpanded) {
                g.classList.add('is-collapsed');
            } else {
                g.classList.remove('is-collapsed');
            }
        });

        const allIds = anyExpanded ? Array.from(groups).map(g => g.getAttribute('data-group-id')) : [];
        localStorage.setItem(STORAGE_KEY, JSON.stringify(allIds));
    };

    // 2. Real-Time Spotlight Search Filter
    const searchInput = document.getElementById('admSidebarSearch');
    const clearBtn = document.getElementById('admSidebarSearchClear');
    const shortcutBadge = document.getElementById('admSidebarShortcut');
    const matchBadge = document.getElementById('admSidebarMatchCounter');
    const navLinks = document.querySelectorAll('.adm-nav__link');
    const navGroups = document.querySelectorAll('.adm-nav__group');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.trim().toLowerCase();
            if (term.length > 0) {
                if (clearBtn) clearBtn.style.display = 'block';
                if (shortcutBadge) shortcutBadge.style.display = 'none';

                let totalMatches = 0;

                navGroups.forEach(g => {
                    let groupHasMatch = false;
                    const items = g.querySelectorAll('.adm-nav__link');

                    items.forEach(link => {
                        const text = link.getAttribute('data-label') || '';
                        const textSpan = link.querySelector('.adm-nav__text');
                        const originalText = textSpan ? textSpan.innerText : '';

                        if (text.includes(term)) {
                            link.style.display = 'flex';
                            groupHasMatch = true;
                            totalMatches++;
                            // Highlight text
                            if (textSpan) {
                                const regex = new RegExp(`(${term})`, 'gi');
                                textSpan.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
                            }
                        } else {
                            link.style.display = 'none';
                        }
                    });

                    if (groupHasMatch) {
                        g.style.display = 'block';
                        g.classList.remove('is-collapsed'); // Expand automatically when searching!
                    } else {
                        g.style.display = 'none';
                    }
                });

                if (matchBadge) {
                    matchBadge.textContent = `${totalMatches}`;
                    matchBadge.classList.remove('d-none');
                }
            } else {
                clearSearchFilter();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearSearchFilter();
                searchInput.focus();
            });
        }
    }

    function clearSearchFilter() {
        if (clearBtn) clearBtn.style.display = 'none';
        if (shortcutBadge) shortcutBadge.style.display = '';
        if (matchBadge) matchBadge.classList.add('d-none');
        navLinks.forEach(link => {
            link.style.display = 'flex';
            const textSpan = link.querySelector('.adm-nav__text');
            if (textSpan) textSpan.innerHTML = textSpan.innerText; // remove <mark>
        });
        navGroups.forEach(g => {
            g.style.display = 'block';
            const gid = g.getAttribute('data-group-id');
            const hasActive = g.classList.contains('is-active-group');
            if (!hasActive && collapsedGroups.includes(gid)) {
                g.classList.add('is-collapsed');
            }
        });
    }

    // Global Shortcut: Ctrl+K or Cmd+K focuses search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        } else if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            clearSearchFilter();
            searchInput.blur();
        }
    });

    // 3. Smart Auto-Scroll to Active Item on Page Load
    setTimeout(() => {
        const activeItem = document.getElementById('activeNavItem');
        const navContainer = document.getElementById('admNavTree');
        if (activeItem && navContainer) {
            const itemRect = activeItem.getBoundingClientRect();
            const navRect = navContainer.getBoundingClientRect();
            if (itemRect.top < navRect.top || itemRect.bottom > navRect.bottom) {
                activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }, 150);
})();
</script>
@endpush
