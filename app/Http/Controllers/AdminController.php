<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use App\Services\AdminDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Services\AdminAccessService;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboard,
        private readonly AdminAccessService $accessService
    ) {
    }

    public function index(Request $request): View
    {
        return $this->dashboard($request);
    }

    public function dashboard(Request $request): View
    {
        $period = $request->string('period', 'all')->toString();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $trafficPeriod = $request->string('traffic_period', 'daily')->toString();
        $salesPeriod = $request->string('sales_period', 'monthly')->toString();

        $systemNotice = null;
        try {
            if (Schema::hasTable('admin_dashboard_settings')) {
                $setting = \App\Models\AdminDashboardSetting::where('key', 'system_notice')->first();
                if ($setting && ! empty($setting->value['active'])) {
                    $systemNotice = $setting->value;
                }
            }
        } catch (\Throwable) {}

        $stats = $this->dashboard->filteredStats($dateFrom, $dateTo, $period);
        $salesChart = $this->dashboard->salesSeries($salesPeriod, $dateFrom, $dateTo);
        $visitorChart = $this->dashboard->visitorSeries($trafficPeriod, $dateFrom, $dateTo);
        $recentOrders = $this->dashboard->recentOrders(8);

        return view('admin.dashboard', [
            'stats'         => $stats,
            'salesChart'    => $salesChart,
            'visitorChart'  => $visitorChart,
            'recentOrders'  => $recentOrders,
            'recentBills'   => $this->dashboard->recentBills(6),
            'pendingRegs'   => $this->dashboard->recentRegistrations(5),
            'topSellers'    => $this->dashboard->topSellers(5),
            'systemHealth'  => $this->accessService->systemHealth(),
            'activityLogs'  => $this->accessService->recentLogs(8),
            'systemNotice'  => $systemNotice,
            'currentPeriod' => $period,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'trafficPeriod' => $trafficPeriod,
            'salesPeriod'   => $salesPeriod,
        ]);
    }

    /**
     * Printable / PDF download report view for sales and visitor metrics.
     */
    public function printReport(Request $request): View
    {
        $period = $request->string('period', 'all')->toString();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $stats = $this->dashboard->filteredStats($dateFrom, $dateTo, $period);
        $salesChart = $this->dashboard->salesSeries('monthly', $dateFrom, $dateTo);
        $visitorChart = $this->dashboard->visitorSeries('daily', $dateFrom, $dateTo);
        $recentOrders = $this->dashboard->recentOrders(20);

        return view('admin.reports.print', [
            'stats'         => $stats,
            'salesChart'    => $salesChart,
            'visitorChart'  => $visitorChart,
            'recentOrders'  => $recentOrders,
            'period'        => $period,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
        ]);
    }

    /**
     * Quick stock quantity updater from dashboard low-stock widget.
     */
    public function quickUpdateStock(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'book_id'  => 'required|integer|exists:books,id',
            'quantity' => 'required|integer|min:0|max:100000',
        ]);

        $book = \Modules\Book\Models\Book::findOrFail($validated['book_id']);
        $book->stock_quantity = $validated['quantity'];
        $book->save();

        return response()->json([
            'success' => true,
            'message' => "বই '{$book->title}'-এর নতুন স্টক সংখ্যা ({$book->stock_quantity}টি) সংরক্ষিত হয়েছে।",
            'stock'   => $book->stock_quantity,
        ]);
    }

    // ─── Users ──────────────────────────────────────────────────────────

    public function users(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->string('search')->trim() . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $role = $request->string('role')->trim()->value();
                if ($role === 'buyer') {
                    $q->whereIn('role', ['buyer', 'customer']);
                } else {
                    $q->where('role', $role);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', [
            'users'      => $users,
            'roleCounts' => $this->dashboard->roleBreakdown(),
        ]);
    }

    // ─── Orders / bills ─────────────────────────────────────────────────

    public function orders(Request $request): View
    {
        $bills = $this->paginateSafely(
            fn () => Bill::query()
                ->with('seller')
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%' . $request->string('search')->trim() . '%';
                    $q->where(fn ($w) => $w->where('bill_no', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('customer_phone', 'like', $term));
                })
                ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->string('status')))
                ->latest(),
            'bills'
        );

        $totalCount = 0;
        $totalRevenue = 0.0;
        $totalDue = 0.0;
        try {
            if (Schema::hasTable('bills')) {
                $totalCount = (int) Bill::count();
                $totalRevenue = (float) Bill::where('payment_status', 'paid')->sum('total');
                $totalDue = (float) Bill::whereIn('payment_status', ['pending', 'partial'])->sum('total');
            }
        } catch (\Throwable) {}

        return view('admin.orders', [
            'bills'   => $bills,
            'summary' => [
                'count'   => $totalCount,
                'revenue' => $totalRevenue,
                'due'     => $totalDue,
            ],
        ]);
    }

    public function ecommerceOrders(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $search = $request->string('search')->trim()->value();
        $dateFilter = $request->string('date_filter')->trim()->value();

        $query = \App\Models\Order::query()
            ->with(['book', 'user', 'affiliate'])
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('order_number', 'like', $like)
                      ->orWhere('customer_name', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like)
                      ->orWhere('gift_recipient_name', 'like', $like)
                      ->orWhere('district', 'like', $like)
                      ->orWhere('tracking_code', 'like', $like);
                });
            })
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($dateFilter === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($dateFilter === 'this_week', fn ($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($dateFilter === 'this_month', fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->latest();

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => \App\Models\Order::count(),
            'pending'     => \App\Models\Order::where('status', 'pending')->count(),
            'processing'  => \App\Models\Order::where('status', 'processing')->count(),
            'shipped'     => \App\Models\Order::where('status', 'shipped')->count(),
            'delivered'   => \App\Models\Order::where('status', 'delivered')->count(),
            'cancelled'   => \App\Models\Order::where('status', 'cancelled')->count(),
            'revenue'     => \App\Models\Order::whereIn('status', ['delivered', 'shipped', 'processing'])->sum('total_amount'),
        ];

        $books = \Modules\Book\Models\Book::select('id', 'title', 'price', 'discount_price')->get();

        return view('admin.ecommerce-orders', compact('orders', 'stats', 'status', 'search', 'dateFilter', 'books'));
    }

    public function showEcommerceOrder(\App\Models\Order $order)
    {
        $order->load(['book', 'user', 'affiliate']);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'order'   => $order,
            ]);
        }
        return redirect()->route('admin.ecommerce-orders.invoice', $order);
    }

    public function updateEcommerceOrder(Request $request, \App\Models\Order $order)
    {
        $validated = $request->validate([
            'customer_name'          => 'required|string|max:255',
            'customer_phone'         => 'required|string|max:20',
            'district'               => 'required|string',
            'thana'                  => 'nullable|string|max:100',
            'post_code'              => 'nullable|string|max:20',
            'house_road'             => 'nullable|string|max:255',
            'customer_address'       => 'required|string',
            'quantity'               => 'required|integer|min:1',
            'unit_price'             => 'required|numeric|min:0',
            'shipping_cost'          => 'required|numeric|min:0',
            'discount_amount'        => 'nullable|numeric|min:0',
            'gift_wrap_fee'          => 'nullable|numeric|min:0',
            'total_amount'           => 'required|numeric|min:0',
            'status'                 => 'required|string|in:pending,processing,confirmed,shipped,delivered,cancelled,returned',
            'payment_method'         => 'required|string|in:cod,bkash,nagad,rocket,card',
            'payment_status'         => 'required|string|in:pending,paid,partial,unpaid',
            'courier_name'           => 'nullable|string|max:100',
            'tracking_code'          => 'nullable|string|max:100',
            'admin_notes'            => 'nullable|string',
            'is_gift'                => 'nullable|boolean',
            'gift_recipient_name'    => 'nullable|string|max:255',
            'gift_recipient_phone'   => 'nullable|string|max:20',
            'gift_recipient_address' => 'nullable|string',
            'gift_message'           => 'nullable|string',
        ]);

        $validated['is_gift'] = $request->boolean('is_gift');
        $order->update($validated);

        $this->accessService->log('order_update', "অর্ডার #{$order->order_number} তথ্য আপডেট করা হয়েছে");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'অর্ডার সফলভাবে আপডেট হয়েছে!']);
        }

        return back()->with('success', "অর্ডার #{$order->order_number} সফলভাবে আপডেট হয়েছে!");
    }

    public function updateEcommerceOrderStatus(Request $request, \App\Models\Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,confirmed,shipped,delivered,cancelled,returned',
            'courier_name' => 'nullable|string|max:100',
            'tracking_code' => 'nullable|string|max:100',
        ]);

        $order->update($validated);
        $this->accessService->log('order_status_update', "অর্ডার #{$order->order_number} স্ট্যাটাস পরিবর্তন: {$order->status}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'অর্ডার স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে!',
                'status'  => $order->status,
                'label'   => $order->status_label,
            ]);
        }

        return back()->with('success', "অর্ডার #{$order->order_number} স্ট্যাটাস আপডেট হয়েছে!");
    }

    public function ecommerceOrderInvoice(\App\Models\Order $order): View
    {
        $order->load(['book', 'user', 'affiliate']);
        
        $invoiceSettings = [
            'sender_name'    => 'আইডিয়া প্রকাশন',
            'sender_address' => 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ',
            'sender_phone'   => '01558712870',
            'sender_email'   => 'ideapbd@gmail.com',
            'sender_website' => 'www.ideaabd.com',
            'invoice_title'  => 'ক্যাশ মেমো / ইনভয়েস',
            'invoice_terms'  => 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই হেল্পলাইনে যোগাযোগ করুন।',
            'invoice_footer' => 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!',
        ];

        if (Schema::hasTable('admin_dashboard_settings')) {
            $settingRow = \App\Models\AdminDashboardSetting::where('key', 'invoice_settings')->first();
            if ($settingRow && !empty($settingRow->value)) {
                $invoiceSettings = array_merge($invoiceSettings, $settingRow->value);
            }
        }

        return view('admin.orders.ecommerce-invoice', compact('order', 'invoiceSettings'));
    }

    public function ecommerceOrderSlip(\App\Models\Order $order): View
    {
        $order->load(['book', 'user', 'affiliate']);

        $invoiceSettings = [
            'sender_name'    => 'আইডিয়া প্রকাশন',
            'sender_address' => 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ',
            'sender_phone'   => '01558712870',
            'sender_email'   => 'ideapbd@gmail.com',
            'sender_website' => 'www.ideaabd.com',
        ];

        if (Schema::hasTable('admin_dashboard_settings')) {
            $settingRow = \App\Models\AdminDashboardSetting::where('key', 'invoice_settings')->first();
            if ($settingRow && !empty($settingRow->value)) {
                $invoiceSettings = array_merge($invoiceSettings, $settingRow->value);
            }
        }

        return view('admin.orders.ecommerce-slip', compact('order', 'invoiceSettings'));
    }

    public function destroyEcommerceOrder(\App\Models\Order $order)
    {
        $orderNum = $order->order_number;
        $order->delete();
        $this->accessService->log('order_delete', "অর্ডার #{$orderNum} মুছে ফেলা হয়েছে");

        return back()->with('success', "অর্ডার #{$orderNum} সফলভাবে মুছে ফেলা হয়েছে।");
    }

    // ─── Catalog & content lists ────────────────────────────────────────

    public function books(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $categoryId = $request->input('category_id');
        $stockFilter = $request->string('stock')->trim()->value();
        $status = $request->input('is_active');

        $query = \Modules\Book\Models\Book::query()
            ->with(['category', 'authorLink'])
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('title', 'like', $like)
                      ->orWhere('subtitle', 'like', $like)
                      ->orWhere('author_name', 'like', $like)
                      ->orWhere('isbn', 'like', $like)
                      ->orWhere('slug', 'like', $like);
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($stockFilter === 'pre_order', fn ($q) => $q->where('stock_status', 'pre_order'))
            ->when($stockFilter === 'low', fn ($q) => $q->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0))
            ->when($stockFilter === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->when($stockFilter === 'in_stock', fn ($q) => $q->where('stock_quantity', '>', 5))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->latest();

        $books = $query->paginate(20)->withQueryString();

        $categories = DB::table('categories')->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total'     => \Modules\Book\Models\Book::count(),
            'active'    => \Modules\Book\Models\Book::where('is_active', true)->count(),
            'pre_order' => \Modules\Book\Models\Book::where('stock_status', 'pre_order')->count(),
            'low_stock' => \Modules\Book\Models\Book::where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->count(),
            'out_stock' => \Modules\Book\Models\Book::where('stock_quantity', '<=', 0)->count(),
        ];

        return view('admin.books', compact('books', 'categories', 'stats'));
    }

    public function ebooks(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $categoryId = $request->input('category_id');
        $status = $request->input('is_active');

        $query = \Modules\Ebook\Models\Ebook::query()
            ->with(['category', 'authorLink'])
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('title', 'like', $like)
                      ->orWhere('author_name', 'like', $like)
                      ->orWhere('slug', 'like', $like);
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->latest();

        $ebooks = $query->paginate(20)->withQueryString();
        $categories = DB::table('categories')->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total'  => \Modules\Ebook\Models\Ebook::count(),
            'active' => \Modules\Ebook\Models\Ebook::where('is_active', true)->count(),
            'free'   => \Modules\Ebook\Models\Ebook::where(fn($q) => $q->whereNull('price')->orWhere('price', 0))->count(),
        ];

        return view('admin.ebooks', compact('ebooks', 'categories', 'stats'));
    }

    public function categories(Request $request): View
    {
        $search   = $request->string('search')->trim()->value();
        $status   = $request->input('is_active');
        $parentId = $request->input('parent_id');

        $query = \Modules\Book\Models\Category::query()
            ->with(['parent'])
            ->withCount('books')
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('slug', 'like', $like)
                      ->orWhere('description', 'like', $like);
                });
            })
            ->when($parentId === 'root', fn ($q) => $q->whereNull('parent_id'))
            ->when($parentId && $parentId !== 'root', fn ($q) => $q->where('parent_id', $parentId))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->orderBy('sort_order')
            ->orderBy('name');

        $categories = $query->paginate(25)->withQueryString();
        $parentCategories = \Modules\Book\Models\Category::whereNull('parent_id')->orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total'    => \Modules\Book\Models\Category::count(),
            'active'   => \Modules\Book\Models\Category::where('is_active', true)->count(),
            'parents'  => \Modules\Book\Models\Category::whereNull('parent_id')->count(),
            'children' => \Modules\Book\Models\Category::whereNotNull('parent_id')->count(),
        ];

        return view('admin.categories', compact('categories', 'parentCategories', 'stats'));
    }

    public function blog(Request $request): View
    {
        return view('admin.blog', [
            'posts' => $this->listing('blog_posts', $request, ['title', 'slug']),
        ]);
    }

    public function webzines(Request $request): View
    {
        return view('admin.webzines', [
            'webzines' => $this->listing('webzines', $request, ['title', 'slug']),
        ]);
    }

    public function authors(Request $request): View
    {
        return view('admin.authors', [
            'authors' => $this->listing('authors', $request, ['name', 'slug']),
        ]);
    }

    public function publishers(Request $request): View
    {
        return view('admin.publishers', [
            'publishers' => $this->listing('publishers', $request, ['name', 'slug']),
        ]);
    }

    // ─── internals ──────────────────────────────────────────────────────

    /**
     * Generic paginated listing straight off the query builder.
     *
     * The catalog modules are optional on a given deployment, so this reads the
     * table directly (no model class required) and returns an empty paginator
     * when the table has not been migrated.
     *
     * @param  list<string>  $searchable  columns the search box filters on
     */
    private function listing(string $table, Request $request, array $searchable): mixed
    {
        return $this->paginateSafely(function () use ($table, $request, $searchable) {
            $query = DB::table($table);

            if ($request->filled('search') && $searchable !== []) {
                $term = '%' . $request->string('search')->trim() . '%';
                $query->where(function ($w) use ($searchable, $term, $table) {
                    foreach ($searchable as $column) {
                        if (Schema::hasColumn($table, $column)) {
                            $w->orWhere($column, 'like', $term);
                        }
                    }
                });
            }

            return Schema::hasColumn($table, 'created_at')
                ? $query->orderByDesc('created_at')
                : $query->orderByDesc('id');
        }, $table);
    }

    /**
     * Paginate a query, degrading to an empty paginator if the table is missing
     * or the driver rejects the query — an admin list page should render either
     * way rather than throwing a 500.
     */
    private function paginateSafely(callable $builder, string $table, int $perPage = 20): mixed
    {
        try {
            if (! Schema::hasTable($table)) {
                return $this->emptyPaginator($perPage);
            }

            return $builder()->paginate($perPage)->withQueryString();
        } catch (\Throwable $e) {
            report($e);

            return $this->emptyPaginator($perPage);
        }
    }

    // ─── Visitor Reports & Traffic Analytics ─────────────────────────────

    public function visitorReports(Request $request): View
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'today_views'     => \App\Models\VisitorLog::where('visited_at', '>=', $today)->count(),
            'today_uniques'   => \App\Models\VisitorLog::where('visited_at', '>=', $today)->distinct('ip_address')->count('ip_address'),
            'week_views'      => \App\Models\VisitorLog::where('visited_at', '>=', $thisWeek)->count(),
            'month_views'     => \App\Models\VisitorLog::where('visited_at', '>=', $thisMonth)->count(),
            'total_views'     => \App\Models\VisitorLog::count(),
            'total_uniques'   => \App\Models\VisitorLog::distinct('ip_address')->count('ip_address'),
        ];

        // Device Breakdown
        $devices = \App\Models\VisitorLog::select('device', DB::raw('count(*) as total'))
            ->groupBy('device')
            ->pluck('total', 'device')
            ->toArray();

        // Browser Breakdown
        $browsers = \App\Models\VisitorLog::select('browser', DB::raw('count(*) as total'))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Top Visited Pages
        $topPages = \App\Models\VisitorLog::select('url', 'page_title', DB::raw('count(*) as views'))
            ->groupBy('url', 'page_title')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Recent Logs Stream
        $logs = \App\Models\VisitorLog::with('user')
            ->when($request->filled('device'), fn($q) => $q->where('device', $request->string('device')))
            ->when($request->filled('search'), function($q) use ($request) {
                $term = '%' . $request->string('search')->trim() . '%';
                $q->where(fn($w) => $w->where('url', 'like', $term)->orWhere('ip_address', 'like', $term)->orWhere('page_title', 'like', $term));
            })
            ->latest('visited_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.analytics', [
            'stats'    => $stats,
            'devices'  => $devices,
            'browsers' => $browsers,
            'topPages' => $topPages,
            'logs'     => $logs,
        ]);
    }

    // ─── Customer Directory & Bulk Broadcast ────────────────────────────

    public function customers(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $district = $request->string('district')->trim()->value();

        $query = User::query()
            ->whereIn('role', ['buyer', 'customer'])
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_amount')
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('phone', 'like', $like)
                      ->orWhere('email', 'like', $like);
                });
            })
            ->when($district, function ($q, $d) {
                if (Schema::hasColumn('users', 'district')) {
                    $q->where('district', $d);
                } else {
                    $q->whereHas('orders', fn ($oq) => $oq->where('district', $d));
                }
            })
            ->latest();

        $customers = $query->paginate(20)->withQueryString();

        $hasLoyaltyPoints = Schema::hasColumn('users', 'loyalty_points');

        $summary = [
            'total_customers' => User::whereIn('role', ['buyer', 'customer'])->count(),
            'active_buyers'   => User::whereIn('role', ['buyer', 'customer'])->has('orders')->count(),
            'total_spent_sum' => \App\Models\Order::sum('total_amount'),
            'loyalty_points'  => $hasLoyaltyPoints ? User::whereIn('role', ['buyer', 'customer'])->sum('loyalty_points') : 0,
        ];

        return view('admin.customers.index', [
            'customers' => $customers,
            'summary'   => $summary,
        ]);
    }

    public function broadcastMessage(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'target_group' => 'required|string|in:all,with_orders,high_value',
            'channel'      => 'required|string|in:sms,notice,email',
            'title'        => 'nullable|string|max:255',
            'message_body' => 'required|string|max:1000',
        ]);

        $query = User::whereIn('role', ['buyer', 'customer']);
        if ($validated['target_group'] === 'with_orders') {
            $query->has('orders');
        }

        $recipientCount = $query->count();
        $messageBody = $validated['message_body'];

        // If channel is 'notice', update system notice in settings
        if ($validated['channel'] === 'notice') {
            \App\Models\AdminDashboardSetting::updateOrCreate(
                ['key' => 'system_notice'],
                [
                    'value' => [
                        'text'   => $messageBody,
                        'active' => true,
                        'type'   => 'info',
                    ],
                    'updated_by' => auth()->id(),
                ]
            );
        }

        $this->accessService->log('broadcast_message', "{$recipientCount} জন গ্রাহককে সফলভাবে মেসেজ/নোটিফিকেশন পাঠানো হয়েছে ({$validated['channel']})");

        return back()->with('success', "{$recipientCount} জন গ্রাহকের কাছে সফলভাবে ব্রডকাস্ট মেসেজ পাঠানো হয়েছে!");
    }
}
