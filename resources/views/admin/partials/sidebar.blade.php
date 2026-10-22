@php
    /**
     * Dynamic & Smart Accordion Nav tree for the admin panel.
     * Includes spotlight search filter, quick favorites strip, and auto-scroll.
     */
    $sidebarBadges = \Illuminate\Support\Facades\Cache::remember('admin_sidebar_pending_badges', 60, function () use ($adminPendingRegistrations) {
        $hasUsers = \Illuminate\Support\Facades\Schema::hasTable('users');
        return [
            'pending' => $adminPendingRegistrations ?? ($hasUsers ? \App\Models\User::where('reg_status', 'pending')->count() : 0),
            'authors' => $hasUsers ? \App\Models\User::where('reg_status', 'pending')->where(fn($q) => $q->where('role', 'author')->orWhere('reg_type', 'author'))->count() : 0,
            'publishers' => $hasUsers ? \App\Models\User::where('reg_status', 'pending')->where(fn($q) => $q->where('role', 'publisher')->orWhere('reg_type', 'publisher'))->count() : 0,
            'sellers' => $hasUsers ? \App\Models\User::where('reg_status', 'pending')->where(fn($q) => $q->where('role', 'seller')->orWhere('reg_type', 'seller'))->count() : 0,
            'payouts' => \Illuminate\Support\Facades\Schema::hasTable('author_payout_requests') ? \App\Models\AuthorPayoutRequest::where('status', 'pending')->count() : 0,
            'honorariums' => \Illuminate\Support\Facades\Schema::hasTable('author_honorariums') ? \App\Models\AuthorHonorarium::where('payment_status', 'pending')->count() : 0,
            'password_requests' => \Illuminate\Support\Facades\Schema::hasTable('password_reset_requests') ? \App\Models\PasswordResetRequest::where('status', 'pending')->count() : 0,
        ];
    });

    $pending = $sidebarBadges['pending'] ?? 0;
    $pendingAuthors = $sidebarBadges['authors'] ?? 0;
    $pendingPublishers = $sidebarBadges['publishers'] ?? 0;
    $pendingSellers = $sidebarBadges['sellers'] ?? 0;
    $pendingPayouts = $sidebarBadges['payouts'] ?? 0;
    $pendingHonorariums = $sidebarBadges['honorariums'] ?? 0;
    $pendingPasswordRequests = $sidebarBadges['password_requests'] ?? 0;

    $menu = [
        null => [
            ['route' => 'admin.dashboard', 'icon' => 'gauge-high', 'label' => 'Dashboard'],
        ],
        'Catalog' => [
            ['route' => 'admin.books',          'icon' => 'book',          'label' => 'Books'],
            ['route' => 'admin.products.electronics', 'icon' => 'laptop',       'label' => 'Electronics'],
            ['route' => 'admin.products.stationery',  'icon' => 'pen-ruler',    'label' => 'Stationery'],
            ['route' => 'admin.products.appliance',   'icon' => 'blender',      'label' => 'Appliances'],
            ['route' => 'admin.bundles.index',  'icon' => 'boxes-stacked', 'label' => 'Bundles'],
            ['route' => 'admin.categories',     'icon' => 'folder-tree',   'label' => 'Categories'],
            ['route' => 'admin.genres.index',   'icon' => 'tags',          'label' => 'Genres'],
            ['route' => 'admin.authors',        'icon' => 'pen-fancy',     'label' => 'Authors'],
            ['route' => 'admin.publishers',     'icon' => 'building',      'label' => 'Publishers'],
        ],
        'Ebooks' => [
            ['route' => 'admin.ebooks',                 'icon' => 'tablet-screen-button', 'label' => 'Titles'],
            ['route' => 'admin.subscriptions.index',    'icon' => 'crown',                'label' => 'Club'],
            ['route' => 'admin.ebook-sales-report',     'icon' => 'chart-pie',             'label' => 'Reports'],
            ['route' => 'admin.author-royalties.index', 'icon' => 'scale-balanced',       'label' => 'Royalties'],
            ['route' => 'admin.author-payouts.index',   'icon' => 'hand-holding-dollar',  'label' => 'Payouts',
             'badge' => $pendingPayouts > 0 ? $pendingPayouts : null, 'badgeClass' => 'bg-warning text-dark'],
            ['route' => 'admin.royalty-payout-logs',    'icon' => 'file-invoice-dollar',  'label' => 'Logs'],
        ],
        'Purchases' => [
            ['route' => 'admin.purchases.index',    'icon' => 'cart-flatbed',        'label' => 'Purchases'],
            ['route' => 'admin.purchases.payments', 'icon' => 'hand-holding-dollar', 'label' => 'Payments'],
            ['route' => 'admin.purchases.ledger',   'icon' => 'book-bookmark',       'label' => 'Ledgers'],
            ['route' => 'admin.purchases.monthly-report', 'icon' => 'chart-pie',     'label' => 'Monthly'],
            ['route' => 'admin.purchases.stationery', 'icon' => 'pen-ruler',         'label' => 'Stationery'],
            ['route' => 'admin.purchases.electronics', 'icon' => 'plug',             'label' => 'Electronics'],
            ['route' => 'admin.purchases.items.stock', 'icon' => 'boxes-stacked',    'label' => 'Items'],
            ['route' => 'admin.production.index',    'icon' => 'print',              'label' => 'Production'],
            ['route' => 'admin.inventory.index',     'icon' => 'warehouse',          'label' => 'Stock'],
            ['route' => 'admin.inventory.locations', 'icon' => 'location-dot',       'label' => 'Locations'],
            ['route' => 'admin.inventory.transfers', 'icon' => 'truck-ramp-box',     'label' => 'Transfers'],
            ['route' => 'admin.inventory.wastages',  'icon' => 'triangle-exclamation','label' => 'Wastage'],
            ['route' => 'admin.inventory.alerts',    'icon' => 'bell',               'label' => 'Alerts'],
            ['route' => 'admin.inventory.logs',      'icon' => 'clock-rotate-left',  'label' => 'Movements'],
        ],
        'Accounting' => [
            ['route' => 'admin.accounting.index',                  'icon' => 'scale-balanced',      'label' => 'Transactions'],
            ['route' => 'admin.accounting.invoices.index',         'icon' => 'file-invoice-dollar', 'label' => 'Invoices', 'url' => route('admin.accounting.index', ['stream' => 'invoices'])],
            ['route' => 'admin.accounting.invoices.index',         'icon' => 'file-lines',          'label' => 'Quotations', 'type' => 'quotation', 'url' => route('admin.accounting.index', ['stream' => 'invoices', 'type' => 'quotation'])],
            ['route' => 'admin.accounting.customer-ledger.index', 'icon' => 'book-bookmark',       'label' => 'Ledger'],
            ['route' => 'admin.accounting.tax-vat-deductions.index', 'icon' => 'receipt',           'label' => 'Tax'],
            ['route' => 'admin.accounting.salary.index',           'icon' => 'money-check-dollar',  'label' => 'Payroll'],
            ['route' => 'admin.accounting.employees.index',        'icon' => 'users-gear',          'label' => 'Employees'],
            ['route' => 'admin.accounting.reports.index',          'icon' => 'chart-pie',           'label' => 'Reports'],
        ],
        'Content' => [
            ['route' => 'admin.blog',                     'icon' => 'newspaper',          'label' => 'Blog'],
            ['route' => 'admin.author-honorariums.index', 'icon' => 'hand-holding-heart', 'label' => 'Honorariums',
             'badge' => $pendingHonorariums > 0 ? $pendingHonorariums : null, 'badgeClass' => 'bg-danger text-white'],
            ['route' => 'admin.webzines',                 'icon' => 'book-open',          'label' => 'Webzines'],
        ],
        'Sales' => [
            ['route' => 'admin.pos.index',        'icon' => 'cash-register', 'label' => 'POS'],
            ['route' => 'admin.ecommerce-orders', 'icon' => 'cart-shopping', 'label' => 'Orders'],
            ['route' => 'admin.affiliates.index', 'icon' => 'bullhorn',      'label' => 'Affiliates'],
            ['route' => 'admin.gateway-reports',  'icon' => 'receipt',       'label' => 'Receipts'],
            ['route' => 'admin.payments.index',   'icon' => 'credit-card',   'label' => 'Gateways'],
            ['route' => 'admin.sms.index',        'icon' => 'comment-sms',   'label' => 'SMS'],
            ['route' => 'admin.orders',           'icon' => 'file-invoice',  'label' => 'Bills'],
            ['route' => 'admin.book-requests.index', 'icon' => 'code-pull-request', 'label' => 'Requests'],
        ],
        'Users' => [
            ['route' => 'admin.users',                   'icon' => 'users',           'label' => 'Users'],
            ['route' => 'admin.registrations.authors',   'icon' => 'feather-pointed', 'label' => 'Authors',
             'badge' => $pendingAuthors > 0 ? $pendingAuthors : null, 'badgeClass' => 'bg-success text-white'],
            ['route' => 'admin.registrations.publishers','icon' => 'building',        'label' => 'Publishers',
             'badge' => $pendingPublishers > 0 ? $pendingPublishers : null, 'badgeClass' => 'bg-info text-white'],
            ['route' => 'admin.registrations.sellers',   'icon' => 'store',           'label' => 'Sellers',
             'badge' => $pendingSellers > 0 ? $pendingSellers : null, 'badgeClass' => 'bg-primary text-white'],
            ['route' => 'admin.registrations.customers', 'icon' => 'user-tag',        'label' => 'Customers'],
            ['route' => 'admin.registrations.approvals', 'icon' => 'user-check',      'label' => 'Approvals',
             'badge' => $pending > 0 ? $pending : null, 'badgeClass' => 'bg-danger text-white'],
            ['route' => 'admin.users.security.index',    'icon' => 'shield-halved',   'label' => 'Security',
             'badge' => $pendingPasswordRequests > 0 ? $pendingPasswordRequests : null, 'badgeClass' => 'bg-danger text-white'],
            ['route' => 'admin.sub-admins.index',        'icon' => 'user-shield',     'label' => 'Staff'],
            ['route' => 'admin.event-campaigns.index',   'icon' => 'calendar-check',  'label' => 'Campaigns'],
            ['route' => 'admin.libraries.index',         'icon' => 'book-open-reader','label' => 'Libraries'],
            ['route' => 'admin.tickets.index',           'icon' => 'ticket',          'label' => 'Tickets'],
        ],
        'System' => [
            ['route' => 'admin.seo.index',           'icon' => 'magnifying-glass-chart', 'label' => 'SEO'],
            ['route' => 'admin.currencies.index',    'icon' => 'coins',           'label' => 'Currencies'],
            ['route' => 'admin.translations.index',  'icon' => 'language',        'label' => 'Translations'],
            ['route' => 'admin.communication.index', 'icon' => 'paper-plane',      'label' => 'Communication'],
            ['route' => 'admin.roles.index',         'icon' => 'key',             'label' => 'Roles'],
            ['route' => 'admin.visitor-reports',     'icon' => 'chart-line',     'label' => 'Visitors'],
            ['route' => 'admin.system-settings',     'icon' => 'sliders',         'label' => 'Settings'],
            ['route' => 'admin.cache.manage',        'icon' => 'bolt',            'label' => 'Cache'],
            ['route' => 'admin.media.index',         'icon' => 'images',          'label' => 'Media'],
            ['route' => 'admin.backup.index',        'icon' => 'database',        'label' => 'Backup'],
            ['route' => 'admin.audit-logs.index',    'icon' => 'shield-halved',   'label' => 'Audit'],
        ],
        'Seller' => [
            ['route' => 'subadmin.dashboard',    'icon' => 'gauge-high',         'label' => 'Dashboard'],
            ['route' => 'subadmin.bills.create', 'icon' => 'plus-circle',        'label' => 'New'],
            ['route' => 'subadmin.bills.index',  'icon' => 'file-invoice-dollar', 'label' => 'Bills'],
            ['route' => 'subadmin.accounts',     'icon' => 'wallet',             'label' => 'Accounts'],
        ],
        'Website' => [
            ['route' => 'home', 'icon' => 'arrow-up-right-from-square', 'label' => 'Website', 'target' => '_blank'],
        ],
    ];

    // One-word Bangla menu names (the English name is kept in data-label so search still matches it).
    $bn = [
        'Dashboard' => 'ড্যাশবোর্ড', 'Catalog' => 'ক্যাটালগ', 'Books' => 'বই', 'Electronics' => 'ইলেকট্রনিক্স',
        'Stationery' => 'স্টেশনারি', 'Appliances' => 'যন্ত্রপাতি', 'Bundles' => 'প্যাকেজ', 'Categories' => 'বিভাগ',
        'Authors' => 'লেখক', 'Publishers' => 'প্রকাশক', 'Ebooks' => 'ই-বুক', 'Titles' => 'শিরোনাম', 'Club' => 'ক্লাব',
        'Reports' => 'প্রতিবেদন', 'Royalties' => 'রয়্যালটি', 'Payouts' => 'উত্তোলন', 'Logs' => 'লগ',
        'Purchases' => 'ক্রয়', 'Payments' => 'পরিশোধ', 'Ledgers' => 'খতিয়ান', 'Monthly' => 'মাসিক', 'Items' => 'পণ্য',
        'Production' => 'উৎপাদন', 'Stock' => 'মজুদ', 'Locations' => 'অবস্থান', 'Transfers' => 'স্থানান্তর',
        'Wastage' => 'অপচয়', 'Alerts' => 'সতর্কতা', 'Movements' => 'চলাচল',
        'Accounting' => 'হিসাব', 'Transactions' => 'লেনদেন', 'Invoices' => 'চালান', 'Quotations' => 'কোটেশন',
        'Ledger' => 'বহি', 'Tax' => 'কর', 'Payroll' => 'বেতন', 'Employees' => 'কর্মী',
        'Content' => 'কনটেন্ট', 'Blog' => 'ব্লগ', 'Honorariums' => 'সম্মানী', 'Webzines' => 'ওয়েবজিন',
        'Sales' => 'বিক্রয়', 'POS' => 'কাউন্টার', 'Orders' => 'অর্ডার', 'Affiliates' => 'অ্যাফিলিয়েট', 'Receipts' => 'রসিদ',
        'Gateways' => 'গেটওয়ে', 'SMS' => 'এসএমএস', 'Bills' => 'বিল', 'Requests' => 'অনুরোধ',
        'Users' => 'ব্যবহারকারী', 'Sellers' => 'বিক্রেতা', 'Customers' => 'গ্রাহক', 'Approvals' => 'অনুমোদন',
        'Security' => 'নিরাপত্তা', 'Staff' => 'কর্মকর্তা', 'Campaigns' => 'ক্যাম্পেইন', 'Libraries' => 'গ্রন্থাগার', 'Tickets' => 'টিকিট',
        'System' => 'সিস্টেম', 'SEO' => 'এসইও', 'Currencies' => 'মুদ্রা', 'Translations' => 'অনুবাদ', 'Communication' => 'যোগাযোগ',
        'Roles' => 'ভূমিকা', 'Visitors' => 'দর্শক', 'Settings' => 'সেটিংস', 'Cache' => 'ক্যাশ', 'Media' => 'মিডিয়া',
        'Backup' => 'ব্যাকআপ', 'Audit' => 'নিরীক্ষা', 'Seller' => 'বিক্রেতা', 'New' => 'নতুন', 'Accounts' => 'হিসাব',
        'Website' => 'ওয়েবসাইট',
    ];
    $t = fn ($s) => $bn[$s] ?? $s;

    $routePermissions = [
        'admin.books' => 'catalog.view',
        'admin.bundles.index' => 'catalog.view',
        'admin.categories' => 'catalog.view',
        'admin.genres.index' => 'catalog.view',
        'admin.authors' => 'catalog.view',
        'admin.publishers' => 'catalog.view',
        'admin.ebooks' => 'ebooks.view',
        'admin.subscriptions.index' => 'ebooks.view',
        'admin.ebook-sales-report' => 'ebooks.view',
        'admin.author-royalties.index' => 'royalties.manage',
        'admin.author-payouts.index' => 'royalties.manage',
        'admin.royalty-payout-logs' => 'royalties.manage',
        'admin.purchases.index' => 'purchases.manage',
        'admin.purchases.payments' => 'purchases.manage',
        'admin.purchases.ledger' => 'purchases.manage',
        'admin.purchases.monthly-report' => 'purchases.manage',
        'admin.purchases.stationery' => 'purchases.manage',
        'admin.purchases.electronics' => 'purchases.manage',
        'admin.purchases.items.stock' => 'purchases.manage',
        'admin.production.index' => 'catalog.view',
        'admin.inventory.index' => 'catalog.view',
        'admin.inventory.locations' => 'catalog.view',
        'admin.inventory.transfers' => 'catalog.view',
        'admin.inventory.wastages' => 'catalog.view',
        'admin.inventory.alerts' => 'catalog.view',
        'admin.inventory.logs' => 'catalog.view',
        'admin.accounting.index' => 'accounting.view',
        'admin.accounting.invoices.index' => 'accounting.view',
        'admin.accounting.customer-ledger.index' => 'accounting.view',
        'admin.accounting.tax-vat-deductions.index' => 'accounting.view',
        'admin.accounting.salary.index' => 'accounting.salary_disburse',
        'admin.accounting.employees.index' => 'staff.view',
        'admin.accounting.reports.index' => 'accounting.reports',
        'admin.blog' => 'editorial.view',
        'admin.author-honorariums.index' => 'editorial.honorarium',
        'admin.webzines' => 'editorial.view',
        'admin.pos.index' => 'pos.operate',
        'admin.ecommerce-orders' => 'orders.view',
        'admin.affiliates.index' => 'marketing.affiliates_manage',
        'admin.gateway-reports' => 'orders.view',
        'admin.payments.index' => 'orders.view',
        'admin.customers' => 'support.broadcast',
        'admin.sms.index' => 'support.broadcast',
        'admin.orders' => 'orders.view',
        'admin.book-requests.index' => 'support.view',
        'admin.users' => 'users.view',
        'admin.tickets.index' => 'support.view',
        'admin.users.security.index' => 'security.sessions',
        'admin.registrations.index' => 'users.view',
        'admin.registrations.authors' => 'users.view',
        'admin.registrations.publishers' => 'users.view',
        'admin.registrations.sellers' => 'users.view',
        'admin.registrations.customers' => 'users.view',
        'admin.registrations.approvals' => 'users.view',
        'admin.sub-admins.index' => 'roles.manage',
        'admin.roles.index' => 'roles.manage',
        'admin.seo.index' => 'settings.manage',
        'admin.currencies.index' => 'settings.manage',
        'admin.translations.index' => 'editorial.view',
        'admin.communication.index' => 'support.broadcast',
        'admin.visitor-reports' => 'marketing.view',
        'admin.system-settings' => 'settings.manage',
        'admin.cache.manage' => 'settings.manage',
        'admin.media.index' => 'catalog.view',
        'admin.backup.index' => 'settings.manage',
        'admin.audit-logs.index' => 'activity_logs.view',
    ];
@endphp

<aside class="adm-side" data-sidebar>
    <div class="adm-side__header d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="adm-brand text-decoration-none" title="{{ config('brand.name') }}">
            <x-brand-logo :size="24" :height="24" :width="24" />
            <span class="adm-brand__text">
                <span class="adm-brand__name d-block text-truncate">{{ config('brand.name') }}</span>
                <span class="adm-brand__sub text-truncate">{{ config('brand.tagline') }}</span>
            </span>
        </a>
        <button type="button" class="adm-side__close d-lg-none btn btn-sm text-white" data-side-close aria-label="Close sidebar">
            <i class="fa-solid fa-xmark fs-5"></i>
        </button>
        <button type="button" class="adm-side__desktop-toggle d-none d-lg-flex btn btn-sm text-white-50 p-1 border-0" data-side-toggle title="Menu" aria-label="Menu">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
    </div>

    <!-- 1. Spotlight Search Bar (Instant Filter) -->
    <div class="adm-side__search-wrap">
        <div class="adm-side__search-box">
            <i class="fa-solid fa-magnifying-glass adm-side__search-icon"></i>
            <input type="text" id="admSidebarSearch" class="adm-side__search-input" placeholder="Search (Ctrl+K)" autocomplete="off">
            <span id="admSidebarMatchCounter" class="adm-side__match-badge d-none">0</span>
            <span class="adm-side__search-shortcut" id="admSidebarShortcut">⌘K</span>
            <button type="button" id="admSidebarSearchClear" class="adm-side__search-clear" title="Clear">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- 2. Quick Access / Favorites Chip Bar -->
    <div class="adm-side__favorites">
        <a href="{{ route('admin.pos.index') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.pos.*') ? 'is-active' : '' }}" title="POS" data-tooltip="POS">
            <i class="fa-solid fa-cash-register text-warning"></i>
            <span>POS</span>
        </a>
        <a href="{{ route('admin.ecommerce-orders') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.ecommerce-orders*') ? 'is-active' : '' }}" title="Orders" data-tooltip="Orders">
            <i class="fa-solid fa-cart-shopping text-info"></i>
            <span>Orders</span>
        </a>
        <a href="{{ route('admin.books') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.books*') ? 'is-active' : '' }}" title="Books" data-tooltip="Books">
            <i class="fa-solid fa-book text-success"></i>
            <span>Books</span>
        </a>
        <a href="{{ route('admin.currencies.index') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.currencies.*') ? 'is-active' : '' }}" title="Currencies" data-tooltip="Currencies">
            <i class="fa-solid fa-coins text-warning"></i>
            <span>FX</span>
        </a>
        <a href="{{ route('admin.tickets.index') }}" class="adm-side__fav-chip {{ request()->routeIs('admin.tickets.*') ? 'is-active' : '' }}" title="Tickets" data-tooltip="Tickets">
            <i class="fa-solid fa-ticket text-danger"></i>
            <span>Tickets</span>
        </a>
    </div>

    <!-- 3. Dynamic Collapsible Navigation Tree -->
    <nav class="adm-nav" id="admNavTree">
        @php $sidebarAuthUser = auth()->user(); @endphp
        @foreach ($menu as $group => $items)
            @php
                $items = array_filter($items, function ($i) use ($sidebarAuthUser, $routePermissions) {
                    if (! Route::has($i['route'])) return false;
                    if (! $sidebarAuthUser) return false;
                    if ($sidebarAuthUser->isAdmin()) return true;

                    // STRICT EXCLUSIVE LOCK: Core security, system settings, gateways & IAM are Super Admin only
                    $exclusiveAdminRoutes = [
                        'admin.roles.index',
                        'admin.system-settings',
                        'admin.backup.index',
                        'admin.cache.manage',
                        'admin.audit-logs.index',
                        'admin.users.security.index',
                        'admin.sub-admins.index',
                        'admin.payments.index',
                        'admin.currencies.index',
                    ];
                    if (in_array($i['route'], $exclusiveAdminRoutes, true)) {
                        return false;
                    }

                    if ($i['route'] === 'home' || $i['route'] === 'admin.dashboard') return true;
                    $reqPerm = $routePermissions[$i['route']] ?? null;
                    if (! $reqPerm) return true;
                    return $sidebarAuthUser->hasPermission($reqPerm);
                });
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
                            <span>{{ $t($group) }}</span>
                            <i class="fa-solid fa-chevron-down adm-nav__group-chevron"></i>
                        </div>
                        <div class="adm-nav__group-items" id="{{ $groupId }}">
                            @foreach ($items as $item)
                                @php
                                    $base = preg_replace('/\.index$/', '', $item['route']);
                                    // An *.index link only owns its whole prefix when no sibling link lives under it
                                    // (otherwise Stock/Purchases would stay lit on every sibling page).
                                    $ownsPrefix = collect($items)->filter(fn ($o) => $o['route'] !== $item['route'] && str_starts_with($o['route'], $base . '.'))->isEmpty();
                                    $isRouteActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*') || ($ownsPrefix && request()->routeIs($base . '.*'));
                                    $active = isset($item['category']) ? ($isRouteActive && request('category') === $item['category']) : ($isRouteActive && empty(request('category')));
                                    // Invoices / Quotations are two views of one hub stream (stream=invoices, type=…)
                                    $onInvoiceStream = request()->routeIs('admin.accounting.index') && request('stream') === 'invoices';
                                    if ($item['route'] === 'admin.accounting.invoices.index') {
                                        $wantsQuotation = ($item['type'] ?? null) === 'quotation';
                                        $active = $onInvoiceStream && ((request('type') === 'quotation') === $wantsQuotation);
                                    } elseif ($item['route'] === 'admin.accounting.index' && $onInvoiceStream) {
                                        $active = false;
                                    }
                                    $href = $item['url'] ?? route($item['route']);
                                @endphp
                                <a href="{{ $href }}"
                                   class="adm-nav__link {{ $active ? 'is-active' : '' }}"
                                   data-label="{{ strtolower($item['label'] . ' ' . $group . ' ' . $t($item['label']) . ' ' . $t($group)) }}"
                                   data-tooltip="{{ $t($item['label']) }}"
                                   @isset($item['target']) target="{{ $item['target'] }}" rel="noopener" @endisset
                                   @if ($active) id="activeNavItem" aria-current="page" @endif>
                                    <i class="fa-solid fa-{{ $item['icon'] }}"></i>
                                    <span class="adm-nav__text">{{ $t($item['label']) }}</span>
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
                           data-label="{{ strtolower($item['label'] . ' ' . $t($item['label'])) }}"
                           data-tooltip="{{ $t($item['label']) }}"
                           @if ($active) id="activeNavItem" aria-current="page" @endif>
                            <i class="fa-solid fa-{{ $item['icon'] }}"></i>
                            <span class="adm-nav__text">{{ $t($item['label']) }}</span>
                        </a>
                    @endforeach
                @endif
            @endif
        @endforeach
    </nav>

    <div class="adm-side__foot adm-brand__text d-flex justify-content-between align-items-center">
        <span>v2.0</span>
        <button type="button" class="btn btn-xs p-0 text-white-50 text-decoration-none" onclick="toggleAllNavGroups()" title="Toggle">
            <i class="fa-solid fa-up-down-left-right" style="font-size: 10px;"></i> Toggle
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

    // 3. Smart Auto-Scroll to Active Item on Page Load & Drawer Open
    function scrollToActiveNav() {
        const activeItem = document.getElementById('activeNavItem');
        const navContainer = document.getElementById('admNavTree');
        if (activeItem && navContainer) {
            const itemRect = activeItem.getBoundingClientRect();
            const navRect = navContainer.getBoundingClientRect();
            if (itemRect.top < navRect.top || itemRect.bottom > navRect.bottom) {
                activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    setTimeout(scrollToActiveNav, 180);
    window.addEventListener('side-open', function() {
        setTimeout(scrollToActiveNav, 250);
    });
})();
</script>
@endpush
