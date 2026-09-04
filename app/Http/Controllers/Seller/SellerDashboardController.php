<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Book\Models\Book;

class SellerDashboardController extends Controller
{
    /**
     * Display Seller / Subadmin Portal Dashboard.
     */
    public function dashboard(Request $request): View
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        
        $sellerId = $isAdmin && $request->filled('seller_id') ? $request->input('seller_id') : ($isAdmin ? null : $user->id);
        
        $selectedSeller = null;
        if ($sellerId) {
            $selectedSeller = ($sellerId == $user->id) ? $user : User::find($sellerId);
        }

        $billsQuery = Bill::query()
            ->when(!$isAdmin, fn($q) => $q->where('seller_id', $user->id))
            ->when($isAdmin && $sellerId, fn($q) => $q->where('seller_id', $sellerId));

        // Stats
        $totalBills = (clone $billsQuery)->count();
        $totalSales = (float) (clone $billsQuery)->sum('total');
        $totalPaid = (float) (clone $billsQuery)->where('payment_status', 'paid')->sum('total');
        $totalDue = (float) (clone $billsQuery)->where('payment_status', '!=', 'paid')->sum('total');
        
        $todayBills = (clone $billsQuery)->whereDate('created_at', today())->count();
        $todaySales = (float) (clone $billsQuery)->whereDate('created_at', today())->sum('total');
        
        $thisMonthSales = (float) (clone $billsQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $paidBillsCount = (clone $billsQuery)->where('payment_status', 'paid')->count();
        $dueBillsCount = (clone $billsQuery)->where('payment_status', '!=', 'paid')->count();

        // Calculate total items sold across bills
        $allBills = (clone $billsQuery)->get(['items']);
        $totalItemsSold = 0;
        foreach ($allBills as $b) {
            if (is_array($b->items)) {
                foreach ($b->items as $item) {
                    $totalItemsSold += (int) ($item['quantity'] ?? $item['qty'] ?? 1);
                }
            }
        }

        // Recent 10 bills
        $recentBills = (clone $billsQuery)->with('seller')->latest('id')->take(10)->get();

        // Seller profile & shop details
        if ($selectedSeller) {
            $shopName = $selectedSeller->reg_data['shop_name'] ?? ($selectedSeller->name . ' - Bookshop');
            $shopAddress = $selectedSeller->reg_data['address'] ?? ($selectedSeller->address ?? 'বাংলাদেশ');
            $tradeLicense = $selectedSeller->reg_data['trade_license'] ?? null;
            $nid = $selectedSeller->reg_data['nid'] ?? null;
        } elseif ($isAdmin) {
            $shopName = 'সকল বিক্রেতা ও কেন্দ্রীয় বিক্রয় প্যানেল (All Sellers Overview)';
            $shopAddress = 'সকল সেলার ও বুকশপ আউটলেটের সমন্বিত বিক্রয় ও বিলিং হিসাব';
            $tradeLicense = null;
            $nid = null;
        } else {
            $shopName = $user->reg_data['shop_name'] ?? ($user->name . ' - Bookshop');
            $shopAddress = $user->reg_data['address'] ?? ($user->address ?? 'বাংলাদেশ');
            $tradeLicense = $user->reg_data['trade_license'] ?? null;
            $nid = $user->reg_data['nid'] ?? null;
        }

        // Quick inventory / popular books preview
        $popularBooks = Book::where('is_active', true)
            ->orderByDesc('sales_count')
            ->take(6)
            ->get(['id', 'title', 'price', 'stock_quantity', 'cover_image', 'slug']);

        $sellersList = $isAdmin ? User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUB_ADMIN, User::ROLE_SELLER])->orderBy('name')->pluck('name', 'id')->all() : [];

        return view('seller.dashboard', compact(
            'user',
            'isAdmin',
            'sellerId',
            'shopName',
            'shopAddress',
            'tradeLicense',
            'nid',
            'totalBills',
            'totalSales',
            'totalPaid',
            'totalDue',
            'todayBills',
            'todaySales',
            'thisMonthSales',
            'paidBillsCount',
            'dueBillsCount',
            'totalItemsSold',
            'recentBills',
            'popularBooks',
            'sellersList'
        ));
    }
}
