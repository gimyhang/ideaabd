<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display Electronics shop page.
     */
    public function electronics(Request $request)
    {
        return $this->renderCatalog($request, 'electronics');
    }

    /**
     * Display Stationery shop page.
     */
    public function stationery(Request $request)
    {
        return $this->renderCatalog($request, 'stationery');
    }

    /**
     * Common catalog renderer for electronics and stationery.
     */
    protected function renderCatalog(Request $request, string $type)
    {
        $categories = ProductCategory::where('type', $type)
            ->active()
            ->orderBy('sort_order')
            ->withCount(['products' => function ($q) {
                $q->active();
            }])
            ->get();

        $query = Product::where('type', $type)
            ->active()
            ->with('category');

        // Filter by category (slug or id)
        if ($request->filled('category')) {
            $catParam = (string) $request->input('category');
            $query->where(function ($q) use ($catParam) {
                $q->whereHas('category', function ($sub) use ($catParam) {
                    $sub->where('slug', $catParam)->orWhere('id', $catParam);
                })->orWhere('category_id', $catParam);
            });
        }

        // Filter by search query
        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        // Filter by price range (against actual effective selling price)
        if ($request->filled('min_price')) {
            $minP = (float) $request->input('min_price');
            $query->whereRaw('(CASE WHEN discount_price > 0 AND discount_price < price THEN discount_price ELSE price END) >= ?', [$minP]);
        }
        if ($request->filled('max_price')) {
            $maxP = (float) $request->input('max_price');
            $query->whereRaw('(CASE WHEN discount_price > 0 AND discount_price < price THEN discount_price ELSE price END) <= ?', [$maxP]);
        }

        // Filter by stock
        if ($request->input('in_stock') === '1') {
            $query->where('stock', '>', 0);
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        // Filter by discount only
        if ($request->input('discount') === '1') {
            $query->whereNotNull('discount_price')->whereRaw('discount_price < price');
        }

        // Filter by min rating
        if ($request->filled('rating')) {
            $query->where('rating', '>=', (float) $request->input('rating'));
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw('(CASE WHEN discount_price > 0 AND discount_price < price THEN discount_price ELSE price END) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('(CASE WHEN discount_price > 0 AND discount_price < price THEN discount_price ELSE price END) DESC');
                break;
            case 'popular':
                $query->orderByDesc('reviews_count')->orderByDesc('rating')->orderByDesc('id');
                break;
            case 'discount':
                $query->orderByRaw('((price - COALESCE(NULLIF(discount_price, 0), price)) / NULLIF(price, 0)) DESC')->orderByDesc('id');
                break;
            case 'latest':
            default:
                $query->orderBy('sort_order')->orderByDesc('id');
                break;
        }

        $products = $query->paginate(16)->withQueryString();

        $availableBrands = Product::where('type', $type)
            ->active()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');

        $priceStats = [
            'min' => (float) (Product::where('type', $type)->active()->min('price') ?? 0),
            'max' => (float) (Product::where('type', $type)->active()->max('price') ?? 5000),
        ];

        $featuredProducts = Product::where('type', $type)
            ->active()
            ->featured()
            ->take(5)
            ->get();

        $selectedCategory = $request->filled('category')
            ? $categories->first(function ($c) use ($request) {
                $val = (string) $request->input('category');
                return $c->slug === $val || (string) $c->id === $val;
            })
            : null;

        $typeMeta = [
            'electronics' => [
                'title' => 'ইলেক্ট্রনিক্স ও ডিজিটাল গ্যাজেট শপ',
                'subtitle' => 'স্মার্ট স্টাডি ডিভাইস, রিডিং ল্যাম্প, অডিও ও প্রিমিয়াম গ্যাজেটের বিশ্বস্ত কালেকশন',
                'badge' => 'Idea Electronics',
                'badge_suffix' => '১০০% অথেনটিক গ্যাজেট',
                'slug' => 'electronics',
                'icon' => 'fas fa-laptop-code',
                'theme_color' => '#0284c7',
                'theme_color_dark' => '#0369a1',
                'banner_bg' => 'linear-gradient(135deg, #0a192f 0%, #0369a1 55%, #0284c7 100%)',
                'search_placeholder' => 'রিডিং ল্যাম্প, হেডফোন, স্মার্ট গ্যাজেট খুঁজুন...',
                'category_title' => 'গ্যাজেট ও ইলেকট্রনিক্স ক্যাটাগরি',
                'popular_text' => 'সর্বোচ্চ জনপ্রিয় গ্যাজেট',
                'new_text' => 'নতুন গ্যাজেট',
                'under_1000_text' => '৳১,০০০-এর নিচের গ্যাজেট',
                'guarantee_title' => '১০০% আসল ও জেনুইন গ্যাজেট',
                'guarantee_desc' => 'পরীক্ষিত সেরা ব্র্যান্ড ও সর্বোচ্চ কোয়ালিটি নিশ্চয়তা',
                'quick_view_summary' => 'উন্নত মানের আকর্ষণীয় ও পরীক্ষিত গ্যাজেট।',
            ],
            'stationery' => [
                'title' => 'স্টেশনারি ও শিক্ষা সামগ্রী শপ',
                'subtitle' => 'প্রিমিয়াম ডায়েরি, ফাউন্টেন পেন, আর্ট সামগ্রী ও অর্গানাইজারের সমৃদ্ধ সম্ভার',
                'badge' => 'Idea Stationery',
                'badge_suffix' => '১০০% প্রিমিয়াম শিক্ষা সামগ্রী',
                'slug' => 'stationery',
                'icon' => 'fas fa-pen-nib',
                'theme_color' => '#059669',
                'theme_color_dark' => '#047857',
                'banner_bg' => 'linear-gradient(135deg, #064e3b 0%, #047857 55%, #059669 100%)',
                'search_placeholder' => 'ডায়েরি, ফাউন্টেন পেন, স্কেচবুক, আর্ট প্যাড খুঁজুন...',
                'category_title' => 'স্টেশনারি ও শিক্ষা সামগ্রী ক্যাটাগরি',
                'popular_text' => 'সর্বোচ্চ জনপ্রিয় স্টেশনারি',
                'new_text' => 'নতুন শিক্ষা সামগ্রী',
                'under_1000_text' => '৳১,০০০-এর নিচের স্টেশনারি',
                'guarantee_title' => '১০০% প্রিমিয়াম ও অরিজিনাল কালেকশন',
                'guarantee_desc' => 'সেরা ব্র্যান্ডের ডায়েরি, পেন ও প্রফেশনাল ক্রিয়েটিভ আর্ট সামগ্রী',
                'quick_view_summary' => 'উন্নত মানের আকর্ষণীয় ও প্রিমিয়াম স্টেশনারি সামগ্রী।',
            ],
        ];

        $currentMeta = $typeMeta[$type] ?? $typeMeta['electronics'];

        return view('frontend.products.index', compact(
            'products',
            'categories',
            'featuredProducts',
            'selectedCategory',
            'type',
            'currentMeta',
            'availableBrands',
            'priceStats'
        ));
    }

    /**
     * Display Single Product Detail page.
     */
    public function show(string $type, string $slug)
    {
        if (!in_array($type, ['electronics', 'stationery'], true)) {
            abort(404);
        }

        $product = Product::where('type', $type)
            ->where('slug', $slug)
            ->active()
            ->with('category')
            ->firstOrFail();

        $relatedProducts = Product::where('type', $type)
            ->where('id', '!=', $product->id)
            ->active()
            ->when($product->category_id, function ($q) use ($product) {
                $q->orderByRaw("category_id = {$product->category_id} DESC");
            })
            ->take(6)
            ->get();

        return view('frontend.products.show', compact('product', 'relatedProducts', 'type'));
    }

    /**
     * Quick View Modal via AJAX.
     */
    public function quickView(int $id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json([
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'type' => $product->type,
            'brand' => $product->brand,
            'model' => $product->model,
            'sku' => $product->sku,
            'price' => (float) $product->price,
            'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
            'final_price' => (float) $product->final_price,
            'discount_percent' => (int) $product->discount_percent,
            'stock' => (int) $product->stock,
            'stock_status' => $product->stock_status,
            'is_in_stock' => (int) $product->stock > 0 && $product->stock_status !== 'out_of_stock',
            'summary' => $product->summary,
            'warranty' => $product->warranty,
            'badge' => $product->badge,
            'rating' => (float) ($product->rating ?? 4.9),
            'reviews_count' => (int) ($product->reviews_count ?? 15),
            'specifications' => $product->specifications ?? [],
            'image_url' => $product->image_url,
            'url' => route('products.show', ['type' => $product->type, 'slug' => $product->slug]),
            'category_name' => $product->category?->name,
        ]);
    }
}
