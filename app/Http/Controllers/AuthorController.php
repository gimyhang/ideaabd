<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Author\Models\Author;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthorController extends Controller
{
    /**
     * Display a comprehensive modern listing of authors with rich filters, categories, and search.
     */
    public function index(Request $request)
    {
        $query = Author::query()
            ->withCount('books')
            ->with(['books' => function ($q) {
                $q->select('books.id', 'books.title', 'books.slug', 'books.cover_image', 'books.price')
                  ->where('books.is_active', true)
                  ->orderByDesc('books.id')
                  ->take(3);
            }])
            ->where('is_active', true);

        // Keyword search (Name, Bio, Slug, or Book Titles)
        if ($request->filled('q') || $request->filled('search')) {
            $term = trim($request->input('q') ?: $request->input('search'));
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', '%' . $term . '%')
                    ->orWhere('slug', 'like', '%' . $term . '%')
                    ->orWhere('bio', 'like', '%' . $term . '%')
                    ->orWhereHas('books', function ($bq) use ($term) {
                        $bq->where('books.title', 'like', '%' . $term . '%');
                    });
            });
        }

        // Alphabetical & Bengali Character Filter
        if ($request->filled('letter')) {
            $letter = trim($request->letter);
            $query->where('name', 'like', "{$letter}%");
        }

        // Category Filter (Authors who published books in a specific Category)
        if ($request->filled('category_id')) {
            $catId = (int) $request->category_id;
            $query->whereHas('books', function ($bq) use ($catId) {
                $bq->where('books.category_id', $catId);
            });
        }

        // Quick Filters
        if ($request->filled('filter')) {
            match ($request->filter) {
                'verified'     => $query->where('is_verified', true),
                'most_books'   => $query->orderByDesc('books_count'),
                'with_books'   => $query->has('books'),
                'recent_active'=> $query->latest('updated_at'),
                default        => null,
            };
        }

        // Sorting
        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'books_desc' => $query->orderByDesc('books_count'),
            'latest'     => $query->latest('created_at'),
            'popular'    => $query->orderByDesc('books_count')->latest('id'),
            default      => $query->orderByDesc('books_count')->latest('id'),
        };

        // Per page
        $perPage = in_array((int) $request->input('per_page'), [12, 18, 24, 36, 48], true) ? (int) $request->input('per_page') : 18;
        $authors = $query->paginate($perPage)->withQueryString();

        // If AJAX request for live search autocomplete
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'total'   => $authors->total(),
                'data'    => $authors->items(),
            ]);
        }

        // Sidebar Data: Top Authors by book count
        $topAuthors = Author::withCount('books')
            ->where('is_active', true)
            ->orderByDesc('books_count')
            ->limit(6)
            ->get();

        // Sidebar Data: Featured Verified Authors
        $featuredAuthors = Author::withCount('books')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->has('books')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Popular Categories with book/author connections
        $popularCategories = collect();
        if (Schema::hasTable('categories') && Schema::hasTable('books')) {
            $popularCategories = DB::table('categories')
                ->join('books', 'categories.id', '=', 'books.category_id')
                ->where('categories.is_active', true)
                ->where('books.is_active', true)
                ->select('categories.id', 'categories.name', 'categories.slug', DB::raw('COUNT(books.id) as total_books'))
                ->groupBy('categories.id', 'categories.name', 'categories.slug')
                ->orderByDesc('total_books')
                ->limit(8)
                ->get();
        }

        // Directory Global Stats
        $stats = [
            'total_authors'  => Author::where('is_active', true)->count(),
            'verified_count' => Author::where('is_active', true)->where('is_verified', true)->count(),
            'total_books'    => Schema::hasTable('book_author') ? DB::table('book_author')->distinct('book_id')->count('book_id') : 0,
        ];

        return view('authors.index', compact('authors', 'topAuthors', 'featuredAuthors', 'popularCategories', 'stats'));
    }

    /**
     * Display the specified author, their full biography, published books, ebooks & blog articles.
     */
    public function show($author)
    {
        // Support either slug string or numeric ID or Model
        if ($author instanceof Author) {
            $authorRecord = $author;
        } else {
            $authorRecord = is_numeric($author)
                ? Author::where('id', $author)->where('is_active', true)->firstOrFail()
                : Author::where('slug', $author)->where('is_active', true)->firstOrFail();
        }

        // Paginate author books
        $books = $authorRecord->books()
            ->where('books.is_active', true)
            ->orderByDesc('books.id')
            ->paginate(12);

        // Load author ebooks if Ebook relation exists
        $ebooks = collect();
        try {
            $ebooks = $authorRecord->ebooks()->where('is_active', true)->take(6)->get();
        } catch (\Throwable $e) {}

        // Load author blog posts if any
        $blogPosts = collect();
        try {
            $blogPosts = $authorRecord->blogPosts()->where('status', 'published')->latest()->take(4)->get();
        } catch (\Throwable $e) {}

        // Related Authors (Other authors in similar genres or top authors)
        $relatedAuthors = Author::withCount('books')
            ->where('is_active', true)
            ->where('id', '!=', $authorRecord->id)
            ->orderByDesc('books_count')
            ->limit(5)
            ->get();

        return view('authors.show', [
            'author'         => $authorRecord,
            'books'          => $books,
            'ebooks'         => $ebooks,
            'blogPosts'      => $blogPosts,
            'relatedAuthors' => $relatedAuthors,
            'editorsPicks'   => [],
        ]);
    }
}
