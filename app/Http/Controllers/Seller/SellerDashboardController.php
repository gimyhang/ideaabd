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
    /** Roles that can own bills. */
    private const SELLER_ROLES = [User::ROLE_ADMIN, User::ROLE_SUB_ADMIN, User::ROLE_SELLER];

    /**
     * Seller / sub-admin portal dashboard.
     * A seller sees their own bills. An admin sees all sellers, or one seller picked with ?seller_id=.
     */
    public function dashboard(Request $request): View
    {
        $user    = $request->user();
        $isAdmin = $user->isAdmin();

        $request->validate(['seller_id' => 'nullable|integer']);

        $sellersList = $isAdmin
            ? User::whereIn('role', self::SELLER_ROLES)->orderBy('name')->pluck('name', 'id')->all()
            : [];

        // Only an admin can pick a seller, and only one that really can own bills.
        $sellerId = $isAdmin && $request->filled('seller_id') && isset($sellersList[(int) $request->input('seller_id')])
            ? (int) $request->input('seller_id')
            : null;

        $scope   = $isAdmin ? $sellerId : $user->id; // null = every seller (admin overview)
        $profile = $isAdmin
            ? ($sellerId ? User::find($sellerId) : null)
            : $user;

        $bills = Bill::query()->when($scope, fn ($q) => $q->where('seller_id', $scope));

        // One query for every number on the cards.
        $todayStart = now()->startOfDay()->toDateTimeString();
        $monthStart = now()->startOfMonth()->toDateTimeString();
        $stats = (clone $bills)->toBase()->selectRaw(
            "COUNT(*) AS bills,
             COALESCE(SUM(total), 0) AS sales,
             COALESCE(SUM(paid_amount), 0) AS paid,
             COALESCE(SUM(due_amount), 0) AS due,
             COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END), 0) AS paid_bills,
             COALESCE(SUM(CASE WHEN payment_status IS NULL OR payment_status <> 'paid' THEN 1 ELSE 0 END), 0) AS open_bills,
             COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) AS today_bills,
             COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) AS today_sales,
             COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) AS month_sales",
            [$todayStart, $todayStart, $monthStart]
        )->first();

        // Copies sold: read the items column a few hundred bills at a time instead of holding every bill in memory.
        $itemsSold = 0;
        (clone $bills)->select(['id', 'items'])->chunkById(500, function ($chunk) use (&$itemsSold) {
            foreach ($chunk as $bill) {
                foreach ((array) $bill->items as $item) {
                    $itemsSold += max(0, (int) ($item['quantity'] ?? $item['qty'] ?? 1));
                }
            }
        });

        $recentBills = (clone $bills)->with('seller:id,name')->latest('id')->take(10)->get();

        $popularBooks = Book::where('is_active', true)
            ->orderByDesc('sales_count')
            ->take(6)
            ->get(['id', 'title', 'price', 'stock_quantity', 'slug']);

        // Whose shop the banner describes: the seller (or the admin's own account), or nobody for the all-sellers view.
        $owner = $profile ?? ($isAdmin ? null : $user);

        return view('seller.dashboard', [
            'user'         => $user,
            'profile'      => $owner,
            'isAdmin'      => $isAdmin,
            'sellerId'     => $sellerId,
            'sellersList'  => $sellersList,
            'shopName'     => $owner ? ($owner->reg_data['shop_name'] ?? ($owner->name . ' - বুকশপ')) : 'সকল বিক্রেতা',
            'shopAddress'  => $owner ? ($owner->reg_data['address'] ?? $owner->address ?? null) : null,
            'tradeLicense' => $owner ? ($owner->reg_data['trade_license'] ?? null) : null,
            'stats'        => $stats,
            'itemsSold'    => $itemsSold,
            'recentBills'  => $recentBills,
            'popularBooks' => $popularBooks,
        ]);
    }
}
