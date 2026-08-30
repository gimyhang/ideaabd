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
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('order_number', 'like', $like)
                                  ->orWhere('customer_name', 'like', $like)
                                  ->orWhere('customer_phone', 'like', $like)
                                  ->orWhere('customer_email', 'like', $like)
                                  ->orWhere('gift_recipient_name', 'like', $like)
                                  ->orWhere('district', 'like', $like)
                                  ->orWhere('tracking_code', 'like', $like)
                                  ->orWhereHas('book', fn($bq) => $bq->where('title', 'like', $like));
                            });
                        }
                    });
                }
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

    /**
     * Normalize search input: converts Bengali numerals to English,
     * strips unnecessary control characters, and splits into distinct search tokens.
     *
     * @return array{raw: string, tokens: array<string>, normalized: string}
     */
    private function parseSearchKeywords(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return ['raw' => '', 'tokens' => [], 'normalized' => ''];
        }

        // Map Bangla digits to English digits
        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $normalizedDigits = str_replace($bnDigits, $enDigits, $term);

        // Split into tokens by whitespace
        $words = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = [];
        foreach ($words as $w) {
            $w = trim($w);
            if ($w !== '') {
                $tokens[] = $w;
                // Also add digit-normalized variation if it contains numbers
                $digitVariant = str_replace($bnDigits, $enDigits, $w);
                if ($digitVariant !== $w && !in_array($digitVariant, $tokens, true)) {
                    $tokens[] = $digitVariant;
                }
            }
        }

        return [
            'raw'        => $term,
            'tokens'     => array_values(array_unique($tokens)),
            'normalized' => $normalizedDigits,
        ];
    }

    public function books(Request $request): View
    {
        $search        = $request->string('search')->trim()->value();
        $authorId      = $request->input('author_id');
        $publisherId   = $request->input('publisher_id');
        $categoryId    = $request->input('category_id');
        $stockFilter   = $request->string('stock')->trim()->value();
        $format        = $request->string('format')->trim()->value();
        $coverType     = $request->string('cover_type')->trim()->value();
        $minPrice      = $request->filled('min_price') ? $request->float('min_price') : null;
        $maxPrice      = $request->filled('max_price') ? $request->float('max_price') : null;
        $discountOnly  = $request->boolean('discount_only') || $request->input('discount_only') === '1';
        $status        = $request->input('is_active');
        $modStatus     = $request->string('mod_status')->trim()->value();
        $sort          = $request->string('sort')->trim()->value() ?: 'latest';
        $perPage       = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 200], true) ? (int) $request->input('per_page') : 20;

        $query = \Modules\Book\Models\Book::query()
            ->with(['category', 'publisher', 'authorLink', 'authors'])
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];

                if (!empty($tokens)) {
                    $q->where(function ($masterQuery) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $masterQuery->where(function ($w) use ($like, $token) {
                                $w->where('title', 'like', $like)
                                  ->orWhere('subtitle', 'like', $like)
                                  ->orWhere('author_name', 'like', $like)
                                  ->orWhere('translator_name', 'like', $like)
                                  ->orWhere('editor_name', 'like', $like)
                                  ->orWhere('cover_artist', 'like', $like)
                                  ->orWhere('isbn', 'like', $like)
                                  ->orWhere('sku', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('summary', 'like', $like)
                                  ->orWhere('description', 'like', $like)
                                  ->orWhereHas('publisher', function ($pub) use ($like) {
                                      $pub->where('name', 'like', $like)
                                          ->orWhere('slug', 'like', $like)
                                          ->orWhere('phone', 'like', $like)
                                          ->orWhere('email', 'like', $like);
                                  })
                                  ->orWhereHas('category', function ($cat) use ($like) {
                                      $cat->where('name', 'like', $like)
                                          ->orWhere('slug', 'like', $like);
                                  })
                                  ->orWhereHas('authorLink', function ($aut) use ($like) {
                                      $aut->where('name', 'like', $like)
                                          ->orWhere('slug', 'like', $like)
                                          ->orWhere('phone', 'like', $like)
                                          ->orWhere('email', 'like', $like);
                                  })
                                  ->orWhereHas('authors', function ($aut) use ($like) {
                                      $aut->where('name', 'like', $like)
                                          ->orWhere('slug', 'like', $like);
                                  })
                                  ->orWhereHas('tags', function ($tag) use ($like) {
                                      $tag->where('name', 'like', $like)
                                          ->orWhere('slug', 'like', $like);
                                  });

                                // In-house publisher "Idea Prakashon" match for null publisher_id
                                $lowerToken = mb_strtolower($token);
                                if (
                                    str_contains($lowerToken, 'idea') || 
                                    str_contains($token, 'আইডিয়া') || 
                                    str_contains($token, 'আইডিয়া') || 
                                    str_contains($token, 'প্রকাশন') ||
                                    str_contains($token, 'আইডিয়া প্রকাশন')
                                ) {
                                    $w->orWhereNull('publisher_id');
                                }
                            });
                        }
                    });
                }
            })
            ->when($authorId, function ($q, $aId) {
                $q->where(function ($sq) use ($aId) {
                    $sq->where('author_link_id', $aId)
                       ->orWhereHas('authors', fn($aq) => $aq->where('authors.id', $aId));
                });
            })
            ->when($publisherId, function ($q, $pId) {
                if ($pId === 'idea' || $pId === 'in_house') {
                    $q->whereNull('publisher_id');
                } elseif ($pId === 'registered') {
                    $q->whereNotNull('publisher_id');
                } else {
                    $q->where('publisher_id', $pId);
                }
            })
            ->when($categoryId, function ($q, $cId) {
                $childIds = DB::table('categories')->where('parent_id', $cId)->whereNull('deleted_at')->pluck('id')->all();
                $allIds = array_merge([(int)$cId], $childIds);
                $q->whereIn('category_id', $allIds);
            })
            ->when($stockFilter === 'pre_order', fn ($q) => $q->where('stock_status', 'pre_order'))
            ->when($stockFilter === 'low', fn ($q) => $q->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0))
            ->when($stockFilter === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->when($stockFilter === 'in_stock', fn ($q) => $q->where('stock_quantity', '>', 5))
            ->when($format && in_array($format, ['printed', 'ebook', 'both'], true), fn ($q) => $q->where('format', $format))
            ->when($coverType && in_array($coverType, ['paperback', 'hardcover', 'both'], true), fn ($q) => $q->where('cover_type', $coverType))
            ->when($minPrice !== null, fn ($q) => $q->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn ($q) => $q->where('price', '<=', $maxPrice))
            ->when($discountOnly, fn ($q) => $q->whereNotNull('discount_price')->where('discount_price', '>', 0)->whereColumn('discount_price', '<', 'price'))
            ->when($modStatus !== '', fn ($q) => $q->where('mod_status', $modStatus))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status));

        match ($sort) {
            'oldest'        => $query->oldest('id'),
            'title_asc'     => $query->orderBy('title', 'asc'),
            'title_desc'    => $query->orderBy('title', 'desc'),
            'price_low'     => $query->orderBy('price', 'asc'),
            'price_high'    => $query->orderBy('price', 'desc'),
            'sales_high'    => $query->orderByDesc('sales_count'),
            'stock_low'     => $query->orderBy('stock_quantity', 'asc'),
            'stock_high'    => $query->orderByDesc('stock_quantity'),
            'discount_high' => $query->whereNotNull('discount_price')->orderByRaw('(price - discount_price) DESC'),
            default         => $query->latest('id'),
        };

        $books = $query->paginate($perPage)->withQueryString();

        $authors    = \Modules\Author\Models\Author::whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
        $categoriesRaw = DB::table('categories')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $categories = [];
        $parents = $categoriesRaw->whereNull('parent_id');
        $children = $categoriesRaw->whereNotNull('parent_id');
        foreach ($parents as $p) {
            $categories[$p->id] = $p->name;
            foreach ($children->where('parent_id', $p->id) as $c) {
                $categories[$c->id] = '— ' . $c->name . ' (' . $p->name . ')';
            }
        }
        foreach ($children as $c) {
            if (!isset($categories[$c->id])) {
                $categories[$c->id] = $c->name;
            }
        }
        $publishers = \Modules\Publisher\Models\Publisher::whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total'               => \Modules\Book\Models\Book::count(),
            'active'              => \Modules\Book\Models\Book::where('is_active', true)->count(),
            'pending'             => \Modules\Book\Models\Book::where('mod_status', 'pending')->count(),
            'publisher_pending'   => \Modules\Book\Models\Book::whereNotNull('publisher_id')->where('mod_status', 'pending')->count(),
            'publisher_total'     => \Modules\Book\Models\Book::whereNotNull('publisher_id')->count(),
            'pre_order'           => \Modules\Book\Models\Book::where('stock_status', 'pre_order')->count(),
            'low_stock'           => \Modules\Book\Models\Book::where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->count(),
            'out_stock'           => \Modules\Book\Models\Book::where('stock_quantity', '<=', 0)->count(),
            'discount'            => \Modules\Book\Models\Book::whereNotNull('discount_price')->where('discount_price', '>', 0)->whereColumn('discount_price', '<', 'price')->count(),
        ];

        return view('admin.books', compact('books', 'categories', 'publishers', 'authors', 'stats', 'sort', 'perPage'));
    }

    public function toggleBookStatus(Request $request, int $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $book = \Modules\Book\Models\Book::findOrFail($id);
        $book->is_active = !$book->is_active;
        $book->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'is_active' => $book->is_active,
                'message'   => $book->is_active ? 'বইটি লাইভ শপে সক্রিয় করা হয়েছে।' : 'বইটি ড্রাফট / নিষ্ক্রিয় করা হয়েছে।',
            ]);
        }

        return back()->with('success', 'বইয়ের স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।');
    }

    public function approveBook(Request $request, int $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $book = \Modules\Book\Models\Book::findOrFail($id);
        $book->update([
            'mod_status'       => 'approved',
            'is_active'        => true,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => null,
        ]);

        $this->accessService->log('book_approved', "বই '{$book->title}' অনুমোদন করা হয়েছে");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'mod_status' => 'approved',
                'is_active'  => true,
                'message'    => "‘{$book->title}’ বইটি সফলভাবে অনুমোদন করা হয়েছে এবং শপে লাইভ হয়েছে!",
            ]);
        }

        return back()->with('success', "‘{$book->title}’ বইটি সফলভাবে অনুমোদন করা হয়েছে এবং শপে লাইভ হয়েছে!");
    }

    public function rejectBook(Request $request, int $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $reason = $request->input('rejection_reason') ?: 'অসম্পূর্ণ বা ভুল তথ্য রয়েছে।';
        $book = \Modules\Book\Models\Book::findOrFail($id);
        $book->update([
            'mod_status'       => 'rejected',
            'is_active'        => false,
            'rejection_reason' => $reason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        $this->accessService->log('book_rejected', "বই '{$book->title}' বাতিল করা হয়েছে");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'mod_status' => 'rejected',
                'is_active'  => false,
                'message'    => "‘{$book->title}’ বইটি বাতিল করা হয়েছে।",
            ]);
        }

        return back()->with('success', "‘{$book->title}’ বইটি বাতিল করা হয়েছে।");
    }

    public function ebooks(Request $request): View
    {
        $search      = $request->string('search')->trim()->value();
        $authorId    = $request->input('author_id');
        $categoryId  = $request->input('category_id');
        $publisherId = $request->input('publisher_id');
        $status      = $request->input('is_active');
        $priceType   = $request->string('price_type')->trim()->value();
        $sort        = $request->string('sort')->trim()->value() ?: 'latest';
        $perPage     = in_array((int) $request->input('per_page'), [10, 20, 50, 100], true) ? (int) $request->input('per_page') : 20;

        $modStatus   = $request->string('mod_status')->trim()->value();
        $query = \Modules\Ebook\Models\Ebook::query()
            ->with(['category', 'publisher', 'authorLink', 'author', 'authorUser'])
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];

                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like, $token) {
                                $w->where('title', 'like', $like)
                                  ->orWhere('author_name', 'like', $like)
                                  ->orWhere('isbn', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('description', 'like', $like)
                                  ->orWhereHas('publisher', fn($pub) => $pub->where('name', 'like', $like)->orWhere('slug', 'like', $like))
                                  ->orWhereHas('category', fn($cat) => $cat->where('name', 'like', $like)->orWhere('slug', 'like', $like))
                                  ->orWhereHas('authorLink', fn($aut) => $aut->where('name', 'like', $like)->orWhere('slug', 'like', $like))
                                  ->orWhereHas('author', fn($aut) => $aut->where('name', 'like', $like)->orWhere('slug', 'like', $like));

                                $lowerToken = mb_strtolower($token);
                                if (
                                    str_contains($lowerToken, 'idea') || 
                                    str_contains($token, 'আইডিয়া') || 
                                    str_contains($token, 'আইডিয়া')
                                ) {
                                    $w->orWhereNull('publisher_id');
                                }
                            });
                        }
                    });
                }
            })
            ->when($authorId, function ($q, $aId) {
                $q->where(function ($sq) use ($aId) {
                    $sq->where('author_link_id', $aId)
                       ->orWhere('author_id', $aId);
                });
            })
            ->when($categoryId, function ($q, $cId) {
                $childIds = DB::table('categories')->where('parent_id', $cId)->whereNull('deleted_at')->pluck('id')->all();
                $allIds = array_merge([(int)$cId], $childIds);
                $q->whereIn('category_id', $allIds);
            })
            ->when($publisherId, function ($q, $pId) {
                if ($pId === 'idea' || $pId === 'in_house') {
                    $q->whereNull('publisher_id');
                } else {
                    $q->where('publisher_id', $pId);
                }
            })
            ->when($priceType === 'free', fn ($q) => $q->where(fn($sq) => $sq->whereNull('price')->orWhere('price', '<=', 0)))
            ->when($priceType === 'paid', fn ($q) => $q->whereNotNull('price')->where('price', '>', 0))
            ->when($modStatus !== '', fn ($q) => $q->where('mod_status', $modStatus))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status));

        match ($sort) {
            'oldest'     => $query->oldest('id'),
            'title_asc'  => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            'price_low'  => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'sales_high' => $query->orderByDesc('sales_count'),
            default      => $query->latest('id'),
        };

        $ebooks = $query->paginate($perPage)->withQueryString();
        $authors    = \Modules\Author\Models\Author::whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
        $categoriesRaw = DB::table('categories')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $categories = [];
        $parents = $categoriesRaw->whereNull('parent_id');
        $children = $categoriesRaw->whereNotNull('parent_id');
        foreach ($parents as $p) {
            $categories[$p->id] = $p->name;
            foreach ($children->where('parent_id', $p->id) as $c) {
                $categories[$c->id] = '— ' . $c->name . ' (' . $p->name . ')';
            }
        }
        foreach ($children as $c) {
            if (!isset($categories[$c->id])) {
                $categories[$c->id] = $c->name;
            }
        }
        $publishers = \Modules\Publisher\Models\Publisher::whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total'       => \Modules\Ebook\Models\Ebook::count(),
            'active'      => \Modules\Ebook\Models\Ebook::where('is_active', true)->count(),
            'pending'     => \Modules\Ebook\Models\Ebook::where('mod_status', 'pending')->count(),
            'free'        => \Modules\Ebook\Models\Ebook::where(fn($q) => $q->whereNull('price')->orWhere('price', '<=', 0))->count(),
            'paid'        => \Modules\Ebook\Models\Ebook::whereNotNull('price')->where('price', '>', 0)->count(),
            'total_sales' => \Modules\Ebook\Models\Ebook::sum('sales_count'),
        ];

        $defaultPreviewPages = \App\Support\SiteSetting::ebookPreviewLimit();

        return view('admin.ebooks', compact('ebooks', 'categories', 'publishers', 'authors', 'stats', 'sort', 'perPage', 'defaultPreviewPages'));
    }

    public function updateEbookSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'default_preview_pages' => 'required|integer|min:1|max:100',
        ]);

        $settings = \App\Support\SiteSetting::get('ebook_settings') ?: [];
        if (!is_array($settings)) {
            $settings = [];
        }
        $settings['default_preview_pages'] = (int) $validated['default_preview_pages'];

        \App\Models\AdminDashboardSetting::updateOrCreate(
            ['key' => 'ebook_settings'],
            ['value' => json_encode($settings, JSON_UNESCAPED_UNICODE)]
        );

        \App\Support\SiteSetting::clearCache();

        return back()->with('success', 'ই-বুক গ্লোবাল সেটিংস (ডিফল্ট ' . $settings['default_preview_pages'] . ' পেজ প্রিভিউ) সফলভাবে সংরক্ষিত হয়েছে!');
    }

    public function toggleEbookStatus(Request $request, int $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $ebook = \Modules\Ebook\Models\Ebook::findOrFail($id);
        $ebook->is_active = !$ebook->is_active;
        $ebook->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $ebook->is_active,
                'message'   => $ebook->is_active ? 'ই-বুকটি লাইভ স্টোরে দৃশ্যমান করা হয়েছে।' : 'ই-বুকটি ড্রাফট মোডে নেওয়া হয়েছে।',
            ]);
        }

        return back()->with('success', 'ই-বুক স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।');
    }

    public function approveEbook(Request $request, int $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $ebook = \Modules\Ebook\Models\Ebook::findOrFail($id);
        $ebook->update([
            'mod_status'       => 'approved',
            'is_active'        => true,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => null,
        ]);

        $this->accessService->log('ebook_approved', "ই-বুক '{$ebook->title}' অনুমোদন করা হয়েছে");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'mod_status' => 'approved',
                'is_active'  => true,
                'message'    => "‘{$ebook->title}’ ই-বুকটি অনুমোদিত ও লাইভ স্টোরে প্রকাশিত হয়েছে!",
            ]);
        }

        return back()->with('success', "‘{$ebook->title}’ ই-বুকটি অনুমোদিত ও লাইভ স্টোরে প্রকাশিত হয়েছে!");
    }

    public function rejectEbook(Request $request, int $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $reason = $request->input('rejection_reason') ?: 'অসম্পূর্ণ বা সংশোধন প্রয়োজন।';
        $ebook = \Modules\Ebook\Models\Ebook::findOrFail($id);
        $ebook->update([
            'mod_status'       => 'rejected',
            'is_active'        => false,
            'rejection_reason' => $reason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        $this->accessService->log('ebook_rejected', "ই-বুক '{$ebook->title}' বাতিল করা হয়েছে");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'mod_status' => 'rejected',
                'is_active'  => false,
                'message'    => "‘{$ebook->title}’ বইটি সংশোধনের জন্য লেখকের কাছে ফেরত পাঠানো হয়েছে।",
            ]);
        }

        return back()->with('success', "‘{$ebook->title}’ বইটি সংশোধনের জন্য লেখকের কাছে ফেরত পাঠানো হয়েছে।");
    }

    public function categories(Request $request): View
    {
        $search   = $request->string('search')->trim()->value();
        $status   = $request->input('is_active');
        $parentId = $request->input('parent_id');
        $sort     = $request->string('sort')->trim()->value() ?: 'sort_order';
        $perPage  = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true) ? (int) $request->input('per_page') : 25;

        $query = \Modules\Book\Models\Category::query()
            ->with(['parent'])
            ->withCount('books')
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('name', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('description', 'like', $like);
                            });
                        }
                    });
                }
            })
            ->when($parentId === 'root', fn ($q) => $q->whereNull('parent_id'))
            ->when($parentId && $parentId !== 'root', fn ($q) => $q->where('parent_id', $parentId))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status));

        match ($sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'books_desc' => $query->orderByDesc('books_count'),
            'latest'     => $query->latest('id'),
            default      => $query->orderBy('sort_order')->orderBy('name'),
        };

        $categories = $query->paginate($perPage)->withQueryString();
        $parentCategories = \Modules\Book\Models\Category::whereNull('parent_id')->orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total'    => \Modules\Book\Models\Category::count(),
            'active'   => \Modules\Book\Models\Category::where('is_active', true)->count(),
            'parents'  => \Modules\Book\Models\Category::whereNull('parent_id')->count(),
            'children' => \Modules\Book\Models\Category::whereNotNull('parent_id')->count(),
        ];

        return view('admin.categories', compact('categories', 'parentCategories', 'stats', 'sort', 'perPage'));
    }

    public function blog(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $query = \Modules\Blog\Models\BlogPost::query()
            ->with(['category', 'author', 'submitter'])
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('title', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('content', 'like', $like)
                                  ->orWhere('owner_name', 'like', $like)
                                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', $like)->orWhere('slug', 'like', $like))
                                  ->orWhereHas('author', fn($a) => $a->where('name', 'like', $like)->orWhere('phone', 'like', $like));
                            });
                        }
                    });
                }
            })
            ->when($status && $status !== 'all', function ($q) use ($status) {
                if ($status === 'published') {
                    $q->where(fn($w) => $w->where('status', 'published')->orWhere('mod_status', 'approved'));
                } elseif ($status === 'pending') {
                    $q->where(fn($w) => $w->where('status', 'pending')->orWhere('mod_status', 'pending'));
                } elseif ($status === 'rejected') {
                    $q->where(fn($w) => $w->where('status', 'rejected')->orWhere('mod_status', 'rejected'));
                } elseif ($status === 'draft') {
                    $q->where('status', 'draft');
                } elseif ($status === 'featured') {
                    $q->where('is_featured', true);
                }
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $cat = $request->input('category');
                $q->where(function ($sub) use ($cat) {
                    $sub->where('category_id', $cat)
                        ->orWhereHas('category', fn($c) => $c->where('slug', $cat)->orWhere('name', $cat));
                });
            })
            ->when($request->filled('is_featured'), function ($q) use ($request) {
                $q->where('is_featured', $request->boolean('is_featured'));
            })
            ->orderByRaw("CASE WHEN status = 'pending' OR mod_status = 'pending' THEN 0 ELSE 1 END")
            ->latest('id');

        $perPage = in_array((int)$request->input('per_page'), [10, 20, 50, 100], true) ? (int)$request->input('per_page') : 20;
        $posts = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total'     => \Modules\Blog\Models\BlogPost::count(),
            'published' => \Modules\Blog\Models\BlogPost::where(fn($w) => $w->where('status', 'published')->orWhere('mod_status', 'approved'))->count(),
            'pending'   => \Modules\Blog\Models\BlogPost::where(fn($w) => $w->where('status', 'pending')->orWhere('mod_status', 'pending'))->count(),
            'draft'     => \Modules\Blog\Models\BlogPost::where('status', 'draft')->count(),
            'rejected'  => \Modules\Blog\Models\BlogPost::where(fn($w) => $w->where('status', 'rejected')->orWhere('mod_status', 'rejected'))->count(),
            'featured'  => \Modules\Blog\Models\BlogPost::where('is_featured', true)->count(),
        ];

        $categories = \Modules\Blog\Models\BlogCategory::orderBy('name')->get();
        $blogSettings = \App\Support\SiteSetting::blogCustomizer();
        $blogOgBannerUrl = \App\Support\SiteSetting::blogOgBannerUrl();

        return view('admin.blog', compact('posts', 'stats', 'search', 'status', 'categories', 'blogSettings', 'blogOgBannerUrl', 'perPage'));
    }

    public function updateBlogSettings(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'hero_badge'        => 'nullable|string|max:255',
            'hero_title'        => 'nullable|string|max:255',
            'hero_subtitle'     => 'nullable|string|max:1000',
            'write_button_text' => 'nullable|string|max:100',
            'write_button_url'  => 'nullable|string|max:255',
            'font_family'       => 'nullable|string|max:255',
            'reading_font_size' => 'nullable|string|max:50',
            'line_height'       => 'nullable|string|max:50',
            'poetry_line_height'=> 'nullable|string|max:50',
            'poetry_align'      => 'nullable|string|in:left,center,justify',
            'paragraph_margin'  => 'nullable|string|max:50',
            'reading_bg'        => 'nullable|string|max:50',
            'show_reading_bar'  => 'nullable|string',
            'enable_share_bar'  => 'nullable|string',
            'show_author_box'   => 'nullable|string',
            'header_gradient'   => 'nullable|string|max:255',
            'blog_og_banner'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $settings = [
            'hero_badge'        => $validated['hero_badge'] ?? 'সাহিত্য, শিল্প-সংস্কৃতি, গবেষণা ও মুক্তচিন্তা',
            'hero_title'        => $validated['hero_title'] ?? 'আইডিয়াপত্র — সমকালীন সাহিত্য ও চিন্তা',
            'hero_subtitle'     => $validated['hero_subtitle'] ?? '',
            'write_button_text' => $validated['write_button_text'] ?? 'নিজের লেখা পোস্ট করুন',
            'write_button_url'  => $validated['write_button_url'] ?? '/blog/write',
            'font_family'       => $validated['font_family'] ?? "'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif",
            'reading_font_size' => $validated['reading_font_size'] ?? '1.08rem',
            'line_height'       => $validated['line_height'] ?? '1.6',
            'poetry_line_height'=> $validated['poetry_line_height'] ?? '1.45',
            'poetry_align'      => $validated['poetry_align'] ?? 'left',
            'paragraph_margin'  => $validated['paragraph_margin'] ?? '0.85rem',
            'reading_bg'        => $validated['reading_bg'] ?? '#ffffff',
            'show_reading_bar'  => $request->boolean('show_reading_bar') ? '1' : '0',
            'enable_share_bar'  => $request->boolean('enable_share_bar') ? '1' : '0',
            'show_author_box'   => $request->boolean('show_author_box') ? '1' : '0',
            'header_gradient'   => $validated['header_gradient'] ?? 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)',
        ];

        \App\Models\AdminDashboardSetting::updateOrCreate(
            ['key' => 'blog_customizer_settings'],
            [
                'value'      => $settings,
                'updated_by' => auth()->id(),
            ]
        );

        // Handle Banner Upload if supplied
        if ($request->boolean('remove_blog_og_banner')) {
            \App\Models\AdminDashboardSetting::where('key', 'blog_og_banner')->delete();
        } elseif ($request->hasFile('blog_og_banner') || $request->filled('blog_og_banner_cropped')) {
            $savedBanner = null;
            if ($request->filled('blog_og_banner_cropped') && preg_match('/^data:image\/(\w+);base64,/', $request->input('blog_og_banner_cropped'))) {
                $base64 = substr($request->input('blog_og_banner_cropped'), strpos($request->input('blog_og_banner_cropped'), ',') + 1);
                $binary = base64_decode($base64);
                $filename = 'crop_' . uniqid('', true) . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put('images/banners/' . $filename, $binary);
                $savedBanner = 'storage/images/banners/' . $filename;
            } elseif ($request->hasFile('blog_og_banner')) {
                $path = $request->file('blog_og_banner')->store('images/banners', 'public');
                $savedBanner = 'storage/' . $path;
            }

            if ($savedBanner) {
                \App\Models\AdminDashboardSetting::updateOrCreate(
                    ['key' => 'blog_og_banner'],
                    ['value' => $savedBanner, 'updated_by' => auth()->id()]
                );
            }
        }

        \App\Support\SiteSetting::clearCache();

        $this->accessService->log('blog_settings_update', 'ব্লগের ডিজাইন, হেডার ও ব্যানার কাস্টমাইজেশন হালনাগাদ করা হয়েছে');

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'ব্লগের ডিজাইন ও ব্যানার সেটিংস সফলভাবে সংরক্ষিত হয়েছে!',
                'settings' => $settings,
            ]);
        }

        return back()->with('success', 'ব্লগের ডিজাইন ও ব্যানার সেটিংস সফলভাবে সংরক্ষিত হয়েছে!');
    }

    public function togglePostStatus(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $post = \Modules\Blog\Models\BlogPost::findOrFail($id);
        $newStatus = $request->input('status', 'published');

        if (!in_array($newStatus, ['published', 'pending', 'draft', 'rejected'], true)) {
            $newStatus = 'published';
        }

        $post->status = $newStatus;
        if ($newStatus === 'published') {
            $post->mod_status = 'approved';
            $post->rejection_reason = null;
            if (!$post->published_at) {
                $post->published_at = now();
            }
            $message = "‘{$post->title}’ পোস্টটি সফলভাবে অনুমোদন করা হয়েছে এবং ব্লগে প্রকাশিত হয়েছে!";
        } elseif ($newStatus === 'rejected') {
            $post->mod_status = 'rejected';
            $post->rejection_reason = $request->input('rejection_reason') ?: 'অসম্পূর্ণ বা সংশোধন প্রয়োজন।';
            $message = "‘{$post->title}’ পোস্টটি বাতিল করা হয়েছে।";
        } elseif ($newStatus === 'pending') {
            $post->mod_status = 'pending';
            $message = "‘{$post->title}’ পোস্টটি পর্যালোচনার জন্য পেন্ডিং রাখা হয়েছে।";
        } else {
            $post->mod_status = 'pending';
            $message = "‘{$post->title}’ পোস্টটি খসড়া (Draft) হিসেবে সংরক্ষিত হয়েছে।";
        }

        $post->save();

        $this->accessService->log('blog_status_toggle', "ব্লগ পোস্ট '{$post->title}' এর স্ট্যাটাস '{$newStatus}' করা হয়েছে");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => $message,
                'status'     => $newStatus,
                'mod_status' => $post->mod_status,
                'slug'       => $post->slug,
                'show_url'   => route('blog.show', $post->slug),
            ]);
        }

        return back()->with('success', $message);
    }

    public function togglePostFeatured(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $post = \Modules\Blog\Models\BlogPost::findOrFail($id);
        $post->is_featured = !$post->is_featured;
        $post->save();

        $stateText = $post->is_featured ? 'নির্বাচিত (Featured)' : 'সাধারণ (Unfeatured)';
        $this->accessService->log('blog_featured_toggle', "ব্লগ পোস্ট '{$post->title}' {$stateText} করা হয়েছে");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'is_featured' => $post->is_featured,
                'message'     => "পোস্টটি সফলভাবে {$stateText} করা হয়েছে।",
            ]);
        }

        return back()->with('success', "পোস্টটি সফলভাবে {$stateText} করা হয়েছে।");
    }

    public function destroyPost($id): \Illuminate\Http\RedirectResponse
    {
        $post = \Modules\Blog\Models\BlogPost::findOrFail($id);
        $title = $post->title;
        $post->delete();

        $this->accessService->log('blog_post_delete', "ব্লগ পোস্ট '{$title}' মুছে ফেলা হয়েছে");

        return back()->with('success', "ব্লগ পোস্ট '{$title}' সফলভাবে মুছে ফেলা হয়েছে।");
    }

    public function bulkBlogAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $ids = $request->input('selected_ids', []);
        $action = $request->input('bulk_action');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'অনুগ্রহ করে কমপক্ষে একটি পোস্ট নির্বাচন করুন।');
        }

        if ($action === 'publish') {
            \Modules\Blog\Models\BlogPost::whereIn('id', $ids)->update([
                'status'       => 'published',
                'mod_status'   => 'approved',
                'published_at' => now(),
            ]);
            return back()->with('success', count($ids) . 'টি পোস্ট সফলভাবে প্রকাশ ও অনুমোদন করা হয়েছে।');
        }

        if ($action === 'draft') {
            \Modules\Blog\Models\BlogPost::whereIn('id', $ids)->update([
                'status' => 'draft',
            ]);
            return back()->with('success', count($ids) . 'টি পোস্ট ড্রাফটে নেওয়া হয়েছে।');
        }

        if ($action === 'delete') {
            \Modules\Blog\Models\BlogPost::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . 'টি পোস্ট মুছে ফেলা হয়েছে।');
        }

        return back()->with('error', 'সঠিক অ্যাকশন নির্বাচন করুন।');
    }

    public function bulkNormalizeBlogTypography(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $target = $request->input('target', 'all');
        $query = \Modules\Blog\Models\BlogPost::query();

        if ($target === 'published') {
            $query->where(fn($w) => $w->where('status', 'published')->orWhere('mod_status', 'approved'));
        }

        $posts = $query->get();
        $updatedCount = 0;

        foreach ($posts as $post) {
            $content = $post->content;
            if (!$content) continue;

            $orig = $content;
            // 1. Replace inflated poetry line-height and excessive margins
            $content = preg_replace('/line-height:\s*(2\.[0-9]+|3\.[0-9]+|1\.9[0-9]*);?/i', 'line-height: 1.45;', $content);
            $content = preg_replace('/margin-bottom:\s*(1\.[5-9]rem|2\.[0-9]+rem);?/i', 'margin-bottom: 0.85rem;', $content);
            // 2. Clean excessive multiple breaks
            $content = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $content);

            if ($content !== $orig) {
                $post->content = $content;
                $post->save();
                $updatedCount++;
            }
        }

        $this->accessService->log('blog_bulk_typography', "মোট {$updatedCount}টি ব্লগ পোস্টের অতিরিক্ত লাইন স্পেস ও স্তবক বিন্যাস সফলভাবে অপ্টিমাইজ করা হয়েছে");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "মোট {$updatedCount}টি লেখার অতিরিক্ত লাইন ও প্যারা স্পেস সফলভাবে মেরামত করা হয়েছে!",
                'count'   => $updatedCount,
            ]);
        }

        return back()->with('success', "মোট {$updatedCount}টি লেখার অতিরিক্ত লাইন ও প্যারা স্পেস সফলভাবে মেরামত করা হয়েছে!");
    }

    public function blogCategories(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $query = \Modules\Blog\Models\BlogCategory::query()
            ->withCount(['posts' => fn($q) => $q->where('status', 'published')])
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $query->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('name', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('description', 'like', $like);
                            });
                        }
                    });
                }
            })
            ->orderBy('name');

        $categories = $query->paginate(25)->withQueryString();

        $stats = [
            'total'  => \Modules\Blog\Models\BlogCategory::count(),
            'active' => \Modules\Blog\Models\BlogCategory::where('is_active', true)->count(),
        ];

        return view('admin.blog-categories', compact('categories', 'stats', 'search'));
    }

    public function webzines(Request $request): View
    {
        return view('admin.webzines', [
            'webzines' => $this->listing('webzines', $request, ['title', 'slug']),
        ]);
    }

    public function authors(Request $request): View
    {
        $search   = $request->string('search')->trim()->value();
        $status   = $request->input('is_active');
        $verified = $request->input('is_verified');
        $hasBooks = $request->input('has_books');
        $sort     = $request->string('sort')->trim()->value() ?: 'latest';
        $perPage  = in_array((int) $request->input('per_page'), [14, 21, 28, 35, 42, 70, 98, 100], true) ? (int) $request->input('per_page') : 28;

        $query = \Modules\Author\Models\Author::query()
            ->withCount('books')
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('name', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('email', 'like', $like)
                                  ->orWhere('phone', 'like', $like)
                                  ->orWhere('bio', 'like', $like);
                            });
                        }
                    });
                }
            })
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->when($verified !== null && $verified !== '', fn ($q) => $q->where('is_verified', (bool) $verified))
            ->when($hasBooks === '1', fn ($q) => $q->has('books'))
            ->when($hasBooks === '0', fn ($q) => $q->doesntHave('books'));

        match ($sort) {
            'oldest'     => $query->oldest('id'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'books_desc' => $query->orderByDesc('books_count'),
            'books_asc'  => $query->orderBy('books_count', 'asc'),
            default      => $query->latest('id'),
        };

        $authors = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total'       => \Modules\Author\Models\Author::count(),
            'active'      => \Modules\Author\Models\Author::where('is_active', true)->count(),
            'verified'    => \Modules\Author\Models\Author::where('is_verified', true)->count(),
            'with_books'  => \Modules\Author\Models\Author::has('books')->count(),
            'total_books' => Schema::hasTable('book_author') ? DB::table('book_author')->distinct('book_id')->count('book_id') : 0,
        ];

        return view('admin.authors', compact('authors', 'stats', 'search', 'status', 'verified', 'hasBooks', 'sort', 'perPage'));
    }

    public function quickStoreAuthor(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'bio'         => 'nullable|string|max:20000',
            'is_active'   => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'avatar_file' => 'nullable|image|max:4096',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar_file')) {
            $avatarPath = $request->file('avatar_file')->store('authors', 'public');
        }

        $author = \Modules\Author\Models\Author::findOrCreateUnified([
            'name'        => $validated['name'],
            'slug'        => $validated['slug'] ?? null,
            'phone'       => $validated['phone'] ?? null,
            'email'       => $validated['email'] ?? null,
            'website'     => $validated['website'] ?? null,
            'bio'         => $validated['bio'] ?? null,
            'avatar'      => $avatarPath,
            'is_active'   => $request->boolean('is_active', true),
            'is_verified' => $request->boolean('is_verified', false),
        ]);

        $this->accessService->log('author_quick_create', "লেখক '{$author->name}' ডিরেক্টরিতে সিঙ্ক/যুক্ত করা হয়েছে");

        return response()->json([
            'success' => true,
            'message' => "লেখক '{$author->name}' সফলভাবে সংরক্ষিত ও সিঙ্ক হয়েছে!",
            'author'  => $author,
        ]);
    }

    public function quickUpdateAuthor(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $author = \Modules\Author\Models\Author::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:authors,slug,' . $author->id,
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'bio'         => 'nullable|string|max:20000',
            'is_active'   => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'avatar_file' => 'nullable|image|max:4096',
        ]);

        $updates = [
            'name'    => $validated['name'],
            'phone'   => $validated['phone'] ?? null,
            'email'   => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'bio'     => $validated['bio'] ?? null,
        ];

        if (!empty($validated['slug'])) {
            $updates['slug'] = \Illuminate\Support\Str::slug($validated['slug']);
        }
        if ($request->has('is_active')) {
            $updates['is_active'] = $request->boolean('is_active');
        }
        if ($request->has('is_verified')) {
            $updates['is_verified'] = $request->boolean('is_verified');
        }
        if ($request->hasFile('avatar_file')) {
            $updates['avatar'] = $request->file('avatar_file')->store('authors', 'public');
        }

        $author->update($updates);

        // Sync linked user account avatar if exists
        if (!empty($updates['avatar'])) {
            \App\Models\User::where(function ($q) use ($author) {
                if ($author->email) $q->orWhere('email', $author->email);
                if ($author->phone) $q->orWhere('phone', $author->phone);
                if ($author->name)  $q->orWhere('name', $author->name);
            })->update(['avatar' => $updates['avatar']]);
        }

        $this->accessService->log('author_quick_update', "লেখক '{$author->name}' এর তথ্য আপডেট করা হয়েছে");

        return response()->json([
            'success' => true,
            'message' => "লেখকের তথ্য সফলভাবে সংরক্ষিত হয়েছে!",
            'author'  => $author,
        ]);
    }

    public function toggleAuthorStatus($id): \Illuminate\Http\JsonResponse
    {
        $author = \Modules\Author\Models\Author::findOrFail($id);
        $author->is_active = !$author->is_active;
        $author->save();

        $statusText = $author->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        $this->accessService->log('author_status_toggle', "লেখক '{$author->name}' {$statusText} করা হয়েছে");

        return response()->json([
            'success'   => true,
            'is_active' => $author->is_active,
            'message'   => "লেখক '{$author->name}' এখন {$statusText}",
        ]);
    }

    public function toggleAuthorVerified($id): \Illuminate\Http\JsonResponse
    {
        $author = \Modules\Author\Models\Author::findOrFail($id);
        $author->is_verified = !$author->is_verified;
        $author->save();

        $vText = $author->is_verified ? 'যাচাইকৃত (Verified)' : 'সাধারণ (Unverified)';
        $this->accessService->log('author_verified_toggle', "লেখক '{$author->name}' কে {$vText} হিসেবে চিহ্নিত করা হয়েছে");

        return response()->json([
            'success'     => true,
            'is_verified' => $author->is_verified,
            'message'     => "লেখক '{$author->name}' এখন {$vText}",
        ]);
    }

    public function resetAuthorPassword(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $author = \Modules\Author\Models\Author::findOrFail($id);

        $validated = $request->validate([
            'password'      => 'nullable|string|min:6|max:100',
            'auto_generate' => 'nullable|boolean',
            'notify_author' => 'nullable|boolean',
        ]);

        // Determine or generate new friendly password
        $newPassword = !empty($validated['password']) ? trim($validated['password']) : ('Idea@' . rand(1000, 9999));

        // Find or create linked User account for Author
        $user = null;
        if ($author->user_id) {
            $user = \App\Models\User::find($author->user_id);
        }
        if (!$user && !empty($author->email)) {
            $user = \App\Models\User::where('email', $author->email)->first();
        }
        if (!$user && !empty($author->phone)) {
            $user = \App\Models\User::where('phone', $author->phone)->first();
        }

        if (!$user) {
            $email = $author->email ?: (($author->slug ?: 'author_' . $author->id) . '@ideaabd.com');
            $user = \App\Models\User::create([
                'name'     => $author->name,
                'email'    => $email,
                'phone'    => $author->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
                'role'     => 'author',
            ]);
            $author->update(['user_id' => $user->id, 'email' => $user->email]);
        } else {
            $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
            if ($user->role !== 'admin' && $user->role !== 'author') {
                $user->role = 'author';
            }
            $user->save();
            if ($author->user_id !== $user->id) {
                $author->update(['user_id' => $user->id]);
            }
        }

        $loginUrl = route('login');
        $loginUsername = $user->email ?: ($user->phone ?: $user->name);

        $whatsappMessage = "শ্রদ্ধেয় লেখক {$author->name},\nআইডিয়া প্রকাশনে আপনার লেখক পোর্টালের নতুন লগইন তথ্য:\n\nলগইন আইডি: {$loginUsername}\nনতুন পাসওয়ার্ড: {$newPassword}\nলগইন লিংক: {$loginUrl}\n\nধন্যবাদ,\nআইডিয়া প্রকাশন";

        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$user->phone);
        $whatsappUrl = !empty($cleanPhone) ? ('https://wa.me/' . (str_starts_with($cleanPhone, '88') ? $cleanPhone : ('88' . ltrim($cleanPhone, '0'))) . '?text=' . urlencode($whatsappMessage)) : null;

        $this->accessService->log('author_password_reset', "লেখক '{$author->name}' (User ID: {$user->id})-এর পাসওয়ার্ড সফলভাবে রিসেট করা হয়েছে।");

        return response()->json([
            'success'          => true,
            'message'          => "লেখক '{$author->name}'-এর পাসওয়ার্ড সফলভাবে রিসেট করা হয়েছে!",
            'author_name'      => $author->name,
            'login_identity'   => $loginUsername,
            'new_password'     => $newPassword,
            'login_url'        => $loginUrl,
            'whatsapp_url'     => $whatsappUrl,
            'whatsapp_message' => $whatsappMessage,
        ]);
    }

    public function authorDetails($id): \Illuminate\Http\JsonResponse
    {
        $author = \Modules\Author\Models\Author::with(['books' => function ($q) {
            $q->select('books.id', 'books.title', 'books.price', 'books.cover_image')->orderByDesc('books.id')->take(10);
        }])->withCount('books')->findOrFail($id);

        return response()->json([
            'success' => true,
            'author'  => $author,
        ]);
    }

    public function publishers(Request $request): View
    {
        $search   = $request->string('search')->trim()->value();
        $status   = $request->input('is_active');
        $hasDue   = $request->input('has_due');
        $sort     = $request->string('sort')->trim()->value() ?: 'latest';
        $perPage  = in_array((int) $request->input('per_page'), [10, 20, 50, 100], true) ? (int) $request->input('per_page') : 20;

        $query = \Modules\Publisher\Models\Publisher::query()
            ->withCount('books')
            ->withSum('books as total_catalog_price', 'price')
            ->withSum('purchases as total_purchase_sum', 'grand_total')
            ->withSum('purchases as total_due_sum', 'due_amount')
            ->withSum('purchases as total_paid_sum', 'paid_amount')
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('name', 'like', $like)
                                  ->orWhere('slug', 'like', $like)
                                  ->orWhere('email', 'like', $like)
                                  ->orWhere('phone', 'like', $like)
                                  ->orWhere('address', 'like', $like)
                                  ->orWhere('description', 'like', $like);
                            });
                        }
                    });
                }
            })
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->when($hasDue, function ($q) {
                $q->whereHas('purchases', fn($pq) => $pq->where('due_amount', '>', 0));
            });

        match ($sort) {
            'oldest'        => $query->oldest('id'),
            'name_asc'      => $query->orderBy('name', 'asc'),
            'name_desc'     => $query->orderBy('name', 'desc'),
            'books_desc'    => $query->orderByDesc('books_count'),
            'purchase_desc' => $query->orderByDesc('total_purchase_sum'),
            'due_desc'      => $query->orderByDesc('total_due_sum'),
            default         => $query->latest('id'),
        };

        $publishers = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total'               => \Modules\Publisher\Models\Publisher::count(),
            'active'              => \Modules\Publisher\Models\Publisher::where('is_active', true)->count(),
            'total_books'         => \Modules\Book\Models\Book::count(),
            'total_catalog_sum'   => (float) \Modules\Book\Models\Book::selectRaw('COALESCE(SUM(COALESCE(NULLIF(price, 0), hardcover_price, 0)), 0) as total')->value('total'),
            'total_purchase_sum'  => (float) \App\Models\PublisherPurchase::sum('grand_total'),
            'total_due_sum'       => (float) \App\Models\PublisherPurchase::sum('due_amount'),
        ];

        return view('admin.publishers', compact('publishers', 'stats', 'search', 'status', 'hasDue', 'sort', 'perPage'));
    }

    public function quickStorePublisher(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:publishers,slug',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'address'     => 'nullable|string|max:500',
            'website'     => 'nullable|url|max:255',
            'description' => 'nullable|string|max:2000',
            'is_active'   => 'nullable|boolean',
            'logo_file'   => 'nullable|image|max:3072',
        ]);

        $slug = !empty($validated['slug']) 
            ? \Illuminate\Support\Str::slug($validated['slug'])
            : \Illuminate\Support\Str::slug($validated['name']);
        
        if (\Modules\Publisher\Models\Publisher::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        $logoPath = null;
        if ($request->hasFile('logo_file')) {
            $logoPath = $request->file('logo_file')->store('publishers/logos', 'public');
        }

        $publisher = \Modules\Publisher\Models\Publisher::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'phone'       => $validated['phone'] ?? null,
            'email'       => $validated['email'] ?? null,
            'address'     => $validated['address'] ?? null,
            'website'     => $validated['website'] ?? null,
            'description' => $validated['description'] ?? null,
            'logo'        => $logoPath,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $this->accessService->log('publisher_quick_create', "নতুন প্রকাশক '{$publisher->name}' যুক্ত করা হয়েছে");

        return response()->json([
            'success'   => true,
            'message'   => "নতুন প্রকাশক '{$publisher->name}' সফলভাবে তৈরি হয়েছে!",
            'publisher' => $publisher,
        ]);
    }

    public function quickUpdatePublisher(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $publisher = \Modules\Publisher\Models\Publisher::findOrFail($id);

         $isIdea = (str_contains($publisher->name, 'আইডিয়া') || $publisher->slug === 'ideaprokashon' || $publisher->id === 2);

        $booksQuery = \Modules\Book\Models\Book::query()
            ->with(['authorLink', 'authors', 'category'])
            ->where(function ($q) use ($publisher, $isIdea) {
                $q->where('publisher_id', $publisher->id);
                if ($isIdea) {
                    $q->orWhereNull('publisher_id');
                }
            })
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('title', 'like', $like)
                                  ->orWhere('author_name', 'like', $like)
                                  ->orWhere('isbn', 'like', $like)
                                  ->orWhere('sku', 'like', $like)
                                  ->orWhere('edition', 'like', $like);
                            });
                        }
                    });
                }
            })
            ->when($category, fn($q) => $q->where('category_id', $category))
            ->when($stock, function ($q, $s) {
                match ($s) {
                    'in_stock'  => $q->where('stock_quantity', '>', 5),
                    'low'       => $q->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5),
                    'out'       => $q->where('stock_quantity', '<=', 0),
                    'pre_order' => $q->where('stock_status', 'pre_order'),
                    default     => null,
                };
            });

        match ($sort) {
            'oldest'      => $booksQuery->oldest('id'),
            'title_asc'   => $booksQuery->orderBy('title', 'asc'),
            'title_desc'  => $booksQuery->orderBy('title', 'desc'),
            'price_low'   => $booksQuery->orderBy('price', 'asc'),
            'price_high'  => $booksQuery->orderBy('price', 'desc'),
            'stock_low'   => $booksQuery->orderBy('stock_quantity', 'asc'),
            'stock_high'  => $booksQuery->orderBy('stock_quantity', 'desc'),
            'sales_high'  => $booksQuery->orderByDesc('sales_count'),
            default       => $booksQuery->latest('id'),
        };

        $books = $booksQuery->paginate($perPage)->withQueryString();

        // Invoices / Purchases for this publisher
        $purchases = \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)
            ->with(['items', 'payments'])
            ->latest()
            ->paginate(15, ['*'], 'purchases_page')
            ->withQueryString();

        // Payments records for this publisher
        $payments = \App\Models\PublisherPayment::where('publisher_id', $publisher->id)
            ->with('purchase')
            ->latest()
            ->paginate(15, ['*'], 'payments_page')
            ->withQueryString();

        // Sales & Top Selling Books
        $topBooks = \Modules\Book\Models\Book::where(function ($q) use ($publisher, $isIdea) {
                $q->where('publisher_id', $publisher->id);
                if ($isIdea) {
                    $q->orWhereNull('publisher_id');
                }
            })
            ->where('sales_count', '>', 0)
            ->orderByDesc('sales_count')
            ->take(10)
            ->get();

        // Publisher-specific stats
        $stats = [
            'total_books'      => \Modules\Book\Models\Book::where(fn($q) => $isIdea ? $q->where('publisher_id', $publisher->id)->orWhereNull('publisher_id') : $q->where('publisher_id', $publisher->id))->count(),
            'in_stock'         => \Modules\Book\Models\Book::where(fn($q) => $isIdea ? $q->where('publisher_id', $publisher->id)->orWhereNull('publisher_id') : $q->where('publisher_id', $publisher->id))->where('stock_quantity', '>', 5)->count(),
            'low_stock'        => \Modules\Book\Models\Book::where(fn($q) => $isIdea ? $q->where('publisher_id', $publisher->id)->orWhereNull('publisher_id') : $q->where('publisher_id', $publisher->id))->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count(),
            'out_stock'        => \Modules\Book\Models\Book::where(fn($q) => $isIdea ? $q->where('publisher_id', $publisher->id)->orWhereNull('publisher_id') : $q->where('publisher_id', $publisher->id))->where('stock_quantity', '<=', 0)->count(),
            'total_po'         => \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->count(),
            'total_po_sum'     => (float) \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->sum('grand_total'),
            'total_po_paid'    => (float) \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->sum('paid_amount'),
            'total_po_due'     => (float) \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->sum('due_amount'),
            'total_sold_copies'=> (int) \Modules\Book\Models\Book::where(fn($q) => $isIdea ? $q->where('publisher_id', $publisher->id)->orWhereNull('publisher_id') : $q->where('publisher_id', $publisher->id))->sum('sales_count'),
            'total_payments'   => \App\Models\PublisherPayment::where('publisher_id', $publisher->id)->count(),
        ];is->accessService->log('publisher_quick_payment', "প্রকাশক '{$publisher->name}' কে ৳{$validated['amount']} পরিশোধ রেকর্ড করা হয়েছে (#{$paymentNo})");

        return response()->json([
            'success'    => true,
            'message'    => "৳" . number_format($validated['amount'], 2) . " পরিশোধ সফলভাবে রেকর্ড করা হয়েছে (ভাউচার #{$paymentNo})!",
            'payment_no' => $paymentNo,
        ]);
    }

    public function publisherShow(Request $request, $id): View
    {
        $publisher = \Modules\Publisher\Models\Publisher::withCount('books')->findOrFail($id);

        $tab = $request->string('tab')->trim()->value() ?: 'books';
        $search = $request->string('search')->trim()->value();
        $stock = $request->string('stock')->trim()->value();
        $category = $request->input('category_id');
        $sort = $request->string('sort')->trim()->value() ?: 'latest';
        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 200], true) ? (int) $request->input('per_page') : 20;

        $booksQuery = \Modules\Book\Models\Book::query()
            ->with(['authorLink', 'authors', 'category'])
            ->where('publisher_id', $publisher->id)
            ->when($search, function ($q, $term) {
                $searchData = $this->parseSearchKeywords($term);
                $tokens = $searchData['tokens'];
                if (!empty($tokens)) {
                    $q->where(function ($master) use ($tokens) {
                        foreach ($tokens as $token) {
                            $like = '%' . $token . '%';
                            $master->where(function ($w) use ($like) {
                                $w->where('title', 'like', $like)
                                  ->orWhere('author_name', 'like', $like)
                                  ->orWhere('isbn', 'like', $like)
                                  ->orWhere('sku', 'like', $like)
                                  ->orWhere('edition', 'like', $like);
                            });
                        }
                    });
                }
            })
            ->when($category, fn($q) => $q->where('category_id', $category))
            ->when($stock, function ($q, $s) {
                match ($s) {
                    'in_stock'  => $q->where('stock_quantity', '>', 5),
                    'low'       => $q->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5),
                    'out'       => $q->where('stock_quantity', '<=', 0),
                    'pre_order' => $q->where('stock_status', 'pre_order'),
                    default     => null,
                };
            });

        match ($sort) {
            'oldest'      => $booksQuery->oldest('id'),
            'title_asc'   => $booksQuery->orderBy('title', 'asc'),
            'title_desc'  => $booksQuery->orderBy('title', 'desc'),
            'price_low'   => $booksQuery->orderBy('price', 'asc'),
            'price_high'  => $booksQuery->orderBy('price', 'desc'),
            'stock_low'   => $booksQuery->orderBy('stock_quantity', 'asc'),
            'stock_high'  => $booksQuery->orderBy('stock_quantity', 'desc'),
            'sales_high'  => $booksQuery->orderByDesc('sales_count'),
            default       => $booksQuery->latest('id'),
        };

        $books = $booksQuery->paginate($perPage)->withQueryString();

        // Invoices / Purchases for this publisher
        $purchases = \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)
            ->with(['items', 'payments'])
            ->latest()
            ->paginate(15, ['*'], 'purchases_page')
            ->withQueryString();

        // Payments records for this publisher
        $payments = \App\Models\PublisherPayment::where('publisher_id', $publisher->id)
            ->with('purchase')
            ->latest()
            ->paginate(15, ['*'], 'payments_page')
            ->withQueryString();

        // Sales & Top Selling Books
        $topBooks = \Modules\Book\Models\Book::where('publisher_id', $publisher->id)
            ->where('sales_count', '>', 0)
            ->orderByDesc('sales_count')
            ->take(10)
            ->get();

        // Publisher-specific stats
        $stats = [
            'total_books'      => \Modules\Book\Models\Book::where('publisher_id', $publisher->id)->count(),
            'in_stock'         => \Modules\Book\Models\Book::where('publisher_id', $publisher->id)->where('stock_quantity', '>', 5)->count(),
            'low_stock'        => \Modules\Book\Models\Book::where('publisher_id', $publisher->id)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count(),
            'out_stock'        => \Modules\Book\Models\Book::where('publisher_id', $publisher->id)->where('stock_quantity', '<=', 0)->count(),
            'total_po'         => \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->count(),
            'total_po_sum'     => (float) \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->sum('grand_total'),
            'total_po_paid'    => (float) \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->sum('paid_amount'),
            'total_po_due'     => (float) \App\Models\PublisherPurchase::where('publisher_id', $publisher->id)->sum('due_amount'),
            'total_sold_copies'=> (int) \Modules\Book\Models\Book::where('publisher_id', $publisher->id)->sum('sales_count'),
            'total_payments'   => \App\Models\PublisherPayment::where('publisher_id', $publisher->id)->count(),
        ];

        $categories = \Modules\Book\Models\Category::orderBy('name')->pluck('name', 'id')->all();

        return view('admin.publishers.show', compact('publisher', 'books', 'purchases', 'payments', 'topBooks', 'stats', 'categories', 'tab', 'search', 'stock', 'category', 'sort', 'perPage'));
    }

    public function sendPublisherPurchaseOrderEmail(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $publisher = \Modules\Publisher\Models\Publisher::findOrFail($id);

        $validated = $request->validate([
            'recipient_email'  => 'required|email',
            'subject'          => 'nullable|string|max:255',
            'delivery_date'    => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
            'create_invoice'   => 'nullable|boolean',
            'items'            => 'required|array|min:1',
            'items.*.book_id'  => 'nullable|integer',
            'items.*.title'    => 'required|string|max:255',
            'items.*.author'   => 'nullable|string|max:255',
            'items.*.edition'  => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.commission_percent'  => 'nullable|numeric|min:0|max:100',
            'items.*.cost_price'          => 'required|numeric|min:0',
            'items.*.total_price'         => 'required|numeric|min:0',
        ]);

        $poDate = date('Ymd');
        $randomSeq = strtoupper(\Illuminate\Support\Str::random(4));
        $poNumber = 'PO-' . $poDate . '-' . $randomSeq;

        $orderData = [
            'po_number'     => $poNumber,
            'order_date'    => date('d M Y, h:i A'),
            'delivery_date' => $validated['delivery_date'] ?? null,
            'subject'       => $validated['subject'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'items'         => $validated['items'],
        ];

        // Send Email using PublisherPurchaseOrderMail
        try {
            \Illuminate\Support\Facades\Mail::to($validated['recipient_email'])
                ->send(new \App\Mail\PublisherPurchaseOrderMail($publisher, $orderData, $validated['notes'] ?? null));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Purchase Order Email Failed: " . $e->getMessage());
        }

        // Optionally record in PublisherPurchase table
        $createdPurchase = null;
        if ($request->boolean('create_invoice')) {
            try {
                $subtotal = 0;
                $grandTotal = 0;
                foreach ($validated['items'] as $item) {
                    $qty = (int) $item['quantity'];
                    $mrp = (float) $item['unit_price'];
                    $cost = (float) $item['cost_price'];
                    $subtotal += ($mrp * $qty);
                    $grandTotal += ($cost * $qty);
                }

                $discountAmount = max(0, $subtotal - $grandTotal);

                $purchase = \App\Models\PublisherPurchase::create([
                    'purchase_no'       => $poNumber,
                    'publisher_id'      => $publisher->id,
                    'publisher_name'    => $publisher->name,
                    'publisher_phone'   => $publisher->phone,
                    'publisher_address' => $publisher->address,
                    'purchase_date'     => now()->toDateString(),
                    'payment_type'      => 'credit',
                    'payment_status'    => 'due',
                    'subtotal'          => $subtotal,
                    'discount_amount'   => $discountAmount,
                    'tax_amount'        => 0,
                    'shipping_cost'     => 0,
                    'grand_total'       => $grandTotal,
                    'paid_amount'       => 0,
                    'due_amount'        => $grandTotal,
                    'notes'             => ($validated['notes'] ?? '') . " [ক্রয় আদেশ ইমেইল মারফত প্রেরিত]",
                    'created_by'        => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $qty = (int) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $costPrice = (float) $item['cost_price'];
                    $comm = (float) ($item['commission_percent'] ?? 0);
                    $lineTotal = (float) ($item['total_price'] ?? ($costPrice * $qty));

                    \App\Models\PublisherPurchaseItem::create([
                        'publisher_purchase_id' => $purchase->id,
                        'book_id'               => $item['book_id'] ?? null,
                        'book_title'            => $item['title'],
                        'book_edition'          => $item['edition'] ?? null,
                        'author_name'           => $item['author'] ?? null,
                        'unit_price'            => $unitPrice,
                        'discount_percent'      => $comm,
                        'purchase_rate'         => $costPrice,
                        'quantity'              => $qty,
                        'total_amount'          => $lineTotal,
                    ]);
                }

                $createdPurchase = $purchase;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Failed to auto-create PublisherPurchase: " . $e->getMessage());
            }
        }

        $this->accessService->log('publisher_purchase_order', "প্রকাশক '{$publisher->name}' এর কাছে ক্রয় আদেশ #{$poNumber} ইমেইল করা হয়েছে");

        return response()->json([
            'success'     => true,
            'message'     => "ক্রয় আদেশ #{$poNumber} সফলভাবে {$validated['recipient_email']} ঠিকানায় ইমেইল করা হয়েছে!",
            'po_number'   => $poNumber,
            'purchase_id' => $createdPurchase?->id ?? null,
        ]);
    }

    public function quickUpdateBook(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'book_id'                  => 'required|integer|exists:books,id',
            'title'                    => 'nullable|string|max:255',
            'cover_type'               => 'nullable|string|in:paperback,hardcover,both',
            'price'                    => 'nullable|numeric|min:0',
            'discount_price'           => 'nullable|numeric|min:0',
            'cost_price'               => 'nullable|numeric|min:0',
            'hardcover_price'          => 'nullable|numeric|min:0',
            'hardcover_discount_price' => 'nullable|numeric|min:0',
            'edition'                  => 'nullable|string|max:100',
            'stock_quantity'           => 'nullable|integer|min:0',
            'stock_status'             => 'nullable|string|in:in_stock,low,out,pre_order',
            'is_active'                => 'nullable|boolean',
            'mod_status'               => 'nullable|string|in:pending,approved,rejected',
            'cover_image_file'         => 'nullable|image|max:5120',
        ]);

        $book = \Modules\Book\Models\Book::findOrFail($validated['book_id']);

        $updates = [];

        if ($request->has('title') && $validated['title'] !== null) {
            $updates['title'] = $validated['title'];
        }
        if ($request->has('cover_type') && $validated['cover_type'] !== null) {
            $updates['cover_type'] = $validated['cover_type'];
        }
        if ($request->has('edition')) {
            $updates['edition'] = $validated['edition'];
        }
        if ($request->has('price')) {
            $updates['price'] = $validated['price'] !== null ? (float) $validated['price'] : 0;
        }
        if ($request->has('discount_price')) {
            $updates['discount_price'] = $validated['discount_price'] !== null && $validated['discount_price'] !== '' ? (float) $validated['discount_price'] : null;
        }
        if ($request->has('cost_price')) {
            $updates['cost_price'] = $validated['cost_price'] !== null && $validated['cost_price'] !== '' ? (float) $validated['cost_price'] : null;
        }
        if ($request->has('hardcover_price')) {
            $updates['hardcover_price'] = $validated['hardcover_price'] !== null && $validated['hardcover_price'] !== '' ? (float) $validated['hardcover_price'] : null;
        }
        if ($request->has('hardcover_discount_price')) {
            $updates['hardcover_discount_price'] = $validated['hardcover_discount_price'] !== null && $validated['hardcover_discount_price'] !== '' ? (float) $validated['hardcover_discount_price'] : null;
        }
        if ($request->has('stock_quantity') && $validated['stock_quantity'] !== null) {
            $qty = (int) $validated['stock_quantity'];
            $updates['stock_quantity'] = $qty;
            if (!$request->filled('stock_status')) {
                $updates['stock_status'] = $qty <= 0 ? 'out' : ($qty <= 5 ? 'low' : 'in_stock');
            }
        }
        if ($request->filled('stock_status')) {
            $updates['stock_status'] = $validated['stock_status'];
        }
        if ($request->filled('mod_status')) {
            $updates['mod_status'] = $validated['mod_status'];
            if ($validated['mod_status'] === 'approved') {
                $updates['is_active'] = true;
                $updates['reviewed_by'] = auth()->id();
                $updates['reviewed_at'] = now();
                $updates['rejection_reason'] = null;
            } elseif ($validated['mod_status'] === 'rejected') {
                $updates['is_active'] = false;
                $updates['reviewed_by'] = auth()->id();
                $updates['reviewed_at'] = now();
            }
        }
        if ($request->has('is_active') && !$request->filled('mod_status')) {
            $updates['is_active'] = $request->boolean('is_active');
        }

        // Handle direct cover image file upload
        if ($request->hasFile('cover_image_file')) {
            $path = $request->file('cover_image_file')->store('books/covers', 'public');
            $updates['cover_image'] = $path;
        }

        $book->update($updates);

        $this->accessService->log('book_quick_update', "বই '{$book->title}' (ID: {$book->id}) শর্টকাট তথ্য আপডেট করা হয়েছে");

        // Calculate commissions for response
        $price = (float) $book->price;
        $discountPrice = (float) ($book->discount_price ?? 0);
        $costPrice = (float) ($book->cost_price ?? 0);

        $saleCommissionPercent = ($price > 0 && $discountPrice > 0 && $discountPrice < $price)
            ? round((($price - $discountPrice) / $price) * 100, 1)
            : 0;

        $buyCommissionPercent = ($price > 0 && $costPrice > 0 && $costPrice < $price)
            ? round((($price - $costPrice) / $price) * 100, 1)
            : 0;

        $coverUrl = $book->cover_image 
            ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . ltrim($book->cover_image, '/')))
            : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';

        return response()->json([
            'success' => true,
            'message' => "বইয়ের তথ্য সফলভাবে আপডেট হয়েছে!",
            'book' => [
                'id'                      => $book->id,
                'title'                   => $book->title,
                'edition'                 => $book->edition,
                'price'                   => $price,
                'discount_price'          => $discountPrice,
                'cost_price'              => $costPrice,
                'hardcover_price'         => (float) $book->hardcover_price,
                'hardcover_discount_price'=> (float) $book->hardcover_discount_price,
                'sale_commission_percent' => $saleCommissionPercent,
                'buy_commission_percent'  => $buyCommissionPercent,
                'stock_quantity'          => (int) $book->stock_quantity,
                'stock_status'            => $book->stock_status,
                'is_active'               => (bool) $book->is_active,
                'cover_url'               => $coverUrl,
            ]
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
        $liveThreshold = now()->subMinutes(5);

        $stats = [
            'live_now'        => \App\Models\VisitorLog::where('visited_at', '>=', $liveThreshold)->distinct('ip_address')->count('ip_address'),
            'today_views'     => \App\Models\VisitorLog::where('visited_at', '>=', $today)->count(),
            'today_uniques'   => \App\Models\VisitorLog::where('visited_at', '>=', $today)->distinct('ip_address')->count('ip_address'),
            'week_views'      => \App\Models\VisitorLog::where('visited_at', '>=', $thisWeek)->count(),
            'month_views'     => \App\Models\VisitorLog::where('visited_at', '>=', $thisMonth)->count(),
            'total_views'     => \App\Models\VisitorLog::count(),
            'total_uniques'   => \App\Models\VisitorLog::distinct('ip_address')->count('ip_address'),
        ];

        // 14-Day Traffic Trend Data for Interactive Chart
        $trendDays = collect(range(13, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            $views = \App\Models\VisitorLog::whereDate('visited_at', $date)->count();
            $uniques = \App\Models\VisitorLog::whereDate('visited_at', $date)->distinct('ip_address')->count('ip_address');
            return [
                'date'    => now()->subDays($daysAgo)->format('d M'),
                'views'   => $views,
                'uniques' => $uniques,
            ];
        });

        // Geographic Country Distribution (Worldwide)
        $countryRecords = \App\Models\VisitorLog::select(
                DB::raw("COALESCE(country, 'Bangladesh') as country_name"),
                DB::raw("COALESCE(country_code, 'BD') as c_code"),
                DB::raw("count(*) as total")
            )
            ->groupBy('country_name', 'c_code')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $totalGeo = max(1, $countryRecords->sum('total'));
        $countries = $countryRecords->map(function ($item) use ($totalGeo) {
            $code = strtoupper((string) $item->c_code);
            $flag = '🌐';
            if (strlen($code) === 2) {
                $flag = '';
                foreach (str_split($code) as $c) {
                    $flag .= mb_chr(ord($c) + 127397, 'UTF-8');
                }
            }
            return [
                'country' => $item->country_name,
                'code'    => $code,
                'flag'    => $flag,
                'total'   => $item->total,
                'percent' => round(($item->total / $totalGeo) * 100, 1),
            ];
        });

        // Acquisition & Traffic Channels
        $channels = \App\Models\VisitorLog::select(
                DB::raw("COALESCE(traffic_source, 'Direct / Organic') as channel_name"),
                DB::raw("count(*) as total")
            )
            ->groupBy('channel_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Device Type Breakdown
        $devices = \App\Models\VisitorLog::select('device', DB::raw('count(*) as total'))
            ->groupBy('device')
            ->pluck('total', 'device')
            ->toArray();

        // Top Device Models / Hardware Brands (e.g. iPhone, Samsung, Xiaomi, Windows PC, Mac)
        $deviceModels = \App\Models\VisitorLog::whereNotNull('device_name')
            ->select('device_name', DB::raw('count(*) as total'))
            ->groupBy('device_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Browser Breakdown
        $browsers = \App\Models\VisitorLog::select('browser', DB::raw('count(*) as total'))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Operating System Breakdown
        $osList = \App\Models\VisitorLog::select('os', DB::raw('count(*) as total'))
            ->groupBy('os')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Top Visited Pages (All)
        $topPages = \App\Models\VisitorLog::select('url', 'page_title', DB::raw('count(*) as views'))
            ->groupBy('url', 'page_title')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Top Visited Books
        $topBooks = \App\Models\VisitorLog::where('url', 'like', '%/books/%')
            ->select('url', 'page_title', DB::raw('count(*) as views'))
            ->groupBy('url', 'page_title')
            ->orderByDesc('views')
            ->limit(8)
            ->get();

        // Filtered Real-Time Logs Stream
        $logs = \App\Models\VisitorLog::with('user')
            ->when($request->filled('device'), fn($q) => $q->where('device', $request->string('device')))
            ->when($request->filled('country_code'), fn($q) => $q->where('country_code', $request->string('country_code')))
            ->when($request->filled('traffic_source'), fn($q) => $q->where('traffic_source', 'like', '%' . $request->string('traffic_source') . '%'))
            ->when($request->filled('search'), function($q) use ($request) {
                $term = '%' . $request->string('search')->trim() . '%';
                $q->where(fn($w) => $w->where('url', 'like', $term)
                    ->orWhere('ip_address', 'like', $term)
                    ->orWhere('page_title', 'like', $term)
                    ->orWhere('country', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('device_name', 'like', $term)
                    ->orWhere('traffic_source', 'like', $term)
                    ->orWhere('browser', 'like', $term));
            })
            ->latest('visited_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.analytics', [
            'stats'        => $stats,
            'trendDays'    => $trendDays,
            'countries'    => $countries,
            'channels'     => $channels,
            'devices'      => $devices,
            'deviceModels' => $deviceModels,
            'browsers'     => $browsers,
            'osList'       => $osList,
            'topPages'     => $topPages,
            'topBooks'     => $topBooks,
            'logs'         => $logs,
        ]);
    }

    // ─── Customer Directory & Bulk Broadcast ────────────────────────────

    public function customers(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $search = $request->string('search')->trim()->value();
        $district = $request->string('district')->trim()->value();
        $filter = $request->string('filter', 'all')->trim()->value();

        $query = User::query()
            ->whereIn('role', ['buyer', 'customer'])
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_amount')
            ->with(['orders' => fn($q) => $q->latest()->limit(5)->select('id', 'user_id', 'order_number', 'total_amount', 'status', 'payment_status', 'created_at')])
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
            ->when($filter === 'with_orders', fn($q) => $q->has('orders'))
            ->when($filter === 'zero_orders', fn($q) => $q->doesntHave('orders'))
            ->when($filter === 'high_value', fn($q) => $q->has('orders')->having('total_spent', '>=', 2000))
            ->latest();

        // CSV Export Support
        if ($request->query('export') === 'csv') {
            $exportCustomers = (clone $query)->get();
            return response()->streamDownload(function() use ($exportCustomers) {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['ID', 'Customer Name', 'Phone', 'Email', 'District', 'Total Orders', 'Total Spent (BDT)', 'Date Joined']);
                foreach ($exportCustomers as $c) {
                    fputcsv($handle, [
                        $c->id,
                        $c->name,
                        $c->phone ?: 'N/A',
                        $c->email ?: 'N/A',
                        $c->district ?: 'N/A',
                        $c->orders_count,
                        number_format($c->total_spent ?? 0, 2, '.', ''),
                        $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
                    ]);
                }
                fclose($handle);
            }, 'idea-customers-' . date('Ymd-His') . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $customers = $query->paginate(20)->withQueryString();

        $hasLoyaltyPoints = Schema::hasColumn('users', 'loyalty_points');

        $summary = [
            'total_customers' => User::whereIn('role', ['buyer', 'customer'])->count(),
            'active_buyers'   => User::whereIn('role', ['buyer', 'customer'])->has('orders')->count(),
            'zero_orders'     => User::whereIn('role', ['buyer', 'customer'])->doesntHave('orders')->count(),
            'total_spent_sum' => \App\Models\Order::sum('total_amount'),
            'loyalty_points'  => $hasLoyaltyPoints ? User::whereIn('role', ['buyer', 'customer'])->sum('loyalty_points') : 0,
        ];

        return view('admin.customers.index', [
            'customers' => $customers,
            'summary'   => $summary,
            'filter'    => $filter,
        ]);
    }

    public function broadcastMessage(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'target_group' => 'required|string|in:all,with_orders,high_value',
            'channel'      => 'required|string|in:sms,notice,email,whatsapp',
            'title'        => 'nullable|string|max:255',
            'message_body' => 'required|string|max:1000',
        ]);

        $query = User::whereIn('role', ['buyer', 'customer']);
        if ($validated['target_group'] === 'with_orders') {
            $query->has('orders');
        } elseif ($validated['target_group'] === 'high_value') {
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

        return back()->with('success', "{$recipientCount} জন গ্রাহকের কাছে সফলভাবে ব্রডকাস্ট মেসেজ প্রস্তুত ও পাঠানো হয়েছে!");
    }
}
