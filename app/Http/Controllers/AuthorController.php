<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Author\Models\Author;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors with filters and sorting.
     */
    public function index(Request $request)
    {
        $query = Author::query()->withCount('books')->where('is_active', true);

        if ($request->filled('q')) {
            $query->where(function($sub) use ($request) {
                $sub->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('bio', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->where('name', 'like', "$letter%");
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'most_books':
                    $query->orderBy('books_count', 'desc');
                    break;
                case 'recent_active':
                    $query->orderBy('updated_at', 'desc');
                    break;
                case 'verified':
                    $query->where('is_verified', true);
                    break;
            }
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'popular':
                case 'books_desc':
                    $query->orderBy('books_count', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'latest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $authors = $query->paginate(18)->withQueryString();

        // sidebar: top authors by book count
        $topAuthors = Author::withCount('books')->where('is_active', true)->orderBy('books_count', 'desc')->limit(6)->get();

        return view('authors.index', compact('authors', 'topAuthors'));
    }

    /**
     * Display the specified author and their books.
     */
    public function show(Author $author)
    {
        // eager load books (assumes relation 'books' exists on Author)
        $books = $author->books()->paginate(12);

        // sample sidebar picks — can be customized
        $editorsPicks = [];

        return view('authors.show', compact('author', 'books', 'editorsPicks'));
    }
}
