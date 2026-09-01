<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookBundle;
use App\Models\BundleItem;
use App\Models\PreOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Book\Models\Book;

class BundleAdminController extends Controller
{
    public function index(): View
    {
        $bundles = BookBundle::with(['items.book'])->latest()->paginate(15);
        $preOrders = PreOrder::with('book')->latest()->paginate(15);
        $books = Book::select('id', 'title', 'price')->get();

        $activeBundlesCount = BookBundle::where('is_active', true)->count();
        $totalPreOrdersCount = PreOrder::where('status', 'registered')->count();

        return view('admin.bundles.index', compact(
            'bundles',
            'preOrders',
            'books',
            'activeBundlesCount',
            'totalPreOrdersCount'
        ));
    }

    public function storeBundle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'bundle_price'     => 'required|numeric|min:0',
            'regular_price'    => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'book_ids'         => 'required|array|min:2',
            'book_ids.*'       => 'exists:books,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_featured'      => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
        ]);

        $discount = $validated['discount_percent'] ?? 0;
        if ($discount <= 0 && $validated['regular_price'] > 0) {
            $discount = round((($validated['regular_price'] - $validated['bundle_price']) / $validated['regular_price']) * 100, 1);
        }

        $bundle = BookBundle::create([
            'title'            => $validated['title'],
            'slug'             => Str::slug($validated['title']) . '-' . rand(100, 999),
            'description'      => $validated['description'] ?? null,
            'regular_price'    => $validated['regular_price'],
            'bundle_price'     => $validated['bundle_price'],
            'discount_percent' => max(0, $discount),
            'is_featured'      => $request->boolean('is_featured', false),
            'is_active'        => $request->boolean('is_active', true),
        ]);

        foreach ($validated['book_ids'] as $bookId) {
            BundleItem::create([
                'bundle_id' => $bundle->id,
                'book_id'   => $bookId,
            ]);
        }

        return redirect()->route('admin.bundles.index')->with('success', 'নতুন স্পেশাল বই বান্ডেল / কম্বো প্যাকেজ তৈরি হয়েছে।');
    }

    public function updatePreOrderStatus(Request $request, PreOrder $preOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:registered,confirmed,converted_to_order,cancelled',
        ]);

        $preOrder->update(['status' => $validated['status']]);

        return redirect()->route('admin.bundles.index')->with('success', 'প্রি-অর্ডার স্ট্যাটাস আপডেট হয়েছে।');
    }
}
