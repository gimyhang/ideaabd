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

        // Filter by warranty
        if ($request->filled('warranty')) {
            $wVal = $request->input('warranty');
            if ($wVal === '1year') {
                $query->where(function ($q) {
                    $q->where('warranty', 'like', '%১ বছর%')
                      ->orWhere('warranty', 'like', '%২ বছর%')
                      ->orWhere('warranty', 'like', '%1 year%')
                      ->orWhere('warranty', 'like', '%2 year%');
                });
            } elseif ($wVal === 'replacement') {
                $query->where(function ($q) {
                    $q->where('warranty', 'like', '%রিপ্লেসমেন্ট%')
                      ->orWhere('warranty', 'like', '%replacement%');
                });
            } elseif ($wVal === 'official') {
                $query->where(function ($q) {
                    $q->where('warranty', 'like', '%অফিসিয়াল%')
                      ->orWhere('warranty', 'like', '%ব্র্যান্ড%')
                      ->orWhere('warranty', 'like', '%official%');
                });
            } elseif ($wVal === 'guarantee') {
                $query->where(function ($q) {
                    $q->where('warranty', 'like', '%গ্যারান্টি%')
                      ->orWhere('warranty', 'like', '%লাইফটাইম%')
                      ->orWhere('warranty', 'like', '%guarantee%');
                });
            } elseif ($wVal === 'has_warranty' || $wVal === 'all') {
                $query->whereNotNull('warranty')->where('warranty', '!=', '');
            } else {
                $query->where('warranty', $wVal);
            }
        }

        // Filter by product feature (bestseller, hot_deal, featured, new_arrival)
        if ($request->filled('feature')) {
            $feat = $request->input('feature');
            if ($feat === 'featured') {
                $query->where('is_featured', true);
            } elseif ($feat === 'bestseller') {
                $query->where(function ($q) {
                    $q->where('badge', 'like', '%বেস্টসেলার%')
                      ->orWhere('badge', 'like', '%সেরা%');
                });
            } elseif ($feat === 'hot_deal') {
                $query->where(function ($q) {
                    $q->where('badge', 'like', '%হট ডিল%')
                      ->orWhere('badge', 'like', '%hot deal%');
                });
            } elseif ($feat === 'new_arrival') {
                $query->where(function ($q) {
                    $q->where('badge', 'like', '%নতুন%')
                      ->orWhere('badge', 'like', '%new%');
                });
            }
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

        $ratingCounts = [
            '5' => Product::where('type', $type)->active()->where('rating', '>=', 4.8)->count(),
            '4' => Product::where('type', $type)->active()->where('rating', '>=', 4.0)->count(),
            '3' => Product::where('type', $type)->active()->where('rating', '>=', 3.0)->count(),
        ];

        $warrantyCounts = [
            'all' => Product::where('type', $type)->active()->whereNotNull('warranty')->where('warranty', '!=', '')->count(),
            'official' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('warranty', 'like', '%অফিসিয়াল%')
                  ->orWhere('warranty', 'like', '%ব্র্যান্ড%')
                  ->orWhere('warranty', 'like', '%official%');
            })->count(),
            'replacement' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('warranty', 'like', '%রিপ্লেসমেন্ট%')
                  ->orWhere('warranty', 'like', '%replacement%');
            })->count(),
            '1year' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('warranty', 'like', '%১ বছর%')
                  ->orWhere('warranty', 'like', '%২ বছর%')
                  ->orWhere('warranty', 'like', '%1 year%')
                  ->orWhere('warranty', 'like', '%2 year%');
            })->count(),
            'guarantee' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('warranty', 'like', '%গ্যারান্টি%')
                  ->orWhere('warranty', 'like', '%লাইফটাইম%');
            })->count(),
        ];

        $featureCounts = [
            'featured' => Product::where('type', $type)->active()->where('is_featured', true)->count(),
            'bestseller' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('badge', 'like', '%বেস্টসেলার%')->orWhere('badge', 'like', '%সেরা%');
            })->count(),
            'hot_deal' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('badge', 'like', '%হট ডিল%')->orWhere('badge', 'like', '%hot deal%');
            })->count(),
            'new_arrival' => Product::where('type', $type)->active()->where(function ($q) {
                $q->where('badge', 'like', '%নতুন%')->orWhere('badge', 'like', '%new%');
            })->count(),
        ];

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
                'title' => 'হোম অ্যাপ্লায়েন্স ও ডিজিটাল গ্যাজেট শপ',
                'subtitle' => 'স্মার্ট কিচেন অ্যাপ্লায়েন্স, ইলেকট্রিক কেটলি, ব্লেন্ডার, এয়ার ফ্রায়ার, ভ্যাকুয়াম ক্লিনার ও আধুনিক হোম ডিভাইসের বিশ্বস্ত সম্ভার',
                'badge' => 'Idea Home Appliances',
                'badge_suffix' => '১০০% জেনুইন হোম অ্যাপ্লায়েন্স',
                'slug' => 'electronics',
                'icon' => 'fas fa-blender',
                'theme_color' => '#0284c7',
                'theme_color_dark' => '#0369a1',
                'banner_bg' => 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 55%, #f8fafc 100%)',
                'search_placeholder' => 'ইলেকট্রিক কেটলি, ব্লেন্ডার, এয়ার ফ্রায়ার, ভ্যাকুয়াম ক্লিনার খুঁজুন...',
                'category_title' => 'হোম অ্যাপ্লায়েন্স ক্যাটাগরি',
                'popular_text' => 'জনপ্রিয় অ্যাপ্লায়েন্স',
                'new_text' => 'নতুন সংযোজন',
                'under_1000_text' => '৳১,০০০-এর নিচের পণ্য',
                'guarantee_title' => '১০০% আসল ও জেনুইন অ্যাপ্লায়েন্স',
                'guarantee_desc' => 'পরীক্ষিত সেরা ব্র্যান্ড ও অফিসিয়াল ওয়ারেন্টি নিশ্চয়তা',
                'quick_view_summary' => 'উন্নত মানের আকর্ষণীয় ও পরীক্ষিত হোম অ্যাপ্লায়েন্স।',
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
                'banner_bg' => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 55%, #f8fafc 100%)',
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
            'priceStats',
            'ratingCounts',
            'warrantyCounts',
            'featureCounts'
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
