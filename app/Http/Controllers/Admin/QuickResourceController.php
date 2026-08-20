<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Author\Models\Author;
use Modules\Book\Models\Category;
use Modules\Publisher\Models\Publisher;

class QuickResourceController extends Controller
{
    /**
     * Quickly create a new category via AJAX.
     */
    public function quickStoreCategory(Request $request): JsonResponse
    {
        if ($request->input('type') === 'blog' || $request->input('target') === 'blog_categories') {
            return $this->quickStoreBlogCategory($request);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $name = trim($validated['name']);
        $slug = $this->uniqueSlug('categories', $name);

        $category = Category::create([
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => true,
        ]);

        $parentName = $category->parent ? " ({$category->parent->name})" : '';
        $displayName = ($category->parent_id ? '— ' : '') . $category->name . $parentName;

        return response()->json([
            'success' => true,
            'message' => "ক্যাটাগরি '{$category->name}' সফলভাবে তৈরি হয়েছে।",
            'item'    => [
                'id'           => $category->id,
                'name'         => $category->name,
                'display_name' => $displayName,
                'slug'         => $category->slug,
                'parent_id'    => $category->parent_id,
            ],
        ]);
    }

    /**
     * Quickly create a new author via AJAX.
     */
    public function quickStoreAuthor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'bio'     => 'nullable|string|max:2000',
        ]);

        $author = Author::findOrCreateUnified([
            'name'      => $validated['name'],
            'phone'     => $validated['phone'] ?? null,
            'email'     => $validated['email'] ?? null,
            'bio'       => $validated['bio'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "লেখক '{$author->name}' সফলভাবে সংরক্ষিত ও সিঙ্ক হয়েছে।",
            'item'    => [
                'id'   => $author->id,
                'name' => $author->name,
                'slug' => $author->slug,
            ],
        ]);
    }

    /**
     * Quickly create a new publisher via AJAX.
     */
    public function quickStorePublisher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $name = trim($validated['name']);
        $slug = $this->uniqueSlug('publishers', $name);

        $publisher = Publisher::create([
            'name'      => $name,
            'slug'      => $slug,
            'phone'     => $validated['phone'] ?? null,
            'email'     => $validated['email'] ?? null,
            'address'   => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "প্রকাশনী '{$publisher->name}' সফলভাবে যুক্ত হয়েছে।",
            'item'    => [
                'id'   => $publisher->id,
                'name' => $publisher->name,
                'slug' => $publisher->slug,
            ],
        ]);
    }
    /**
     * Quickly create a new blog category via AJAX.
     */
    public function quickStoreBlogCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'icon'        => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $name = trim($validated['name']);
        $slug = $this->uniqueSlug('blog_categories', $name);

        $category = \Modules\Blog\Models\BlogCategory::create([
            'name'        => $name,
            'slug'        => $slug,
            'icon'        => $validated['icon'] ?: 'feather-pointed',
            'description' => $validated['description'] ?? null,
            'is_active'   => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "ব্লগ ক্যাটাগরি '{$category->name}' সফলভাবে তৈরি হয়েছে।",
            'item'    => [
                'id'           => $category->id,
                'name'         => $category->name,
                'display_name' => $category->name,
                'slug'         => $category->slug,
            ],
        ]);
    }

    /**
     * Generate a unique slug in given table.
     */
    private function uniqueSlug(string $table, string $title): string
    {
        $bengali = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্'];
        $english = ['a','a','i','i','u','u','ri','e','oi','o','ou','k','kh','g','gh','ng','ch','ch','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','n','a','i','i','u','u','ri','e','oi','o','ou',''];
        $converted = str_replace($bengali, $english, $title);
        $base = Str::slug($converted) ?: Str::slug(Str::random(8));
        $slug = $base;
        $count = 1;

        while (\Illuminate\Support\Facades\DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$count);
        }

        return $slug;
    }
}
