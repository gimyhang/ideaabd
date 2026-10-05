<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ImageOptimizerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductAdminController extends Controller
{
    /**
     * Display a listing of products and categories.
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'products');
        $type = $request->query('type', 'all'); // 'all', 'electronics', 'stationery'

        $query = Product::with('category')->orderBy('sort_order')->orderByDesc('id');

        if (in_array($type, ['electronics', 'stationery'], true)) {
            $query->where('type', $type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->paginate(20)->withQueryString();

        $categories = ProductCategory::withCount('products')
            ->when(in_array($type, ['electronics', 'stationery'], true), function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        $counts = [
            'total' => Product::count(),
            'electronics' => Product::where('type', 'electronics')->count(),
            'stationery' => Product::where('type', 'stationery')->count(),
            'categories' => ProductCategory::count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'counts', 'type', 'currentTab'));
    }

    /**
     * Show form for creating a new product.
     */
    public function create(Request $request): View
    {
        $selectedType = $request->query('type', 'electronics');
        if (!in_array($selectedType, ['electronics', 'stationery'], true)) {
            $selectedType = 'electronics';
        }

        $categories = ProductCategory::where('type', $selectedType)->orderBy('sort_order')->get();
        $allCategories = ProductCategory::orderBy('type')->orderBy('name')->get();

        return view('admin.products.form', [
            'product' => new Product(['type' => $selectedType, 'stock_status' => 'in_stock', 'is_active' => true]),
            'isEdit' => false,
            'selectedType' => $selectedType,
            'categories' => $categories,
            'allCategories' => $allCategories,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Pre-clean empty strings to prevent MySQL strict decimal / foreign key errors
        if ($request->input('discount_price') === '' || $request->input('discount_price') === null) {
            $request->merge(['discount_price' => null]);
        }
        if ($request->input('category_id') === '' || $request->input('category_id') === null) {
            $request->merge(['category_id' => null]);
        }
        if ($request->input('slug') === '' || $request->input('slug') === null) {
            $request->merge(['slug' => null]);
        }
        if ($request->input('sku') === '' || $request->input('sku') === null) {
            $request->merge(['sku' => null]);
        }

        $validated = $request->validate([
            'type' => 'required|in:electronics,stationery',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'brand' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,pre_order',
            'warranty' => 'nullable|string|max:150',
            'badge' => 'nullable|string|max:50',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'cover_image_file' => 'nullable|image|max:3072',
            'cover_image_url' => 'nullable|string|max:1000',
            'spec_keys' => 'nullable|array',
            'spec_vals' => 'nullable|array',
        ]);

        $coverImage = $validated['cover_image_url'] ?? null;
        if ($request->hasFile('cover_image_file')) {
            $file = $request->file('cover_image_file');
            $coverImage = ImageOptimizerService::convertAndStoreSquareProductImage($file, 'products', 'public', 82, 800);
        } elseif (!empty($coverImage)) {
            $coverImage = ImageOptimizerService::convertAndStoreSquareProductImage($coverImage, 'products', 'public', 82, 800);
        }

        // Process key-value specifications
        $specifications = [];
        if (!empty($validated['spec_keys']) && !empty($validated['spec_vals'])) {
            foreach ($validated['spec_keys'] as $idx => $key) {
                $k = trim((string)$key);
                $v = trim((string)($validated['spec_vals'][$idx] ?? ''));
                if ($k !== '' && $v !== '') {
                    $specifications[$k] = $v;
                }
            }
        }

        // Guaranteed Unique Slug
        $baseSlug = !empty($validated['slug']) ? Str::slug($validated['slug']) : (Str::slug($validated['title']) ?: 'product-' . time());
        $finalSlug = $baseSlug;
        $counter = 1;
        while (Product::where('slug', $finalSlug)->exists()) {
            $finalSlug = $baseSlug . '-' . (++$counter);
        }

        // Default SKU if empty
        $finalSku = !empty($validated['sku']) ? trim((string)$validated['sku']) : null;
        if (empty($finalSku)) {
            $prefix = ($validated['type'] === 'electronics') ? 'ELC' : 'STN';
            $finalSku = $prefix . '-' . date('ym') . '-' . rand(1000, 9999);
        }

        $product = Product::create([
            'type'           => $validated['type'],
            'category_id'    => !empty($validated['category_id']) ? (int)$validated['category_id'] : null,
            'title'          => $validated['title'],
            'slug'           => $finalSlug,
            'sku'            => $finalSku,
            'brand'          => !empty($validated['brand']) ? trim((string)$validated['brand']) : null,
            'model'          => !empty($validated['model']) ? trim((string)$validated['model']) : null,
            'summary'        => !empty($validated['summary']) ? trim((string)$validated['summary']) : null,
            'description'    => !empty($validated['description']) ? trim((string)$validated['description']) : null,
            'specifications' => $specifications,
            'price'          => (float)$validated['price'],
            'discount_price' => (!empty($validated['discount_price']) && is_numeric($validated['discount_price'])) ? (float)$validated['discount_price'] : null,
            'stock'          => (int)$validated['stock'],
            'stock_status'   => $validated['stock_status'],
            'cover_image'    => $coverImage,
            'warranty'       => !empty($validated['warranty']) ? trim((string)$validated['warranty']) : null,
            'badge'          => !empty($validated['badge']) ? trim((string)$validated['badge']) : null,
            'is_featured'    => !empty($validated['is_featured']),
            'is_active'      => isset($validated['is_active']) ? (bool)$validated['is_active'] : true,
            'sort_order'     => (int)($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.products.index', ['type' => $product->type])
            ->with('success', 'পণ্যটি সফলভাবে যুক্ত করা হয়েছে!');
    }

    /**
     * Show form for editing product.
     */
    public function edit(int $id): View
    {
        $product = Product::findOrFail($id);
        $selectedType = $product->type;
        $categories = ProductCategory::where('type', $selectedType)->orderBy('sort_order')->get();
        $allCategories = ProductCategory::orderBy('type')->orderBy('name')->get();

        return view('admin.products.form', [
            'product' => $product,
            'isEdit' => true,
            'selectedType' => $selectedType,
            'categories' => $categories,
            'allCategories' => $allCategories,
        ]);
    }

    /**
     * Update product in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        if ($request->input('discount_price') === '' || $request->input('discount_price') === null) {
            $request->merge(['discount_price' => null]);
        }
        if ($request->input('category_id') === '' || $request->input('category_id') === null) {
            $request->merge(['category_id' => null]);
        }
        if ($request->input('slug') === '' || $request->input('slug') === null) {
            $request->merge(['slug' => null]);
        }
        if ($request->input('sku') === '' || $request->input('sku') === null) {
            $request->merge(['sku' => null]);
        }

        $validated = $request->validate([
            'type' => 'required|in:electronics,stationery',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'brand' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,pre_order',
            'warranty' => 'nullable|string|max:150',
            'badge' => 'nullable|string|max:50',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'cover_image_file' => 'nullable|image|max:3072',
            'cover_image_url' => 'nullable|string|max:1000',
            'spec_keys' => 'nullable|array',
            'spec_vals' => 'nullable|array',
        ]);

        $coverImage = $product->cover_image;
        if ($request->hasFile('cover_image_file')) {
            $file = $request->file('cover_image_file');
            $coverImage = ImageOptimizerService::convertAndStoreSquareProductImage($file, 'products', 'public', 82, 800);
        } elseif ($request->filled('cover_image_url')) {
            $url = $request->input('cover_image_url');
            if ($url !== $product->cover_image) {
                $coverImage = ImageOptimizerService::convertAndStoreSquareProductImage($url, 'products', 'public', 82, 800);
            }
        }

        // Process specifications
        $specifications = [];
        if (!empty($validated['spec_keys']) && !empty($validated['spec_vals'])) {
            foreach ($validated['spec_keys'] as $idx => $key) {
                $k = trim((string)$key);
                $v = trim((string)($validated['spec_vals'][$idx] ?? ''));
                if ($k !== '' && $v !== '') {
                    $specifications[$k] = $v;
                }
            }
        }

        // Guaranteed unique slug on update
        $baseSlug = !empty($validated['slug']) ? Str::slug($validated['slug']) : ($product->slug ?: Str::slug($validated['title']) ?: 'product-' . $product->id);
        $finalSlug = $baseSlug;
        $counter = 1;
        while (Product::where('slug', $finalSlug)->where('id', '!=', $product->id)->exists()) {
            $finalSlug = $baseSlug . '-' . (++$counter);
        }

        $finalSku = !empty($validated['sku']) ? trim((string)$validated['sku']) : $product->sku;
        if (empty($finalSku)) {
            $prefix = ($validated['type'] === 'electronics') ? 'ELC' : 'STN';
            $finalSku = $prefix . '-' . date('ym') . '-' . rand(1000, 9999);
        }

        $product->update([
            'type'           => $validated['type'],
            'category_id'    => !empty($validated['category_id']) ? (int)$validated['category_id'] : null,
            'title'          => $validated['title'],
            'slug'           => $finalSlug,
            'sku'            => $finalSku,
            'brand'          => !empty($validated['brand']) ? trim((string)$validated['brand']) : null,
            'model'          => !empty($validated['model']) ? trim((string)$validated['model']) : null,
            'summary'        => !empty($validated['summary']) ? trim((string)$validated['summary']) : null,
            'description'    => !empty($validated['description']) ? trim((string)$validated['description']) : null,
            'specifications' => $specifications,
            'price'          => (float)$validated['price'],
            'discount_price' => (!empty($validated['discount_price']) && is_numeric($validated['discount_price'])) ? (float)$validated['discount_price'] : null,
            'stock'          => (int)$validated['stock'],
            'stock_status'   => $validated['stock_status'],
            'cover_image'    => $coverImage,
            'warranty'       => !empty($validated['warranty']) ? trim((string)$validated['warranty']) : null,
            'badge'          => !empty($validated['badge']) ? trim((string)$validated['badge']) : null,
            'is_featured'    => !empty($validated['is_featured']),
            'is_active'      => !empty($validated['is_active']),
            'sort_order'     => (int)($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.products.index', ['type' => $product->type])
            ->with('success', 'পণ্যটি সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * Delete product.
     */
    public function destroy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $type = $product->type;
        $product->delete();

        return redirect()->route('admin.products.index', ['type' => $type])
            ->with('success', 'পণ্যটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        $statusText = $product->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        return back()->with('success', "পণ্যটি {$statusText} করা হয়েছে।");
    }

    /**
     * Store new category.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:electronics,stationery',
            'slug' => 'nullable|string|max:150|unique:product_categories,slug',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        ProductCategory::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'fas fa-tag',
            'sort_order' => (int)($validated['sort_order'] ?? 0),
            'is_active' => true,
        ]);

        return back()->with('success', 'নতুন ক্যাটাগরি সফলভাবে যুক্ত হয়েছে!');
    }

    /**
     * Update category.
     */
    public function updateCategory(Request $request, int $id): RedirectResponse
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:electronics,stationery',
            'slug' => 'nullable|string|max:150|unique:product_categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : $category->slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? $category->icon,
            'sort_order' => (int)($validated['sort_order'] ?? 0),
        ]);

        return back()->with('success', 'ক্যাটাগরি আপডেট করা হয়েছে!');
    }

    /**
     * Delete category.
     */
    public function destroyCategory(int $id): RedirectResponse
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return back()->with('success', 'ক্যাটাগরি সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
