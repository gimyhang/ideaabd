<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Publisher\Models\Publisher;
use Modules\Book\Models\Book;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    /**
     * প্রকাশক ও প্রকাশনী ডিরেক্টরি ক্যাটালগ
     */
    public function index(Request $request): View
    {
        $canUsePublishers = false;
        try {
            $canUsePublishers = DB::getSchemaBuilder()->hasTable('publishers');
        } catch (\Throwable) {
            $canUsePublishers = false;
        }

        $stats = [
            'total' => 0,
            'verified' => 0,
            'total_books' => 0,
        ];

        $publishers = collect();

        if ($canUsePublishers) {
            $stats['total'] = Publisher::query()->where('is_active', true)->count();
            $stats['verified'] = Publisher::query()->where('is_active', true)->where('is_verified', true)->count();
            
            try {
                if (DB::getSchemaBuilder()->hasTable('books')) {
                    $stats['total_books'] = Book::query()->where('is_active', true)->whereNotNull('publisher_id')->count();
                }
            } catch (\Throwable) {}

            $query = Publisher::query()
                ->where('is_active', true)
                ->withCount([
                    'books' => fn ($q) => $q->where('is_active', true),
                    'ebooks' => fn ($q) => $q->where('is_active', true),
                ]);

            // Filter: Search
            if ($request->filled('search')) {
                $term = '%' . trim($request->string('search')->value()) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', $term)
                      ->orWhere('description', 'LIKE', $term)
                      ->orWhere('country', 'LIKE', $term)
                      ->orWhere('address', 'LIKE', $term);
                });
            }

            // Filter: Verified Only
            if ($request->boolean('verified_only')) {
                $query->where('is_verified', true);
            }

            // Filter: Letter / Alphabet (বাংলা বর্ণমালা বা ইংরেজি)
            if ($request->filled('letter') && $request->string('letter') !== 'all') {
                $letter = trim($request->string('letter')->value());
                $query->where('name', 'LIKE', $letter . '%');
            }

            // Sort
            $sort = $request->input('sort', 'most_books');
            match ($sort) {
                'name_asc'   => $query->orderBy('name', 'asc'),
                'name_desc'  => $query->orderBy('name', 'desc'),
                'latest'     => $query->latest('id'),
                default      => $query->orderByDesc('books_count')->orderByDesc('is_verified')->orderBy('name', 'asc'),
            };

            $publishers = $query->paginate(12)->withQueryString();
        }

        return view('frontend.pages.publishers', compact('publishers', 'stats'));
    }

    /**
     * একক প্রকাশনীর প্রোফাইল ও প্রকাশিত বইসমূহ
     */
    public function show(string $slug): View
    {
        $decoded = urldecode($slug);
        
        $publisher = Publisher::query()
            ->where('is_active', true)
            ->where(function ($q) use ($slug, $decoded) {
                $q->where('slug', $slug)
                  ->orWhere('slug', $decoded)
                  ->orWhere('name', $decoded);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->first();

        if (!$publisher) {
            $publisher = Publisher::query()->where('is_active', true)->firstOrFail();
        }

        $books = $publisher->books()
            ->with(['authors', 'category'])
            ->where('is_active', true)
            ->latest()
            ->paginate(12, ['*'], 'books_page');

        $ebooks = $publisher->ebooks()
            ->with(['author', 'category'])
            ->where('is_active', true)
            ->latest()
            ->take(12)
            ->get();

        return view('frontend.pages.publisher-detail', compact('publisher', 'books', 'ebooks'));
    }
}
