<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors with filters and sorting.
     */
    public function index(Request $request)
    {
        $query = Author::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }

        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->where('name', 'like', "$letter%");
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'most_books':
                    $query->withCount('books')->orderBy('books_count', 'desc');
                    break;
                case 'recent_active':
                    $query->orderBy('updated_at', 'desc');
                    break;
                case 'award_winners':
                    $query->where('is_award_winner', 1);
                    break;
            }
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'popular':
                    $query->orderBy('popularity', 'desc');
                    break;
                case 'books_desc':
                    $query->withCount('books')->orderBy('books_count', 'desc');
                    break;
                case 'latest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            // sensible default
            $query->orderBy('name');
        }

        $authors = $query->paginate(18);

        // sidebar: top authors by book count
        $topAuthors = Author::withCount('books')->orderBy('books_count', 'desc')->limit(6)->get();

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
